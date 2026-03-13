<?php

namespace App\Services\Erp;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ErpApiClient
{
    public function __construct(
        protected ?string $baseUrl = null,
        protected ?string $apiToken = null,
        protected ?string $companyId = null,
        protected ?string $branchId = null,
    ) {
        $this->baseUrl = $baseUrl ?: rtrim(config('erp.base_url'), '/');
        $this->apiToken = $apiToken ?: config('erp.api_token');
        $this->companyId = $companyId ?: (string) config('erp.company_id');
        $this->branchId = $branchId ?: (string) config('erp.branch_id');
    }

    /**
    * Temel GET isteği.
    *
    * @throws RequestException
    */
    public function get(string $uri, array $query = []): array
    {
        return $this->request('GET', $uri, [
            'query' => $query,
        ]);
    }

    /**
    * Temel POST isteği.
    *
    * @throws RequestException
    */
    public function post(string $uri, array $payload = []): array
    {
        return $this->request('POST', $uri, [
            'json' => $payload,
        ]);
    }

    /**
    * Login endpoint'i üzerinden token almak için yardımcı metod.
    *
    * Not: Şu an doğrudan kullanılmıyor; ilk aşamada sabit token .env'den okunuyor.
    */
    public function login(array $credentials = []): ?string
    {
        $email = $credentials['email'] ?? config('erp.auth.email');
        $password = $credentials['password'] ?? config('erp.auth.password');
        $deviceName = $credentials['device_name'] ?? config('erp.auth.device_name', 'Laravel ECommerce');

        if (!$email || !$password) {
            return null;
        }

        $response = Http::asJson()
            ->acceptJson()
            ->baseUrl($this->baseUrl)
            ->post('/api/auth/login', [
                'email' => $email,
                'password' => $password,
                'device_name' => $deviceName,
                'revoke_other_tokens' => false,
            ])
            ->throw();

        $token = Arr::get($response->json(), 'data.token');

        if ($token) {
            $this->apiToken = $token;
        }

        return $token;
    }

    /**
    * Düşük seviye istek wrapper'ı.
    *
    * @throws RequestException
    */
    protected function request(string $method, string $uri, array $options = []): array
    {
        if (!config('erp.enabled')) {
            throw new \RuntimeException('ERP entegrasyonu devre dışı (erp.enabled=false).');
        }

        if (!$this->apiToken) {
            Log::warning('ERP API çağrısı için api_token tanımlı değil.');
        }

        $request = Http::withHeaders($this->buildDefaultHeaders())
            ->acceptJson()
            ->baseUrl($this->baseUrl);

        if (isset($options['query']) && is_array($options['query'])) {
            $request = $request->withQueryParameters($options['query']);
        }

        if (isset($options['json']) && is_array($options['json'])) {
            $request = $request->asJson();
        }

        $response = $request->send($method, $uri, Arr::only($options, ['json']));

        if ($response->failed()) {
            Log::warning('ERP API çağrısı başarısız', [
                'method' => $method,
                'uri' => $uri,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            $response->throw();
        }

        $json = $response->json();

        return is_array($json) ? $json : [];
    }

    protected function buildDefaultHeaders(): array
    {
        $headers = [
            'Accept' => 'application/json',
        ];

        if ($this->apiToken) {
            $headers['Authorization'] = 'Bearer ' . $this->apiToken;
        }

        if ($this->companyId) {
            $headers['X-Company-ID'] = $this->companyId;
        }

        if ($this->branchId) {
            $headers['X-Branch-ID'] = $this->branchId;
        }

        return $headers;
    }
}

