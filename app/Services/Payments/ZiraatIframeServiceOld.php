<?php

namespace App\Services\Payments;

use Illuminate\Support\Facades\Log;

class ZiraatIframeService
{
    private string $clientId = '191360440';
    private string $storeKey = 'macroturk77*+';
    private string $gatewayUrl = 'https://sanalpos2.ziraatbank.com.tr/fim/est3Dgate';

    public function preparePaymentData(array $data): array
    {
        Log::channel('ziraat')->info('[1] preparePaymentData çağrıldı', [
            'merchant_oid' => $data['merchant_oid'] ?? null,
            'amount'       => $data['amount'] ?? null,
        ]);

        $params = [
            'clientid'   => $this->clientId,
            'storetype'  => '3d_pay_hosting',
            'amount'     => number_format((float) $data['amount'], 2, '.', ''),
            'currency'   => '949',
            'oid'        => (string) $data['merchant_oid'],
            'okUrl'      => $data['success_url'],
            'failUrl'    => $data['fail_url'],
            'lang'       => 'tr',
            'rnd'        => microtime(true),
            'islemtipi'  => 'Auth',
            'taksit'     => '',
        ];

        Log::channel('ziraat')->info('[2] Params hazırlandı', [
            'params' => array_merge($params, ['clientid' => '***masked***']),
        ]);

        try {
            $hash = $this->calculateHash($params);

            $inputs = array_merge($params, ['hash' => $hash]);

            Log::channel('ziraat')->info('[3] Hash hesaplandı, form hazır', [
                'action'       => $this->gatewayUrl,
                'hash_preview' => substr($hash, 0, 12) . '...',
                'input_keys'   => array_keys($inputs),
            ]);

            return [
                'action' => $this->gatewayUrl,
                'inputs' => $inputs,
            ];
        } catch (\Throwable $e) {
            Log::channel('ziraat')->error('[HATA] preparePaymentData exception', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'trace'   => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    private function calculateHash(array $params): string
{
    $hashFields = [
        'clientid',
        'oid',
        'amount',
        'okUrl',
        'failUrl',
        'islemtipi',
        'taksit',
        'rnd',
        'currency',
        'storetype',
    ];

    $hashval = '';
    foreach ($hashFields as $field) {
        $val      = isset($params[$field]) ? (string) $params[$field] : '';
        $escaped  = str_replace('|', '\\|', str_replace('\\', '\\\\', $val));
        $hashval .= $escaped . '|';
    }

    $escapedKey = str_replace('|', '\\|', str_replace('\\', '\\\\', $this->storeKey));
    $hashval   .= $escapedKey;

    Log::channel('ziraat')->debug('[hash] Ham string', [
        'hashval_preview' => substr($hashval, 0, 120) . '...',
    ]);

    return base64_encode(pack('H*', hash('sha512', $hashval)));
}

    public function verifyCallback(array $response): bool
    {
        $mdStatus   = $response['mdStatus'] ?? '';
        $response3D = $response['Response'] ?? '';
        $oid        = $response['oid'] ?? '-';

        Log::channel('ziraat')->info('[callback] Gelen veri', [
            'oid'        => $oid,
            'mdStatus'   => $mdStatus,
            'Response'   => $response3D,
            'AuthCode'   => $response['AuthCode'] ?? null,
            'ErrMsg'     => $response['ErrMsg'] ?? null,
            'maskedPan'  => $response['maskedCreditCard'] ?? null,
            'raw'        => $response,
        ]);

        // mdStatus 1 = tam 3D, 2/3/4 = kısmi (bankaya göre kabul edilebilir)
        $isValid = in_array($mdStatus, ['1', '2', '3', '4'], true)
                && $response3D === 'Approved';

        if ($isValid) {
            Log::channel('ziraat')->info('[callback] Ödeme BAŞARILI', [
                'oid'      => $oid,
                'authCode' => $response['AuthCode'] ?? null,
            ]);
        } else {
            Log::channel('ziraat')->warning('[callback] Ödeme BAŞARISIZ', [
                'oid'      => $oid,
                'mdStatus' => $mdStatus,
                'Response' => $response3D,
                'ErrMsg'   => $response['ErrMsg'] ?? null,
            ]);
        }

        return $isValid;
    }
}