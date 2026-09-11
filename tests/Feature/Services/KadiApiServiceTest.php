<?php

use App\Services\KadiApiService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Sleep;

beforeEach(function () {
    RateLimiter::clear('kadi-api-outbound');
});

test('self-throttles once outbound requests reach the per-minute cap', function () {
    Http::fake(['*' => Http::response(['data' => []], 200)]);
    Sleep::fake();

    $service = app(KadiApiService::class);

    // MAX_REQUESTS_PER_MINUTE is 15 — the 16th call must wait for a slot.
    for ($i = 0; $i < 16; $i++) {
        $service->get('stats/customers/played');
    }

    Sleep::assertSleptTimes(1);
    Http::assertSentCount(16);
});

test('does not throttle while under the per-minute cap', function () {
    Http::fake(['*' => Http::response(['data' => []], 200)]);
    Sleep::fake();

    $service = app(KadiApiService::class);

    for ($i = 0; $i < 15; $i++) {
        $service->get('stats/customers/played');
    }

    Sleep::assertNeverSlept();
});

test('retries and succeeds after KadiApi responds with 429', function () {
    Http::fake([
        '*' => Http::sequence()
            ->push(['message' => 'Too Many Attempts.'], 429, ['Retry-After' => '2'])
            ->push(['data' => ['foo' => 'bar']], 200),
    ]);
    Sleep::fake();

    $service = app(KadiApiService::class);

    $result = $service->get('stats/customers/played');

    expect($result)->toBe(['foo' => 'bar']);
    Http::assertSentCount(2);
});

test('post sends a unique Idempotency-Key header by default', function () {
    Http::fake(['*' => Http::response(['data' => []], 200)]);

    $service = app(KadiApiService::class);

    $service->post('customers/transactions/1');
    $service->post('customers/transactions/1');

    $keys = collect(Http::recorded())
        ->map(fn ($pair) => $pair[0]->header('Idempotency-Key')[0] ?? null)
        ->all();

    expect($keys)->toHaveCount(2)
        ->and($keys[0])->not->toBeEmpty()
        ->and($keys[0])->not->toBe($keys[1]);
});

test('post reuses an explicitly provided Idempotency-Key', function () {
    Http::fake(['*' => Http::response(['data' => []], 200)]);

    $service = app(KadiApiService::class);

    $service->post('customers/transactions/1', [], 'json', 'fixed-key');

    Http::assertSent(fn ($request) => $request->header('Idempotency-Key')[0] === 'fixed-key');
});

test('createCustomer sends a deterministic Idempotency-Key for the same account', function () {
    Http::fake(['*' => Http::response(['status' => 'Success', 'customer_id' => 1], 200)]);

    $service = app(KadiApiService::class);

    $service->createCustomer(['account_no' => 'ACC-123', 'name' => 'Test']);
    $service->createCustomer(['account_no' => 'ACC-123', 'name' => 'Test']);

    $keys = collect(Http::recorded())
        ->map(fn ($pair) => $pair[0]->header('Idempotency-Key')[0] ?? null)
        ->all();

    expect($keys)->toHaveCount(2)
        ->and($keys[0])->toBe($keys[1])
        ->and($keys[0])->toBe('customer-create-ACC-123');
});
