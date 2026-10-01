<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Visitor;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Torann\GeoIP\Facades\GeoIP;

class LiveVisitorController extends Controller
{
    public function index()
    {
        return view('admin.pages.live_visitors.index');
    }

    public function getLiveVisitors()
    {
        $last60Seconds = Carbon::now()->subSeconds(60);

        $liveVisitors = Visitor::where('visited_at', '>=', $last60Seconds)
            ->select('ip_address', 'device', 'platform', 'visited_at')
            ->get()
            ->groupBy('ip_address')
            ->map(function ($group) {
                $first = $group->sortByDesc('visited_at')->first();
                $locationData = $this->getLocationFromIP($first->ip_address);

                $visitedAt = is_string($first->visited_at)
                    ? Carbon::parse($first->visited_at)->format('H:i:s')
                    : $first->visited_at->format('H:i:s');

                return [
                    'ip' => $first->ip_address,
                    'city' => $locationData['city'] ?? 'Bilinmiyor',
                    'device' => ucfirst($first->device),
                    'platform' => ucfirst($first->platform),
                    'visited_at' => $visitedAt,
                    'lat' => $locationData['lat'] ?? null,
                    'lng' => $locationData['lng'] ?? null
                ];
            })
            ->values();

        return response()->json([
            'count' => $liveVisitors->count(),
            'visitors' => $liveVisitors
        ]);
    }

    private function getLocationFromIP($ip)
    {
        $isPrivateIP = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false;

        $cacheKey = 'ip_location_' . md5($ip);
        return Cache::remember($cacheKey, 86400, function () use ($ip, $isPrivateIP) {
            try {
                if ($isPrivateIP) {
                    try {
                        $publicIPResponse = Http::timeout(2)->get('https://api.ipify.org?format=json');
                        if ($publicIPResponse->successful()) {
                            $publicIPData = $publicIPResponse->json();
                            $publicIP = $publicIPData['ip'] ?? null;
                            if ($publicIP && $publicIP !== $ip) {
                                $ip = $publicIP;
                            }
                        }
                    } catch (\Exception $e) {
                    }
                }

                $location = GeoIP::getLocation($ip);

                if ($location && $location->default === false) {
                    $city = $location->city ?? 'Bilinmiyor';
                    $lat = $location->lat ?? null;
                    $lng = $location->lon ?? null;

                    if ($city && $city !== 'Bilinmiyor' && ($lat === null || $lng === null)) {
                        $cityCoords = $this->getCityCoordinatesByName($city);
                        if ($cityCoords['lat'] !== null && $cityCoords['lng'] !== null) {
                            $lat = $cityCoords['lat'];
                            $lng = $cityCoords['lng'];
                        }
                    }

                    return [
                        'city' => $city ?: 'Bilinmiyor',
                        'lat' => $lat ? (float)$lat : null,
                        'lng' => $lng ? (float)$lng : null
                    ];
                }
            } catch (\Exception $e) {
            }

            return ['city' => 'Bilinmiyor', 'lat' => null, 'lng' => null];
        });
    }

    private function getCityFromIP($ip)
    {
        $location = $this->getLocationFromIP($ip);
        return $location['city'];
    }

    private function getCityCoordinatesByName($cityName)
    {
        if (!$cityName || $cityName === 'Bilinmiyor') {
            return ['lat' => null, 'lng' => null];
        }

        $cityMap = $this->getCityNameToCoordinatesMap();
        $normalizedCityName = $this->normalizeCityName($cityName);

        if (isset($cityMap[$normalizedCityName])) {
            return $cityMap[$normalizedCityName];
        }

        foreach ($cityMap as $mapCity => $coords) {
            if (
                stripos($normalizedCityName, $mapCity) !== false ||
                stripos($mapCity, $normalizedCityName) !== false
            ) {
                return $coords;
            }
        }

        return ['lat' => null, 'lng' => null];
    }

    private function normalizeCityName($cityName)
    {
        $cityName = trim($cityName);
        $cityName = mb_strtoupper($cityName, 'UTF-8');

        $replacements = [
            'İ' => 'I',
            'Ş' => 'S',
            'Ğ' => 'G',
            'Ü' => 'U',
            'Ö' => 'O',
            'Ç' => 'C'
        ];

        foreach ($replacements as $from => $to) {
            $cityName = str_replace($from, $to, $cityName);
        }

        return $cityName;
    }

