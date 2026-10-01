<?php



namespace App\Services\Payments;



use Illuminate\Support\Arr;

use Illuminate\Support\Facades\Log;



class ZiraatService

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



        $orderId = $data['merchant_oid'];

        // $amount = (float) $data['amount'];

        $amount = 0.01;



        // Önemli: Banka bazen Oid'yi boş dönerse URL'den yakalamak için ekliyoruz

        $okUrl = $data['success_url'] . (str_contains($data['success_url'], '?') ? '&' : '?') . 'oid=' . $orderId;

        $failUrl = $data['fail_url'] . (str_contains($data['fail_url'], '?') ? '&' : '?') . 'oid=' . $orderId;



        $cardNumber = str_replace([' ', '-', '.'], '', $data['card_number']);

        $cvv = trim($data['cvv']);



        $month = sprintf('%02d', (int)$data['expiry_month']);

        $year = $data['expiry_year'];

        $fullYear = (strlen($year) == 2) ? '20' . $year : $year;

        $expiry = $fullYear . $month;



        $currencyCode = "949";

        $storeType = "3d_pay";

        $transactionType = "Sale";



        $clientIp = request()->ip();

        if ($clientIp === '::1' || str_contains($clientIp, ':')) {

            $clientIp = '127.0.0.1';
        }



        // Hash hesaplama (Örnekteki calculateHash ile birebir aynı sıra)

        $amountInt = (int)round($amount * 100);

        $hashString = $merchantId . $orderId . $currencyCode . $amountInt . $okUrl . $failUrl . $merchantPassword;

        $hashData = base64_encode(hash('sha512', $hashString, true));



        // XML oluşturma (Örnekteki sıralama ve htmlspecialchars kullanımı)

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';

        $xml .= '<VposRequest>';

        $xml .= '<MerchantId>' . htmlspecialchars($merchantId) . '</MerchantId>';

        $xml .= '<HashData>' . htmlspecialchars($hashData) . '</HashData>';

        $xml .= '<OrderId>' . htmlspecialchars($orderId) . '</OrderId>';

        $xml .= '<TransactionType>' . $transactionType . '</TransactionType>';

        $xml .= '<Pan>' . htmlspecialchars($cardNumber) . '</Pan>';

        $xml .= '<Cvv>' . htmlspecialchars($cvv) . '</Cvv>';

        $xml .= '<Expiry>' . htmlspecialchars($expiry) . '</Expiry>';

        $xml .= '<CurrencyAmount>' . htmlspecialchars($amount) . '</CurrencyAmount>';

        $xml .= '<CurrencyCode>' . htmlspecialchars($currencyCode) . '</CurrencyCode>';

        $xml .= '<ClientIp>' . htmlspecialchars($clientIp) . '</ClientIp>';

        $xml .= '<SuccessUrl>' . htmlspecialchars($okUrl) . '</SuccessUrl>';

        $xml .= '<FailUrl>' . htmlspecialchars($failUrl) . '</FailUrl>';

        $xml .= '<StoreType>' . htmlspecialchars($storeType) . '</StoreType>';

        $xml .= '<TransactionDeviceSource>0</TransactionDeviceSource>';

        $xml .= '</VposRequest>';



        $action = 'https://sanalpos.ziraatbank.com.tr/v4/v3/VposThreeDPay.aspx';



        return [

            'action' => $action,

            'inputs' => [

                'prmstr' => $xml

            ]

        ];
    }



    public function verifyCallback(array $response): bool

    {

        $resultCode = $response['ResultCode'] ?? $response['Response'] ?? $response['ProcReturnCode'] ?? '';

        return $resultCode === '0000' || $resultCode === 'Approved';
    }
}
