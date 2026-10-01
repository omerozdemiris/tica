<?php



namespace App\Services\Payments;



use Illuminate\Support\Arr;

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

        try {

            $clientId = trim(Arr::get($this->config, 'merchant_id'));

            $storeKey = trim(Arr::get($this->config, 'merchant_password'));



            $orderId = $data['merchant_oid'];

            $amount = number_format($data['amount'], 2, '.', '');



            $okUrl = $data['success_url'];

            $failUrl = $data['fail_url'];



            $rawParams = [

                'clientid'   => $clientId,

                'amount'     => $amount,

                'okurl'      => $okUrl,

                'failUrl'    => $failUrl,

                'TranType'   => 'Auth',

                'Instalment' => '',

                'callbackUrl'=> $okUrl,

                'currency'   => '949',

                'rnd'        => microtime(),

                'storetype'  => '3D_PAY_HOSTING',

                'hashAlgorithm' => 'ver3',

                'lang'       => 'tr',

                'BillToName'    => Arr::get($data, 'customer_name', 'Musteri'),

                'BillToCompany' => Arr::get($data, 'customer_company', 'Ürgüp Belediyesi'),

                'refreshtime'   => '5',

                'encoding'      => 'utf-8',

                'oid'           => $orderId,

                'email'         => Arr::get($data, 'customer_email', ''),

                'userid'        => Arr::get($data, 'user_id'),

                'ItemNumber1'   => Arr::get($data, 'user_id'),

            ];



            $params = array_map(function ($item) {

                if (is_string($item) && !mb_detect_encoding($item, 'utf-8', true)) {

                    return mb_convert_encoding($item, 'UTF-8', 'ISO-8859-9');

                }

                return $item;

            }, $rawParams);



            $hashParams = $params;

            unset($hashParams['encoding']);



            uksort($hashParams, function ($a, $b) {

                return strcasecmp($a, $b);

            });



            $hashval = '';

            foreach ($hashParams as $key => $value) {

                $escapedValue = str_replace('|', '\\|', str_replace('\\', '\\\\', (string) $value));

                $hashval .= $escapedValue . '|';

            }



            $escapedStoreKey = str_replace('|', '\\|', str_replace('\\', '\\\\', $storeKey));

            $hashval .= $escapedStoreKey;



            $calculatedHashValue = hash('sha512', $hashval);

            $hash = base64_encode(pack('H*', $calculatedHashValue));



            $params['hash'] = $hash;



            return [

                'action' => 'https://sanalpos2.ziraatbank.com.tr/fim/est3Dgate',

                'inputs' => $params,

            ];

        } catch (\Exception $e) {

            Log::error('Ziraat 3D_PAY_HOSTING Hash Hatası: ' . $e->getMessage());

            throw $e;

        }

    }



    public function verifyCallback(array $response): bool

    {

        $rc = $response['Rc'] ?? $response['Response'] ?? $response['ResultCode'] ?? '';

        return $rc === '0000' || $rc === '00' || $rc === 'Approved';

    }

}

