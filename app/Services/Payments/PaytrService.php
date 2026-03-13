<?php

namespace App\Services\Payments;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

class PaytrService
{
    protected array $config;

    public function __construct(?array $config = null)
    {
        $this->config = $config ?: config('stores.paytr', []);
    }

    /**
     * @param array{
     *     merchant_oid:string,
     *     email:string,
     *     name:string,
     *     address:string,
     *     phone:string|null,
     *     amount:float|int,
     *     basket:array<array{name:string,price:float|int,quantity:int}>,
     *     user_ip:string|null,
     *     success_url:string,
     *     fail_url:string,
     *     installment?:array{no_installment:int,max_installment:int},
     *     currency?:string,
     *     timeout_limit?:int,
     *     iframe_dark?:bool
     * } $payload
     */
    public function createToken(array $payload): array
    {
        $merchantId = Arr::get($this->config, 'merchant_id');
        $merchantKey = Arr::get($this->config, 'merchant_key');
        $merchantSalt = Arr::get($this->config, 'merchant_salt');

        if (!$merchantId || !$merchantKey || !$merchantSalt) {
            throw new RuntimeException('PayTR mağaza bilgileri eksik.');
        }

        $userIp = $payload['user_ip'] ?? request()->ip() ?? '127.0.0.1';
        $merchantOid = $payload['merchant_oid'];
        $email = $payload['email'];
        $amount = (float) $payload['amount'];
        $paymentAmount = (int) round($amount * 100);
        $basketItems = $this->formatBasket($payload['basket'] ?? []);
        $userBasket = base64_encode(json_encode($basketItems, JSON_UNESCAPED_UNICODE));
        $currency = strtoupper($payload['currency'] ?? 'TL');
        $installment = $payload['installment'] ?? ['no_installment' => 0, 'max_installment' => 0];
        $testMode = app()->environment('local', 'testing') ? 1 : 0;
        $timeout = $payload['timeout_limit'] ?? 30;
        $debug = config('app.debug') ? 1 : 0;

        $hashStr = $merchantId
            . $userIp
            . $merchantOid
            . $email
            . $paymentAmount
            . $userBasket
            . ($installment['no_installment'] ?? 0)
            . ($installment['max_installment'] ?? 0)
            . $currency
            . $testMode;

        $paytrToken = base64_encode(
            hash_hmac('sha256', $hashStr . $merchantSalt, $merchantKey, true)
        );

        $postData = [
            'merchant_id' => $merchantId,
            'user_ip' => $userIp,
            'merchant_oid' => $merchantOid,
            'email' => $email,
            'payment_amount' => $paymentAmount,
            'paytr_token' => $paytrToken,
            'user_basket' => $userBasket,
            'debug_on' => $debug,
            'no_installment' => $installment['no_installment'] ?? 0,
            'max_installment' => $installment['max_installment'] ?? 0,
            'user_name' => Str::limit($payload['name'] ?? 'Müşteri', 200, ''),
            'user_address' => Str::limit($payload['address'] ?? '', 500, ''),
            'user_phone' => $payload['phone'] ?? '',
            'merchant_ok_url' => $payload['success_url'],
            'merchant_fail_url' => $payload['fail_url'],
            'timeout_limit' => $timeout,
            'currency' => $currency,
            'test_mode' => $testMode,
            'language' => 'tr',
            'iframe_v2' => Arr::get($this->config, 'iframe_v2.enabled', false) ? 1 : 0,
            'iframe_v2_dark' => ($payload['iframe_dark'] ?? Arr::get($this->config, 'iframe_v2.dark_mode', false)) ? 1 : 0,
        ];

        $response = Http::asForm()->post('https://www.paytr.com/odeme/api/get-token', $postData);

        if (!$response->ok()) {
            Log::error('PayTR token isteği başarısız', [
                'body' => $response->body(),
                'status' => $response->status(),
            ]);
            throw new RuntimeException('Ödeme servisinden yanıt alınamadı.');
        }

        $result = $response->json();

        if (!is_array($result) || (($result['status'] ?? null) !== 'success')) {
            $reason = $result['reason'] ?? $result['message'] ?? 'Bilinmeyen hata';
            Log::warning('PayTR token isteği reddedildi', [
                'reason' => $reason,
                'response' => $result,
            ]);
            throw new RuntimeException($reason);
        }

        return $result;
    }

    public function verifyCallback(string $hash, string $merchantOid, string $status, string $totalAmount): bool
    {
        $merchantKey = Arr::get($this->config, 'merchant_key');
        $merchantSalt = Arr::get($this->config, 'merchant_salt');

        if (!$merchantKey || !$merchantSalt) {
            return false;
        }

        $calculated = base64_encode(
            hash_hmac('sha256', $merchantOid . $merchantSalt . $status . $totalAmount, $merchantKey, true)
        );

        return hash_equals($calculated, $hash);
    }

    /**
     * @param array<array{name:string,price:float|int,quantity:int}> $basket
     * @return array<int, array{0:string,1:string,2:int}>
     */
    protected function formatBasket(array $basket): array
    {
        if (empty($basket)) {
            return [
                ['Genel Toplam', '0.00', 1],
            ];
        }

        return collect($basket)
            ->map(function ($item) {
                $name = Str::limit($item['name'] ?? 'Ürün', 50, '');
                $price = number_format((float) ($item['price'] ?? 0), 2, '.', '');
                $quantity = (int) ($item['quantity'] ?? 1);

                return [$name, $price, $quantity];
            })
            ->values()
            ->all();
    }
}