    private function getCityNameToCoordinatesMap()
    {
        $coords = $this->getCityCoordinates();
        $cityNames = [
            '01' => 'ADANA',
            '02' => 'ADIYAMAN',
            '03' => 'AFYONKARAHISAR',
            '04' => 'AGRI',
            '05' => 'AMASYA',
            '06' => 'ANKARA',
            '07' => 'ANTALYA',
            '08' => 'ARTVIN',
            '09' => 'AYDIN',
            '10' => 'BALIKESIR',
            '11' => 'BILECIK',
            '12' => 'BINGOL',
            '13' => 'BITLIS',
            '14' => 'BOLU',
            '15' => 'BURDUR',
            '16' => 'BURSA',
            '17' => 'CANAKKALE',
            '18' => 'CANKIRI',
            '19' => 'CORUM',
            '20' => 'DENIZLI',
            '21' => 'DIYARBAKIR',
            '22' => 'EDIRNE',
            '23' => 'ELAZIG',
            '24' => 'ERZINCAN',
            '25' => 'ERZURUM',
            '26' => 'ESKISEHIR',
            '27' => 'GAZIANTEP',
            '28' => 'GIRESUN',
            '29' => 'GUMUSHANE',
            '30' => 'HAKKARI',
            '31' => 'HATAY',
            '32' => 'ISPARTA',
            '33' => 'MERSIN',
            '34' => 'ISTANBUL',
            '35' => 'IZMIR',
            '36' => 'KARS',
            '37' => 'KASTAMONU',
            '38' => 'KAYSERI',
            '39' => 'KIRKLARELI',
            '40' => 'KIRSEHIR',
            '41' => 'KOCAELI',
            '42' => 'KONYA',
            '43' => 'KUTAHYA',
            '44' => 'MALATYA',
            '45' => 'MANISA',
            '46' => 'KAHRAMANMARAS',
            '47' => 'MARDIN',
            '48' => 'MUGLA',
            '49' => 'MUS',
            '50' => 'NEVSEHIR',
            '51' => 'NIGDE',
            '52' => 'ORDU',
            '53' => 'RIZE',
            '54' => 'SAKARYA',
            '55' => 'SAMSUN',
            '56' => 'SIIRT',
            '57' => 'SINOP',
            '58' => 'SIVAS',
            '59' => 'TEKIRDAG',
            '60' => 'TOKAT',
            '61' => 'TRABZON',
            '62' => 'TUNCELI',
            '63' => 'SANLIURFA',
            '64' => 'USAK',
            '65' => 'VAN',
            '66' => 'YOZGAT',
            '67' => 'ZONGULDAK',
            '68' => 'AKSARAY',
            '69' => 'BAYBURT',
            '70' => 'KARAMAN',
            '71' => 'KIRIKKALE',
            '72' => 'BATMAN',
            '73' => 'SIRNAK',
            '74' => 'BARTIN',
            '75' => 'ARDAHAN',
            '76' => 'IGDIR',
            '77' => 'YALOVA',
            '78' => 'KARABUK',
            '79' => 'KILIS',
            '80' => 'OSMANIYE',
            '81' => 'DUZCE'
        ];

        $map = [];
        foreach ($cityNames as $plate => $name) {
            $map[$name] = $coords[$plate];
        }

        return $map;
    }

