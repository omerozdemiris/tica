<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SmsService
{
    public function sendSms($phone, $message)
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (str_starts_with($phone, '905') && strlen($phone) === 12) {
            $phone = substr($phone, 2);
        } elseif (str_starts_with($phone, '05') && strlen($phone) === 11) {
            $phone = substr($phone, 1);
        }

        // $url = 'https://apiv3.goldmesaj.net/api/sendSMS';
        // $data = [
        //     'username' => 'Yah*Ya16-',
        //     'password' => 'x*Sq-5yL*Km6q',
        //     'sdate' => '',
        //     'vperiod' => '48',
        //     'message' => [
        //         'sender' => 'O.GAZi BLD',
        //         'text' => $message,
        //         'utf8' => '1',
        //         'gsm' => [$phone],
        //     ]
        // ];

        // try {
        //     $response = Http::post($url, $data);
        //     $responseData = $response->json();
        //     if (isset($responseData['sonuc']) && $responseData['sonuc'] === 'true') {
        //         return true;
        //     }

        //     return false;
        // } catch (\Exception $e) {
        //     \Log::error("SMS gönderimi başarısız: " . $e->getMessage());
        //     return false;
        // }
    }
}
