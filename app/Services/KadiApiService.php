<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class KadiApiService
{
    protected PendingRequest $http;

    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.kadi_api.url');
        $this->http = Http::withHeaders([
            'x-api-key' => config('services.kadi_api.key'),
            'Accept' => 'application/json',
        ])->baseUrl($this->baseUrl);
    }

    /**
     * Make a GET request
     *
     * @throws RequestException|ConnectionException
     */
    public function get(string $endpoint, array $query = []): array
    {
        return $this->http->get($endpoint, $query)
            ->throw()
            ->json('data') ?? [];
    }

    /**
     * Make a POST request
     *
     * @throws RequestException|ConnectionException
     */
    public function post(string $endpoint, array $data = [], string $bodyType = 'json'): array
    {
        $response = $bodyType === 'form'
            ? $this->http->asForm()->post($endpoint, $data)
            : $this->http->post($endpoint, $data);

        return $response->throw()->json('data') ?? [];
    }

    /**
     * Make a PUT request
     *
     * @throws RequestException|ConnectionException
     */
    public function put(string $endpoint, array $data = [], string $bodyType = 'json'): array
    {
        $response = $bodyType === 'form'
            ? $this->http->asForm()->put($endpoint, $data)
            : $this->http->put($endpoint, $data);

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
            ]);
        });
    }

    /**
     * Register a new customer in KadiApi.
     *
     * @throws RequestException|ConnectionException
     */
    public function createCustomer(array $data): array
    {
        return $this->http->post('customers', $data)
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
        return $this->http->delete($endpoint)
            ->throw()
            ->json('data') ?? [];
    }
}
