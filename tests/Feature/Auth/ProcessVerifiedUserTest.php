<?php

use App\Jobs\ProcessVerifiedUser;
use App\Mail\WelcomeEmail;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Laravel\Fortify\Features;

beforeEach(function () {
    $this->skipUnlessFortifyFeature(Features::emailVerification());

    config(['database.connections.kadi' => [
        'driver' => 'sqlite',
        'database' => ':memory:',
        'prefix' => '',
    ]]);

    DB::connection('kadi')->statement(
        'CREATE TABLE IF NOT EXISTS accounts (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, phone TEXT, email TEXT, password TEXT, outh INTEGER)'
    );
});

test('HandleEmailVerified listener dispatches ProcessVerifiedUser job', function () {
    Queue::fake();

    $user = User::factory()->unverified()->create();

    event(new Verified($user));

    Queue::assertPushed(ProcessVerifiedUser::class, fn ($job) => $job->user->is($user));
});

test('registration caches the plain password for later use', function () {
    $user = User::factory()->create();

    Cache::put("user.plain_password.{$user->id}", 'MyPlain@Pass1');

    expect(Cache::get("user.plain_password.{$user->id}"))->toBe('MyPlain@Pass1');
});

test('ProcessVerifiedUser posts to KadiApi and stores customer_id as linked_id', function () {
    Mail::fake();

    Http::fake([
        '*/customers' => Http::response(['status' => 'Success', 'customer_id' => 999], 200),
    ]);

    $user = User::factory()->create(['linked_id' => null]);
    Cache::put("user.plain_password.{$user->id}", 'Secret@123');

    (new ProcessVerifiedUser($user))->handle();

    expect($user->fresh()->linked_id)->toBe(999);
});

test('ProcessVerifiedUser inserts registration password into kadi database', function () {
    Mail::fake();

    Http::fake([
        '*/customers' => Http::response(['status' => 'Success', 'customer_id' => 1], 200),
    ]);

    $user = User::factory()->create();
    Cache::put("user.plain_password.{$user->id}", 'Secret@123');

    (new ProcessVerifiedUser($user))->handle();

    $record = DB::connection('kadi')->table('accounts')->where('email', $user->email)->first();

    expect($record)->not->toBeNull()
        ->and($record->name)->toBe($user->name)
        ->and($record->email)->toBe($user->email)
        ->and($record->phone)->toBe($user->phone)
        ->and($record->password)->toBe('Secret@123')
        ->and($record->outh)->toBe(1);
});

test('ProcessVerifiedUser sends welcome email with the registration password', function () {
    Mail::fake();

    Http::fake([
        '*/customers' => Http::response(['status' => 'Success', 'customer_id' => 1], 200),
    ]);

    $user = User::factory()->create();
    Cache::put("user.plain_password.{$user->id}", 'Secret@123');

    (new ProcessVerifiedUser($user))->handle();

    Mail::assertSent(WelcomeEmail::class, function ($mail) use ($user) {
        return $mail->hasTo($user->email) && $mail->kadiPlayPassword === 'Secret@123';
    });
});

test('ProcessVerifiedUser handles KadiApi failure gracefully', function () {
    Mail::fake();

    Http::fake([
        '*/customers' => Http::response([], 500),
    ]);

    $user = User::factory()->create(['linked_id' => null]);
    Cache::put("user.plain_password.{$user->id}", 'Secret@123');

    expect(fn () => (new ProcessVerifiedUser($user))->handle())->not->toThrow(Exception::class);
    expect($user->fresh()->linked_id)->toBeNull();
});

test('plain password cache entry is consumed after job runs', function () {
    Mail::fake();

    Http::fake([
        '*/customers' => Http::response(['status' => 'Success', 'customer_id' => 1], 200),
    ]);

    $user = User::factory()->create();
    Cache::put("user.plain_password.{$user->id}", 'Secret@123');

    (new ProcessVerifiedUser($user))->handle();

    expect(Cache::has("user.plain_password.{$user->id}"))->toBeFalse();
});
