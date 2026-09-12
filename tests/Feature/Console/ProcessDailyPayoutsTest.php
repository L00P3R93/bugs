<?php

use App\Models\User;
use App\Notifications\DailyTargetReachedNotification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;

function fakeKadiStats(array $current): void
{
    Http::preventStrayRequests();
    Http::fake([
        '*/stats/customers/played' => Http::response(['data' => $current], 200),
    ]);
}

function testerWithWallet(array $walletAttributes = []): User
{
    $user = User::factory()->create();
    $user->forceFill(['linked_id' => 1])->save();
    $user->assignRole('Tester');
    $user->wallet->update(array_merge([
        'balance' => 0,
        'available_balance' => 0,
        'daily_games_played' => 0,
        'daily_earned' => 0,
    ], $walletAttributes));

    return $user->fresh('wallet');
}

test('disabling a game type earnings toggle pays other game types but not the disabled one', function () {
    config(['payouts.features.game_earnings.2_players' => false]);

    $tester = testerWithWallet();

    fakeKadiStats([
        'games' => ['total' => 7, '2_players' => 5, '3_players' => 2, '4_players' => 0],
        'tournament' => ['total' => 0],
        'jackpots' => ['total' => 0],
    ]);

    $this->artisan('payouts:process-daily')->assertSuccessful();

    $wallet = $tester->wallet->fresh();

    // 5 games at 2.50 excluded, 2 games at 3.50 included.
    expect((float) $wallet->daily_earned)->toBe(7.00)
        ->and($wallet->daily_games_played)->toBe(7);
});

test('disabling tournament earnings still counts games played but pays nothing for them', function () {
    config(['payouts.features.tournament_earnings_enabled' => false]);

    $tester = testerWithWallet();

    fakeKadiStats([
        'games' => ['total' => 0, '2_players' => 0, '3_players' => 0, '4_players' => 0],
        'tournament' => ['total' => 2],
        'jackpots' => ['total' => 0],
    ]);

    $this->artisan('payouts:process-daily')->assertSuccessful();

    $wallet = $tester->wallet->fresh();

    expect((float) $wallet->daily_earned)->toBe(0.0)
        ->and($wallet->daily_games_played)->toBe(2);
});

test('disabling jackpot earnings still counts jackpots played but pays nothing for them', function () {
    config(['payouts.features.jackpot_earnings_enabled' => false]);

    $tester = testerWithWallet();

    fakeKadiStats([
        'games' => ['total' => 0, '2_players' => 0, '3_players' => 0, '4_players' => 0],
        'tournament' => ['total' => 0],
        'jackpots' => ['total' => 3],
    ]);

    $this->artisan('payouts:process-daily')->assertSuccessful();

    $wallet = $tester->wallet->fresh();

    expect((float) $wallet->daily_earned)->toBe(0.0)
        ->and($wallet->daily_games_played)->toBe(3);
});

test('disabling target tracking for a game type prevents its target from being marked reached', function () {
    config(['payouts.features.target_tracking.2_players' => false]);
    Notification::fake();

    $tester = testerWithWallet();

    fakeKadiStats([
        'games' => ['total' => 40, '2_players' => 40, '3_players' => 0, '4_players' => 0],
        'tournament' => ['total' => 0],
        'jackpots' => ['total' => 0],
    ]);

    $this->artisan('payouts:process-daily')->assertSuccessful();

    $wallet = $tester->wallet->fresh();

    expect($wallet->daily_2p_games_target_reached)->toBeFalse()
        ->and($wallet->daily_target_reached)->toBeFalse();

    Notification::assertNothingSent();
});

test('target tracking left enabled still marks the target reached and notifies the tester', function () {
    Notification::fake();

    $tester = testerWithWallet();

    fakeKadiStats([
        'games' => ['total' => 40, '2_players' => 40, '3_players' => 0, '4_players' => 0],
        'tournament' => ['total' => 0],
        'jackpots' => ['total' => 0],
    ]);

    $this->artisan('payouts:process-daily')->assertSuccessful();

    $wallet = $tester->wallet->fresh();

    expect($wallet->daily_2p_games_target_reached)->toBeTrue()
        ->and($wallet->daily_target_reached)->toBeTrue();

    Notification::assertSentTo($tester, DailyTargetReachedNotification::class);
});

test('zero balance reset toggle zeroes balance at midnight reset when no target was met', function () {
    config(['payouts.features.zero_balance_reset_enabled' => true]);

    $tester = testerWithWallet([
        'balance' => 500,
        'available_balance' => 500,
        'daily_games_played' => 10,
        'last_daily_reset_at' => now()->subDay(),
    ]);

    fakeKadiStats([
        'games' => ['total' => 0, '2_players' => 0, '3_players' => 0, '4_players' => 0],
        'tournament' => ['total' => 0],
        'jackpots' => ['total' => 0],
    ]);

    $this->artisan('payouts:process-daily')->assertSuccessful();

    $wallet = $tester->wallet->fresh();

    expect((float) $wallet->balance)->toBe(0.0)
        ->and((float) $wallet->available_balance)->toBe(0.0);
});

test('zero balance reset toggle left disabled leaves balance untouched at midnight reset', function () {
    $tester = testerWithWallet([
        'balance' => 500,
        'available_balance' => 500,
        'daily_games_played' => 10,
        'last_daily_reset_at' => now()->subDay(),
    ]);

    fakeKadiStats([
        'games' => ['total' => 0, '2_players' => 0, '3_players' => 0, '4_players' => 0],
        'tournament' => ['total' => 0],
        'jackpots' => ['total' => 0],
    ]);

    $this->artisan('payouts:process-daily')->assertSuccessful();

    $wallet = $tester->wallet->fresh();

    expect((float) $wallet->balance)->toBe(500.0)
        ->and((float) $wallet->available_balance)->toBe(500.0);
});
