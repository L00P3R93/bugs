<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Sleep;
use Illuminate\Support\Str;

class KadiApiService
{
    protected PendingRequest $http;

    protected string $baseUrl;

    /**
     * KadiApi enforces 20 requests/minute on its side. We self-throttle
     * below that so bulk jobs (e.g. hourly payouts) don't trip 429s.
     */
    private const MAX_REQUESTS_PER_MINUTE = 15;

    private const RATE_LIMITER_KEY = 'kadi-api-outbound';

    public function __construct()
    {
        $this->baseUrl = config('services.kadi_api.url');
        $this->http = Http::withHeaders([
            'x-api-key' => config('services.kadi_api.key'),
            'Accept' => 'application/json',
        ])
            ->baseUrl($this->baseUrl)
            ->retry(3, function (int $attempt, \Exception $exception) {
                if ($exception instanceof RequestException && $exception->response->status() === 429) {
                    $retryAfter = (int) $exception->response->header('Retry-After');

                    return max($retryAfter, 1) * 1000;
                }

                return 1000 * $attempt;
            }, when: fn (\Exception $exception) => $exception instanceof ConnectionException
                || ($exception instanceof RequestException && $exception->response->status() === 429));
    }

    /**
     * Block until an outbound request slot is available, keeping us under
     * KadiApi's rate limit even across multiple requests in the same run.
     */
    private function throttle(): void
    {
        if (RateLimiter::tooManyAttempts(self::RATE_LIMITER_KEY, self::MAX_REQUESTS_PER_MINUTE)) {
            Sleep::for(RateLimiter::availableIn(self::RATE_LIMITER_KEY))->seconds();
        }

        RateLimiter::hit(self::RATE_LIMITER_KEY, 60);
    }

    /**
     * Make a GET request
     *
     * @throws RequestException|ConnectionException
     */
    public function get(string $endpoint, array $query = []): array
    {
        $this->throttle();

        return $this->http->get($endpoint, $query)
            ->throw()
            ->json('data') ?? [];
    }

    /**
     * Make a POST request
     *
     * @throws RequestException|ConnectionException
     */
    public function post(string $endpoint, array $data = [], string $bodyType = 'json', ?string $idempotencyKey = null): array
    {
        $this->throttle();

        // Clone before adding headers — withHeaders() mutates the underlying
        // PendingRequest in place, and $this->http is reused across calls.
        $request = (clone $this->http)->withHeaders([
            'Idempotency-Key' => $idempotencyKey ?? Str::uuid()->toString(),
        ]);

        $response = $bodyType === 'form'
            ? $request->asForm()->post($endpoint, $data)
            : $request->post($endpoint, $data);

        return $response->throw()->json('data') ?? [];
    }

    /**
     * Make a PUT request
     *
     * @throws RequestException|ConnectionException
     */
    public function put(string $endpoint, array $data = [], string $bodyType = 'json', ?string $idempotencyKey = null): array
    {
        $this->throttle();

        // Clone before adding headers — withHeaders() mutates the underlying
        // PendingRequest in place, and $this->http is reused across calls.
        $request = (clone $this->http)->withHeaders([
            'Idempotency-Key' => $idempotencyKey ?? Str::uuid()->toString(),
        ]);

        $response = $bodyType === 'form'
            ? $request->asForm()->put($endpoint, $data)
            : $request->put($endpoint, $data);

        return $response->throw()->json('data') ?? [];
    }

    /**
     * Get player statistics for a given date, cached for 1 hour.
     *
     * @throws RequestException|ConnectionException
     */
    public function getPlayerStats(int $linkedId, string $date): array
    {
        $cacheKey = "kadiapi_stats_{$linkedId}_{$date}";

        return Cache::remember($cacheKey, now()->addHour(), function () use ($linkedId, $date) {
            return $this->post('stats/customers/played', [
                'customer_id' => $linkedId,
                'start_date' => $date.' 00:00:00',
                'end_date' => $date.' 23:59:59',
            ], idempotencyKey: "stats-{$linkedId}-{$date}");
        });
    }

    /**
     * Register a new customer in KadiApi.
     *
     * @throws RequestException|ConnectionException
     */
    public function createCustomer(array $data): array
    {
        $this->throttle();

        // Deterministic key: retries for the same customer reuse the same key
        // (so KadiApi returns the original result instead of erroring on a
        // duplicate), while different customers always get distinct keys.
        $key = 'customer-create-'.($data['account_no'] ?? $data['google_id'] ?? Str::uuid()->toString());

        return (clone $this->http)->withHeaders(['Idempotency-Key' => $key])
            ->post('customers', $data)
            ->throw()
            ->json() ?? [];
    }

    /**
     * Make a DELETE request
     *
     * @throws RequestException|ConnectionException
     */
    public function delete(string $endpoint): array
    {
        $this->throttle();

        return $this->http->delete($endpoint)
            ->throw()
            ->json('data') ?? [];
    }
}
