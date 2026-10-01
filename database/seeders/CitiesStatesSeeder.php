<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\City;
use App\Models\State;

class CitiesStatesSeeder extends Seeder
{
    public function run(): void
    {
        // Türkiye'deki tüm iller ve plaka kodları
        $cities = [
            ['name' => 'Adana', 'plate_code' => '01'],
            ['name' => 'Adıyaman', 'plate_code' => '02'],
            ['name' => 'Afyonkarahisar', 'plate_code' => '03'],
            ['name' => 'Ağrı', 'plate_code' => '04'],
            ['name' => 'Amasya', 'plate_code' => '05'],
            ['name' => 'Ankara', 'plate_code' => '06'],
            ['name' => 'Antalya', 'plate_code' => '07'],
            ['name' => 'Artvin', 'plate_code' => '08'],
            ['name' => 'Aydın', 'plate_code' => '09'],
            ['name' => 'Balıkesir', 'plate_code' => '10'],
            ['name' => 'Bilecik', 'plate_code' => '11'],
            ['name' => 'Bingöl', 'plate_code' => '12'],
            ['name' => 'Bitlis', 'plate_code' => '13'],
            ['name' => 'Bolu', 'plate_code' => '14'],
            ['name' => 'Burdur', 'plate_code' => '15'],
            ['name' => 'Bursa', 'plate_code' => '16'],
            ['name' => 'Çanakkale', 'plate_code' => '17'],
            ['name' => 'Çankırı', 'plate_code' => '18'],
            ['name' => 'Çorum', 'plate_code' => '19'],
            ['name' => 'Denizli', 'plate_code' => '20'],
            ['name' => 'Diyarbakır', 'plate_code' => '21'],
            ['name' => 'Edirne', 'plate_code' => '22'],
            ['name' => 'Elazığ', 'plate_code' => '23'],
            ['name' => 'Erzincan', 'plate_code' => '24'],
            ['name' => 'Erzurum', 'plate_code' => '25'],
            ['name' => 'Eskişehir', 'plate_code' => '26'],
            ['name' => 'Gaziantep', 'plate_code' => '27'],
            ['name' => 'Giresun', 'plate_code' => '28'],
            ['name' => 'Gümüşhane', 'plate_code' => '29'],
            ['name' => 'Hakkari', 'plate_code' => '30'],
            ['name' => 'Hatay', 'plate_code' => '31'],
            ['name' => 'Isparta', 'plate_code' => '32'],
            ['name' => 'Mersin', 'plate_code' => '33'],
            ['name' => 'İstanbul', 'plate_code' => '34'],
            ['name' => 'İzmir', 'plate_code' => '35'],
            ['name' => 'Kars', 'plate_code' => '36'],
            ['name' => 'Kastamonu', 'plate_code' => '37'],
            ['name' => 'Kayseri', 'plate_code' => '38'],
            ['name' => 'Kırklareli', 'plate_code' => '39'],
            ['name' => 'Kırşehir', 'plate_code' => '40'],
            ['name' => 'Kocaeli', 'plate_code' => '41'],
            ['name' => 'Konya', 'plate_code' => '42'],
            ['name' => 'Kütahya', 'plate_code' => '43'],
            ['name' => 'Malatya', 'plate_code' => '44'],
            ['name' => 'Manisa', 'plate_code' => '45'],
            ['name' => 'Kahramanmaraş', 'plate_code' => '46'],
            ['name' => 'Mardin', 'plate_code' => '47'],
            ['name' => 'Muğla', 'plate_code' => '48'],
            ['name' => 'Muş', 'plate_code' => '49'],
            ['name' => 'Nevşehir', 'plate_code' => '50'],
            ['name' => 'Niğde', 'plate_code' => '51'],
            ['name' => 'Ordu', 'plate_code' => '52'],
            ['name' => 'Rize', 'plate_code' => '53'],
            ['name' => 'Sakarya', 'plate_code' => '54'],
            ['name' => 'Samsun', 'plate_code' => '55'],
            ['name' => 'Siirt', 'plate_code' => '56'],
            ['name' => 'Sinop', 'plate_code' => '57'],
            ['name' => 'Sivas', 'plate_code' => '58'],
            ['name' => 'Tekirdağ', 'plate_code' => '59'],
            ['name' => 'Tokat', 'plate_code' => '60'],
            ['name' => 'Trabzon', 'plate_code' => '61'],
            ['name' => 'Tunceli', 'plate_code' => '62'],
            ['name' => 'Şanlıurfa', 'plate_code' => '63'],
            ['name' => 'Uşak', 'plate_code' => '64'],
            ['name' => 'Van', 'plate_code' => '65'],
            ['name' => 'Yozgat', 'plate_code' => '66'],
            ['name' => 'Zonguldak', 'plate_code' => '67'],
            ['name' => 'Aksaray', 'plate_code' => '68'],
            ['name' => 'Bayburt', 'plate_code' => '69'],
            ['name' => 'Karaman', 'plate_code' => '70'],
            ['name' => 'Kırıkkale', 'plate_code' => '71'],
            ['name' => 'Batman', 'plate_code' => '72'],
            ['name' => 'Şırnak', 'plate_code' => '73'],
            ['name' => 'Bartın', 'plate_code' => '74'],
            ['name' => 'Ardahan', 'plate_code' => '75'],
            ['name' => 'Iğdır', 'plate_code' => '76'],
            ['name' => 'Yalova', 'plate_code' => '77'],
            ['name' => 'Karabük', 'plate_code' => '78'],
            ['name' => 'Kilis', 'plate_code' => '79'],
            ['name' => 'Osmaniye', 'plate_code' => '80'],
            ['name' => 'Düzce', 'plate_code' => '81'],
        ];

        // İlleri ekle
        $cityModels = [];
        foreach ($cities as $index => $cityData) {
            $city = City::updateOrCreate(
                ['plate_code' => $cityData['plate_code']],
                [
                    'name' => $cityData['name'],
                    'order' => $index + 1,
                ]
            );
            $cityModels[$cityData['name']] = $city;
        }

        // Her il için ilçeleri ekle (önemli ilçeler)
        $states = [
            'Adana' => ['Seyhan', 'Yüreğir', 'Çukurova', 'Sarıçam', 'Karaisalı', 'Ceyhan', 'Kozan', 'İmamoğlu', 'Feke', 'Karaisalı', 'Pozantı', 'Tufanbeyli'],
            'Adıyaman' => ['Merkez', 'Besni', 'Çelikhan', 'Gerger', 'Gölbaşı', 'Kahta', 'Samsat', 'Sincik', 'Tut'],
            'Afyonkarahisar' => ['Merkez', 'Başmakçı', 'Bayat', 'Bolvadin', 'Çay', 'Çobanlar', 'Dazkırı', 'Dinar', 'Emirdağ', 'Evciler', 'Hocalar', 'İhsaniye', 'İscehisar', 'Kızılören', 'Sandıklı', 'Sinanpaşa', 'Sultandağı', 'Şuhut'],
            'Ankara' => ['Altındağ', 'Ayaş', 'Bala', 'Beypazarı', 'Çamlıdere', 'Çankaya', 'Çubuk', 'Elmadağ', 'Güdül', 'Haymana', 'Kalecik', 'Kızılcahamam', 'Nallıhan', 'Polatlı', 'Şereflikoçhisar', 'Yenimahalle', 'Gölbaşı', 'Keçiören', 'Mamak', 'Sincan', 'Kazan', 'Akyurt', 'Pursaklar', 'Etimesgut', 'Evren', 'Akyurt'],
            'Antalya' => ['Alanya', 'Elmalı', 'Finike', 'Gazipaşa', 'Gündoğmuş', 'İbradı', 'Kaş', 'Kemer', 'Korkuteli', 'Kumluca', 'Manavgat', 'Serik', 'Akseki', 'Aksu', 'Demre', 'Döşemealtı', 'Kepez', 'Konyaaltı', 'Muratpaşa'],
            'Bursa' => ['Gemlik', 'İnegöl', 'İznik', 'Karacabey', 'Keles', 'Mudanya', 'Mustafakemalpaşa', 'Orhaneli', 'Orhangazi', 'Yenişehir', 'Büyükorhan', 'Harmancık', 'Nilüfer', 'Osmangazi', 'Yıldırım', 'Gürsu', 'Kestel'],
            'İstanbul' => ['Adalar', 'Bakırköy', 'Beşiktaş', 'Beykoz', 'Beyoğlu', 'Çatalca', 'Eyüpsultan', 'Fatih', 'Gaziosmanpaşa', 'Kadıköy', 'Kartal', 'Sarıyer', 'Silivri', 'Şile', 'Şişli', 'Üsküdar', 'Zeytinburnu', 'Büyükçekmece', 'Kağıthane', 'Küçükçekmece', 'Pendik', 'Ümraniye', 'Bayrampaşa', 'Avcılar', 'Bağcılar', 'Bahçelievler', 'Güngören', 'Maltepe', 'Sultanbeyli', 'Tuzla', 'Esenler', 'Arnavutköy', 'Ataşehir', 'Başakşehir', 'Beylikdüzü', 'Çekmeköy', 'Esenyurt', 'Sancaktepe', 'Sultangazi'],
            'İzmir' => ['Aliağa', 'Bayındır', 'Bergama', 'Bornova', 'Çeşme', 'Dikili', 'Foça', 'Karaburun', 'Karşıyaka', 'Kemalpaşa', 'Kınık', 'Kiraz', 'Menemen', 'Ödemiş', 'Seferihisar', 'Selçuk', 'Tire', 'Torbalı', 'Urla', 'Beydağ', 'Buca', 'Konak', 'Menderes', 'Balçova', 'Çiğli', 'Gaziemir', 'Narlıdere', 'Güzelbahçe', 'Bayraklı', 'Karabağlar'],
            'Konya' => ['Akşehir', 'Beyşehir', 'Bozkır', 'Cihanbeyli', 'Çumra', 'Doğanhisar', 'Ereğli', 'Güneysinir', 'Hadim', 'Halkapınar', 'Hüyük', 'Ilgın', 'Kadınhanı', 'Karapınar', 'Kulu', 'Sarayönü', 'Seydişehir', 'Taşkent', 'Tuzlukçu', 'Yalıhüyük', 'Yunak', 'Meram', 'Selçuklu', 'Karatay', 'Akören', 'Altınekin', 'Derebucak', 'Emirgazi', 'Karaman'],
            'Gaziantep' => ['Araban', 'İslahiye', 'Karkamış', 'Nizip', 'Nurdağı', 'Oğuzeli', 'Şahinbey', 'Şehitkamil', 'Yavuzeli'],
            'Kocaeli' => ['Gebze', 'Gölcük', 'Kandıra', 'Karamürsel', 'Körfez', 'Derince', 'Başiskele', 'Çayırova', 'Darıca', 'Dilovası', 'İzmit', 'Kartepe', 'Karamürsel'],
            'Sakarya' => ['Adapazarı', 'Akyazı', 'Arifiye', 'Erenler', 'Ferizli', 'Geyve', 'Hendek', 'Karapürçek', 'Karasu', 'Kaynarca', 'Kocaali', 'Pamukova', 'Sapanca', 'Serdivan', 'Söğütlü', 'Taraklı'],
            'Tekirdağ' => ['Çerkezköy', 'Çorlu', 'Ergene', 'Hayrabolu', 'Kapaklı', 'Malkara', 'Marmaraereğlisi', 'Muratlı', 'Saray', 'Süleymanpaşa', 'Şarköy'],
            'Balıkesir' => ['Ayvalık', 'Balya', 'Bandırma', 'Bigadiç', 'Burhaniye', 'Dursunbey', 'Edremit', 'Erdek', 'Gönen', 'Havran', 'İvrindi', 'Kepsut', 'Manyas', 'Marmara', 'Savaştepe', 'Sındırgı', 'Susurluk', 'Altıeylül', 'Karesi'],
            'Manisa' => ['Ahmetli', 'Akhisar', 'Alaşehir', 'Demirci', 'Gölmarmara', 'Gördes', 'Kırkağaç', 'Köprübaşı', 'Kula', 'Salihli', 'Sarıgöl', 'Saruhanlı', 'Selendi', 'Soma', 'Turgutlu', 'Yunusemre', 'Şehzadeler'],
            'Denizli' => ['Acıpayam', 'Babadağ', 'Baklan', 'Bekilli', 'Beyağaç', 'Bozkurt', 'Buldan', 'Çal', 'Çameli', 'Çardak', 'Çivril', 'Güney', 'Honaz', 'Kale', 'Merkezefendi', 'Pamukkale', 'Sarayköy', 'Serinhisar', 'Tavas'],
            'Hatay' => ['Altınözü', 'Antakya', 'Arsuz', 'Belen', 'Defne', 'Dörtyol', 'Erzin', 'Hassa', 'İskenderun', 'Kırıkhan', 'Kumlu', 'Payas', 'Reyhanlı', 'Samandağ', 'Yayladağı'],
            'Trabzon' => ['Akçaabat', 'Araklı', 'Arsin', 'Beşikdüzü', 'Çarşıbaşı', 'Çaykara', 'Dernekpazarı', 'Düzköy', 'Hayrat', 'Köprübaşı', 'Maçka', 'Of', 'Şalpazarı', 'Sürmene', 'Tonya', 'Vakfıkebir', 'Yomra', 'Ortahisar'],
            'Samsun' => ['Alaçam', 'Asarcık', 'Atakum', 'Ayvacık', 'Bafra', 'Canik', 'Çarşamba', 'Havza', 'İlkadım', 'Kavak', 'Ladik', 'Ondokuzmayıs', 'Salıpazarı', 'Tekkeköy', 'Terme', 'Vezirköprü', 'Yakakent'],
            'Mersin' => ['Anamur', 'Aydıncık', 'Bozyazı', 'Çamlıyayla', 'Erdemli', 'Gülnar', 'Mut', 'Silifke', 'Tarsus', 'Akdeniz', 'Mezitli', 'Toroslar', 'Yenişehir'],
            'Kayseri' => ['Akkışla', 'Bünyan', 'Develi', 'Felahiye', 'Hacılar', 'İncesu', 'Kocasinan', 'Melikgazi', 'Özvatan', 'Pınarbaşı', 'Sarıoğlan', 'Sarız', 'Talas', 'Tomarza', 'Yahyalı', 'Yeşilhisar'],
            'Eskişehir' => ['Alpu', 'Beylikova', 'Çifteler', 'Günyüzü', 'Han', 'İnönü', 'Mahmudiye', 'Mihalgazi', 'Mihalıççık', 'Odunpazarı', 'Sarıcakaya', 'Seyitgazi', 'Sivrihisar', 'Tepebaşı'],
            'Muğla' => ['Bodrum', 'Dalaman', 'Datça', 'Fethiye', 'Kavaklıdere', 'Köyceğiz', 'Marmaris', 'Milas', 'Ortaca', 'Ula', 'Yatağan', 'Menteşe', 'Seydikemer'],
            'Aydın' => ['Bozdoğan', 'Buharkent', 'Çine', 'Didim', 'Efeler', 'Germencik', 'İncirliova', 'Karacasu', 'Karpuzlu', 'Koçarlı', 'Köşk', 'Kuşadası', 'Kuyucak', 'Nazilli', 'Söke', 'Sultanhisar', 'Yenipazar'],
            'Malatya' => ['Akçadağ', 'Arapgir', 'Arguvan', 'Battalgazi', 'Darende', 'Doğanşehir', 'Doğanyol', 'Hekimhan', 'Kale', 'Kuluncak', 'Pütürge', 'Yazıhan', 'Yeşilyurt'],
            'Van' => ['Bahçesaray', 'Başkale', 'Çaldıran', 'Çatak', 'Edremit', 'Erciş', 'Gevaş', 'Gürpınar', 'İpekyolu', 'Muradiye', 'Özalp', 'Saray', 'Tuşba'],
            'Elazığ' => ['Ağın', 'Alacakaya', 'Arıcak', 'Baskil', 'Karakoçan', 'Keban', 'Kovancılar', 'Maden', 'Palu', 'Sivrice'],
            'Erzurum' => ['Aşkale', 'Aziziye', 'Çat', 'Hınıs', 'Horasan', 'İspir', 'Karaçoban', 'Karayazı', 'Köprüköy', 'Narman', 'Oltu', 'Olur', 'Palandöken', 'Pasinler', 'Şenkaya', 'Tekman', 'Tortum', 'Uzundere', 'Yakutiye'],
            'Diyarbakır' => ['Bağlar', 'Bismil', 'Çermik', 'Çınar', 'Çüngüş', 'Dicle', 'Eğil', 'Ergani', 'Hani', 'Hazro', 'Kayapınar', 'Kocaköy', 'Kulp', 'Lice', 'Silvan', 'Sur', 'Yenişehir'],
            'Şanlıurfa' => ['Akçakale', 'Birecik', 'Bozova', 'Ceylanpınar', 'Eyyübiye', 'Halfeti', 'Haliliye', 'Harran', 'Hilvan', 'Karaköprü', 'Siverek', 'Suruç', 'Viranşehir'],
            'Sakarya' => ['Adapazarı', 'Akyazı', 'Arifiye', 'Erenler', 'Ferizli', 'Geyve', 'Hendek', 'Karapürçek', 'Karasu', 'Kaynarca', 'Kocaali', 'Pamukova', 'Sapanca', 'Serdivan', 'Söğütlü', 'Taraklı'],
        ];

        // İlçeleri ekle
        foreach ($states as $cityName => $stateNames) {
            if (!isset($cityModels[$cityName])) {
                continue;
            }

            $city = $cityModels[$cityName];
            foreach ($stateNames as $index => $stateName) {
                State::updateOrCreate(
                    [
                        'city_id' => $city->id,
                        'name' => $stateName,
                    ],
                    [
                        'order' => $index + 1,
                    ]
                );
            }
        }

        $this->command->info('İller ve ilçeler başarıyla eklendi.');
    }
}
