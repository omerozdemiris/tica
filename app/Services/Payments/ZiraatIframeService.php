<?php

namespace App\Services\Payments;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ZiraatIframeService
{
    protected array $config;

    public function __construct(?array $config = null)
    {
        $this->config = $config ?: config('stores.ziraat', []);
    }

    public function preparePaymentData(array $data): array
    {
        $merchantId = trim(Arr::get($this->config, 'merchant_id'));
        $merchantPassword = trim(Arr::get($this->config, 'merchant_password'));

        $params = [
            'HostMerchantId'       => $merchantId,
            'AmountCode'           => '949',
            'Amount'               => number_format(0.01, 2, '.', ''),
            'MerchantPassword'     => $merchantPassword,
            'TransactionId'        => $data['merchant_oid'],
            'OrderID'              => $data['merchant_oid'],
            'OrderDescription'     => 'Sipariş Ödemesi',
            'InstallmentCount'     => '',
            'TransactionType'      => 'Sale',
            'IsSecure'             => 'true',
            'AllowNotEnrolledCard' => 'false',
            'SuccessURL'           => $data['success_url'],
            'FailURL'              => $data['fail_url'],
        ];

        try {
            $response = Http::asForm()
                ->withHeaders(['Accept' => 'application/xml'])
                ->post('https://yonetim.ziraatbank.com.tr/v4/api/RegisterTransaction', $params);

            if ($response->successful()) {
                $xml = simplexml_load_string($response->body());

                $paymentToken = (string) $xml->PaymentToken;
                $errorCode = (string) $xml->ErrorCode;

                if (empty($errorCode) && !empty($paymentToken)) {
                    return [
                        'action' => 'https://yonetim.ziraatbank.com.tr/v4/SecurePayment?Ptkn=' . $paymentToken,
                        'inputs' => []
                    ];
                }

                $errMsg = (string) $xml->ResponseMessage ?: 'Bilinmeyen Banka Hatası';
                Log::error('Ziraat V4 Kayıt Hatası', ['xml' => $response->body()]);
                throw new \RuntimeException("Banka Hatası: {$errMsg} (Kod: {$errorCode})");
            }

            throw new \RuntimeException("Banka servislerine şu an ulaşılamıyor.");
        } catch (\Exception $e) {
            Log::error('Ziraat V4 Servis Hatası: ' . $e->getMessage());
            throw $e;
        }
    }

    public function verifyCallback(array $response): bool
    {
        $rc = $response['Rc'] ?? $response['Response'] ?? $response['ResultCode'] ?? '';
        return $rc === '0000' || $rc === '00' || $rc === 'Approved';
    }
}
