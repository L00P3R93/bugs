<?php

use App\Services\GameApiService;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config([
        'services.game_api.url' => 'https://game-api.test',
        'services.game_api.key' => 'test-key',
        'services.game_api.openssl_key' => str_repeat('a', 32),
        'services.game_api.openssl_method' => 'AES-128-CBC',
    ]);
});

test('POST requests carry a unique Idempotency-Key header by default', function () {
    Http::fake(['*' => Http::response(['status' => 'Success'], 200)]);

    $service = app(GameApiService::class);

    $service->createGameWallet('game-1');
    $service->createGameWallet('game-2');

    $keys = collect(Http::recorded())
        ->map(fn ($pair) => $pair[0]->header('Idempotency-Key')[0] ?? null)
        ->all();

    expect($keys)->toHaveCount(2)
        ->and($keys[0])->not->toBeEmpty()
        ->and($keys[0])->not->toBe($keys[1]);
});

test('GET requests do not carry an Idempotency-Key header', function () {
    Http::fake(['*' => Http::response(['data' => []], 200)]);

    $service = app(GameApiService::class);

    $service->listCustomers();

    Http::assertSent(fn ($request) => $request->header('Idempotency-Key') === []);
});

test('createCustomer sends a deterministic Idempotency-Key for the same account', function () {
    Http::fake(['*' => Http::response(['status' => 'Success'], 200)]);

    $service = app(GameApiService::class);

    $service->createCustomer(['account_no' => 'ACC-456', 'name' => 'Test']);
    $service->createCustomer(['account_no' => 'ACC-456', 'name' => 'Test']);

    $keys = collect(Http::recorded())
        ->map(fn ($pair) => $pair[0]->header('Idempotency-Key')[0] ?? null)
        ->all();

    expect($keys)->toHaveCount(2)
        ->and($keys[0])->toBe($keys[1])
        ->and($keys[0])->toBe('customer-create-ACC-456');
});