    private function getCityCoordinates()
    {
        return [
            '01' => ['lat' => 37.0000, 'lng' => 35.3213],
            '02' => ['lat' => 37.7648, 'lng' => 38.2786],
            '03' => ['lat' => 38.7507, 'lng' => 30.5567],
            '04' => ['lat' => 39.7191, 'lng' => 43.0503],
            '05' => ['lat' => 40.6499, 'lng' => 35.8353],
            '06' => ['lat' => 39.9334, 'lng' => 32.8597],
            '07' => ['lat' => 36.8969, 'lng' => 30.7133],
            '08' => ['lat' => 41.1828, 'lng' => 41.8183],
            '09' => ['lat' => 37.8444, 'lng' => 27.8458],
            '10' => ['lat' => 39.6484, 'lng' => 27.8826],
            '11' => ['lat' => 40.1419, 'lng' => 29.9793],
            '12' => ['lat' => 38.8847, 'lng' => 40.4939],
            '13' => ['lat' => 38.4006, 'lng' => 42.1095],
            '14' => ['lat' => 40.7350, 'lng' => 31.6061],
            '15' => ['lat' => 37.7203, 'lng' => 30.2908],
            '16' => ['lat' => 40.1826, 'lng' => 29.0660],
            '17' => ['lat' => 40.1467, 'lng' => 26.4086],
            '18' => ['lat' => 40.6013, 'lng' => 33.6134],
            '19' => ['lat' => 40.5506, 'lng' => 34.9556],
            '20' => ['lat' => 37.7765, 'lng' => 29.0864],
            '21' => ['lat' => 37.9144, 'lng' => 40.2110],
            '22' => ['lat' => 41.6818, 'lng' => 26.5623],
            '23' => ['lat' => 38.6810, 'lng' => 39.2264],
            '24' => ['lat' => 39.7500, 'lng' => 39.5000],
            '25' => ['lat' => 39.9000, 'lng' => 41.2700],
            '26' => ['lat' => 39.7767, 'lng' => 30.5206],
            '27' => ['lat' => 37.0662, 'lng' => 37.3833],
            '28' => ['lat' => 40.9128, 'lng' => 38.3895],
            '29' => ['lat' => 40.4608, 'lng' => 39.4814],
            '30' => ['lat' => 37.5744, 'lng' => 43.7408],
            '31' => ['lat' => 36.2000, 'lng' => 36.1667],
            '32' => ['lat' => 37.7644, 'lng' => 30.5522],
            '33' => ['lat' => 36.8121, 'lng' => 34.6415],
            '34' => ['lat' => 41.0082, 'lng' => 28.9784],
            '35' => ['lat' => 38.4192, 'lng' => 27.1287],
            '36' => ['lat' => 40.6167, 'lng' => 43.1000],
            '37' => ['lat' => 41.3887, 'lng' => 33.7827],
            '38' => ['lat' => 38.7312, 'lng' => 35.4787],
            '39' => ['lat' => 41.7333, 'lng' => 27.2167],
            '40' => ['lat' => 39.1458, 'lng' => 34.1639],
            '41' => ['lat' => 40.8533, 'lng' => 29.8815],
            '42' => ['lat' => 37.8714, 'lng' => 32.4846],
            '43' => ['lat' => 39.4167, 'lng' => 29.9833],
            '44' => ['lat' => 38.3552, 'lng' => 38.3095],
            '45' => ['lat' => 38.6191, 'lng' => 27.4289],
            '46' => ['lat' => 37.5858, 'lng' => 36.9371],
            '47' => ['lat' => 37.3129, 'lng' => 40.7339],
            '48' => ['lat' => 37.2153, 'lng' => 28.3636],
            '49' => ['lat' => 38.7317, 'lng' => 41.4911],
            '50' => ['lat' => 38.6244, 'lng' => 34.7144],
            '51' => ['lat' => 37.9667, 'lng' => 34.6833],
            '52' => ['lat' => 40.9839, 'lng' => 37.8764],
            '53' => ['lat' => 41.0201, 'lng' => 40.5234],
            '54' => ['lat' => 40.7569, 'lng' => 30.3783],
            '55' => ['lat' => 41.2928, 'lng' => 36.3313],
            '56' => ['lat' => 37.9333, 'lng' => 41.9500],
            '57' => ['lat' => 42.0231, 'lng' => 35.1531],
            '58' => ['lat' => 39.7477, 'lng' => 37.0179],
            '59' => ['lat' => 40.9833, 'lng' => 27.5167],
            '60' => ['lat' => 40.3167, 'lng' => 36.5500],
            '61' => ['lat' => 41.0027, 'lng' => 39.7168],
            '62' => ['lat' => 39.1079, 'lng' => 39.5401],
            '63' => ['lat' => 37.1591, 'lng' => 38.7969],
            '64' => ['lat' => 38.6823, 'lng' => 29.4082],
            '65' => ['lat' => 38.4891, 'lng' => 43.4089],
            '66' => ['lat' => 39.8181, 'lng' => 34.8147],
            '67' => ['lat' => 41.4511, 'lng' => 31.7944],
            '68' => ['lat' => 38.3687, 'lng' => 34.0327],
            '69' => ['lat' => 40.2552, 'lng' => 40.2249],
            '70' => ['lat' => 37.1759, 'lng' => 33.2214],
            '71' => ['lat' => 39.8468, 'lng' => 33.5153],
            '72' => ['lat' => 37.8812, 'lng' => 41.1351],
            '73' => ['lat' => 37.5164, 'lng' => 42.4611],
            '74' => ['lat' => 41.6344, 'lng' => 32.3375],
            '75' => ['lat' => 41.1105, 'lng' => 42.7022],
            '76' => ['lat' => 39.9237, 'lng' => 44.0450],
            '77' => ['lat' => 40.6551, 'lng' => 29.2769],
            '78' => ['lat' => 41.2061, 'lng' => 32.6204],
            '79' => ['lat' => 36.7184, 'lng' => 37.1212],
            '80' => ['lat' => 37.0742, 'lng' => 36.2473],
            '81' => ['lat' => 40.8438, 'lng' => 31.1565],
        ];
    }
}
