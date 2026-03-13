<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;
use App\Models\Store;

class ExampleSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $site = Setting::first() ?? new Setting();
        $site->fill([
            'logo' => '/assets/logo.png',
            'white_logo' => '/assets/logo-white.png',
            'favicon' => '/assets/favicon.ico',
            'title' => 'MacroTurk',
            'email' => 'info@macroturk.test',
            'phone' => '+90 555 000 00 00',
            'instagram' => 'https://instagram.com/macro_turk',
            'twitter' => 'https://twitter.com/macro_turk',
            'facebook' => 'https://facebook.com/macro_turk',
            'youtube' => 'https://youtube.com/@macro_turk',
            'linkedin' => 'https://linkedin.com/company/macro_turk',
            'whatsapp' => 'https://wa.me/905550000000',
            'google_iframe' => '',
        ]);
        $site->save();

        $store = Store::first() ?? new Store();
        $store->fill([
            'sell_enabled' => true,
            'auth_required' => false,
            'maintenance' => false,
            'auto_stock' => true,
            'meta_title' => 'MacroTurk',
            'meta_description' => 'MacroTurk mağazası örnek ayarları',
            'about' => 'MacroTurk: Modern ve hızlı e-ticaret deneyimi.',
            'privacy_policy' => 'Örnek gizlilik politikası metni.',
            'cookie_policy' => 'Örnek çerez politikası metni.',
            'distance_selling' => 'Örnek mesafeli satış sözleşmesi metni.',
        ]);
        $store->save();
    }
}


