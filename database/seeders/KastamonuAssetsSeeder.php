<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Category;
use App\Models\CustomField;
use App\Models\CustomFieldset;
use App\Models\Location;
use App\Models\Manufacturer;
use App\Models\Statuslabel;
use Illuminate\Database\Seeder;

class KastamonuAssetsSeeder extends Seeder
{
    public function run(): void
    {
        // ── Resolve IDs dynamically ──
        $fieldset = CustomFieldset::where('name', 'Fabrika Ekipman Bilgileri')->first();
        $fieldsetId = $fieldset ? $fieldset->id : null;

        $statusReady = Statuslabel::where('name', 'Ready to Deploy')->first()?->id ?? 1;
        $statusRepair = Statuslabel::where('name', 'Out for Repair')->first()?->id ?? 5;

        // Locations by name
        $locationIds = [
            'fabrika' => Location::where('name', 'Kastamonu Entegre Fabrika')->first()?->id,
            'mdf'     => Location::where('name', 'MDF Üretim Hattı')->first()?->id,
            'depo'    => Location::where('name', 'Depo ve Lojistik')->first()?->id,
            'bakim'   => Location::where('name', 'Bakım Atölyesi')->first()?->id,
            'kalite'  => Location::where('name', 'Kalite Kontrol Laboratuvarı')->first()?->id,
            'ofis'    => Location::where('name', 'Yönetim Ofisleri')->first()?->id,
            'hat1'    => Location::where('name', 'Hat 1 - Pres Bölümü')->first()?->id,
            'hat2'    => Location::where('name', 'Hat 2 - Kaplama Bölümü')->first()?->id,
            'hat3'    => Location::where('name', 'Hat 3 - Kesim Bölümü')->first()?->id,
        ];

        // Custom field db_column names by field name
        $cf = [
            'ekipman_durumu'        => CustomField::where('name', 'Ekipman Durumu')->first()?->db_column,
            'saha_lokasyonu'        => CustomField::where('name', 'Saha Lokasyonu')->first()?->db_column,
            'kalibrasyon_tarihi'    => CustomField::where('name', 'Kalibrasyon Tarihi')->first()?->db_column,
            'bakim_periyodu'        => CustomField::where('name', 'Bakım Periyodu (Gün)')->first()?->db_column,
            'sonraki_bakim'         => CustomField::where('name', 'Sonraki Bakım Tarihi')->first()?->db_column,
            'kalibrasyon_sorumlusu' => CustomField::where('name', 'Kalibrasyon Sorumlusu')->first()?->db_column,
        ];

        // ── Manufacturers ──
        $manufacturers = [];
        foreach (['Siemens', 'ABB', 'Bosch', 'Mitsubishi', 'Toyota', 'Fluke', 'Dell', 'HP'] as $name) {
            $manufacturers[$name] = Manufacturer::firstOrCreate(['name' => $name])->id;
            $this->command->info("  Manufacturer: {$name}");
        }

        // ── Categories ──
        $categories = [];
        foreach ([
            'URT' => 'Üretim Makineleri',
            'FRK' => 'Forklift & Taşıma Ekipmanları',
            'OLC' => 'Ölçüm & Kalibrasyon Cihazları',
            'ITO' => 'IT & Otomasyon Ekipmanları',
            'GVN' => 'Güvenlik & KKD',
        ] as $code => $name) {
            $categories[$code] = Category::firstOrCreate(
                ['name' => $name],
                ['category_type' => 'asset']
            )->id;
            $this->command->info("  Category: {$name} [{$code}]");
        }

        // ── Asset Models ──
        $modelDefs = [
            ['CNC Tezgah',         'URT', 'Siemens',    'SINUMERIK 828D'],
            ['Pres Makinesi',      'URT', 'Siemens',    'SIMOTION D445'],
            ['Bant Zımpara',       'URT', 'Bosch',      'BSM-3000'],
            ['Panel Kesme',        'URT', 'Mitsubishi',  'MC-4500'],
            ['Toyota Forklift',    'FRK', 'Toyota',     '8FBE18T'],
            ['Transpalet',         'FRK', 'Toyota',     'BT-LPE200'],
            ['Konveyör',           'FRK', 'Siemens',    'SIMATIC-CV200'],
            ['Dijital Kumpas',     'OLC', 'Mitsubishi',  'CD-15CPX'],
            ['Termal Kamera',      'OLC', 'Fluke',      'Ti480 PRO'],
            ['Multimetre',         'OLC', 'Fluke',      'Fluke 87V'],
            ['Kaliper',            'OLC', 'Mitsubishi',  'IP67-150mm'],
            ['Endüstriyel PC',     'ITO', 'Siemens',    'SIMATIC IPC477E'],
            ['PLC',                'ITO', 'Siemens',    'S7-1500'],
            ['SCADA Terminali',    'ITO', 'ABB',        'Panel 800'],
            ['Barkod Okuyucu',     'ITO', 'HP',         'HP-RP9-G1'],
            ['Baret',              'GVN', 'Bosch',      'UVEX-P2000'],
            ['Güvenlik Gözlüğü',  'GVN', 'Bosch',      'UVEX-I3'],
            ['İş Eldiveni',        'GVN', 'Bosch',      'UVEX-C500'],
        ];

        $models = [];
        foreach ($modelDefs as $def) {
            [$name, $catCode, $mfr, $modelNum] = $def;
            $model = AssetModel::firstOrCreate(
                ['name' => $name, 'model_number' => $modelNum],
                [
                    'category_id'     => $categories[$catCode],
                    'manufacturer_id' => $manufacturers[$mfr],
                    'fieldset_id'     => $fieldsetId,
                ]
            );
            $models[$name] = ['id' => $model->id, 'code' => $catCode];
        }
        $this->command->info("  " . count($models) . " Asset Models created.");

        // ── Assets ──
        $assetDefs = [
            ['CNC Tezgah',    5, 850000,  1500000, 24, ['hat1', 'hat2', 'hat3'],
                ['Yıllık bakım sözleşmesi mevcut', '3 vardiya çalışıyor', 'Yedek parça stokta', 'Son bakım sorunsuz tamamlandı', 'Yeni bıçak takımı takıldı']],
            ['Pres Makinesi',  5, 1200000, 2500000, 24, ['hat1', 'hat2'],
                ['Hidrolik sistem yenilenmiş', 'Basınç sensörü kalibrasyonu yapıldı', 'Yıllık revizyon planı dahilinde', 'Kalıp seti değiştirildi', 'Kapasite artırımı yapıldı']],
            ['Bant Zımpara',   5, 320000,  650000,  12, ['hat2', 'hat3'],
                ['Zımpara bandı yeni değişti', 'Toz toplama sistemi aktif', 'Aspirasyon bağlantısı kontrol edildi', 'Motor rulmanları değiştirildi', 'Yüzey kalitesi testleri uygun']],
            ['Panel Kesme',    5, 750000,  1800000, 24, ['hat3', 'hat1'],
                ['Testere bıçağı keskinleştirildi', 'Lazer hizalama kalibre edildi', 'Otomatik besleme ünitesi çalışıyor', 'Kesim toleransı ±0.1mm', 'Toz emme sistemi bakımı yapıldı']],
            ['Toyota Forklift', 5, 450000,  800000,  24, ['depo', 'mdf'],
                ['LPG yakıtlı, tank yeni dolduruldu', 'Lastikler %80 ömürlü', 'Çatal yüksekliği 4.5m', 'Yıllık periyodik muayene geçerli', 'Fren sistemi kontrol edildi']],
            ['Transpalet',      5, 25000,   85000,   12, ['depo', 'hat1', 'hat2'],
                ['Elektrikli, şarj durumu iyi', 'Tekerlekler kontrol edildi', 'Kaldırma kapasitesi 2 ton', 'Hidrolik yağı değiştirildi', 'Palet sensörü kalibre edildi']],
            ['Konveyör',        5, 180000,  400000,  24, ['hat1', 'hat2', 'hat3'],
                ['Bant gerginliği ayarlandı', 'Rulolar yağlanmış', 'Hız kontrol ünitesi aktif', 'Acil stop butonları test edildi', 'Motor sürücü kartı yenilendi']],
            ['Dijital Kumpas',  4, 3500,    12000,   12, ['kalite', 'hat1', 'hat2'],
                ['Son kalibrasyon: uygun', 'Pil seviyesi yeterli', 'Hassasiyet: 0.01mm', 'Kalibrasyon sertifikası mevcut']],
            ['Termal Kamera',   4, 85000,   180000,  24, ['bakim', 'kalite'],
                ['Lens temizliği yapıldı', 'Firmware güncellemesi uygulandı', 'Batarya ömrü 4 saat', 'Kalibrasyon tarihi geçerli']],
            ['Multimetre',      4, 8000,    25000,   12, ['bakim', 'kalite', 'ofis'],
                ['Prob kabloları yeni', 'Ölçüm doğruluğu kontrol edildi', 'Batarya yeni değiştirildi', 'Koruyucu kılıf mevcut']],
            ['Kaliper',         3, 5000,    15000,   12, ['kalite'],
                ['Mekanik, yağlama yapıldı', 'Kalibrasyon sertifikası güncel', 'Hassasiyet: 0.02mm']],
            ['Endüstriyel PC',  5, 45000,   120000,  36, ['hat1', 'hat2', 'hat3', 'ofis'],
                ['Windows 10 IoT Enterprise', 'IP65 koruma sınıfı', 'SSD disk yükseltildi', 'RAM 16GB', 'SCADA yazılımı yüklü']],
            ['PLC',             5, 35000,   95000,   36, ['hat1', 'hat2', 'hat3'],
                ['Program yedeklemesi alındı', 'I/O modülleri kontrol edildi', 'Firmware v4.5', 'Haberleşme modülü PROFINET', 'CPU yük oranı normal']],
            ['SCADA Terminali', 5, 60000,   150000,  24, ['hat1', 'hat2', 'ofis'],
                ['Dokunmatik ekran kalibre edildi', 'Alarm geçmişi temizlendi', 'Ağ bağlantısı stabil', 'Yetkilendirme seviyeleri ayarlandı', 'Ekran koruyucu takıldı']],
            ['Barkod Okuyucu',  5, 8000,    25000,   12, ['depo', 'hat1', 'hat2', 'hat3'],
                ['Lazer okuyucu, hızlı tarama', 'Bluetooth bağlantısı aktif', 'Şarj ünitesi mevcut', 'Firmware güncellendi', 'Koruyucu kılıf takılı']],
            ['Baret',              4, 150,   450,   0, ['hat1', 'hat2', 'hat3', 'depo'],
                ['CE sertifikalı', 'Ter bandı değiştirildi', 'UV dayanımlı', 'Kişiye özel ayarlanmış']],
            ['Güvenlik Gözlüğü',  3, 80,    350,   0, ['hat1', 'hat2', 'kalite'],
                ['Anti-fog özellikli', 'Çizilmeye dayanıklı lens', 'Optik sınıf 1']],
            ['İş Eldiveni',        3, 50,    200,   0, ['hat1', 'hat2', 'bakim'],
                ['Kesilmeye dayanıklı', 'Nitril kaplama', 'EN388 sertifikalı']],
        ];

        $ekipmanDurumlari = ['Çalışıyor', 'Çalışıyor', 'Çalışıyor', 'Bakımda', 'Çalışıyor'];
        $sahaMap = [
            'hat1' => 'Hat 1', 'hat2' => 'Hat 2', 'hat3' => 'Hat 3',
            'depo' => 'Depo', 'ofis' => 'Ofis', 'bakim' => 'Bakım Atölyesi',
            'kalite' => 'Bakım Atölyesi', 'mdf' => 'Hat 1', 'fabrika' => 'Hat 1',
        ];
        $sorumlular = ['Ahmet Yılmaz', 'Mehmet Kaya', 'Hasan Demir', 'Ali Çelik', 'Fatih Öztürk'];
        $bakimPeriyotlar = [30, 60, 90, 180, 365];

        $totalAssets = 0;

        foreach ($assetDefs as $def) {
            [$modelName, $count, $costMin, $costMax, $warranty, $locs, $notesPool] = $def;
            $modelInfo = $models[$modelName];

            for ($i = 0; $i < $count; $i++) {
                $tag = sprintf('KE-%s-%03d', $modelInfo['code'], $totalAssets + 1);

                if (Asset::where('asset_tag', $tag)->exists()) {
                    $totalAssets++;
                    continue;
                }

                $locKey = $locs[array_rand($locs)];
                $purchaseDate = $this->randomDate('2018-01-01', '2024-12-31');
                $isRepair = (rand(1, 100) <= 8);

                $asset = new Asset();
                $asset->asset_tag = $tag;
                $asset->name = $modelName . ' ' . sprintf('%02d', $i + 1);
                $asset->model_id = $modelInfo['id'];
                $asset->status_id = $isRepair ? $statusRepair : $statusReady;
                $asset->rtd_location_id = $locationIds[$locKey];
                $asset->purchase_date = $purchaseDate;
                $asset->purchase_cost = round(rand($costMin * 100, $costMax * 100) / 100, 2);
                $asset->warranty_months = $warranty;
                $asset->serial = strtoupper(substr(md5($tag . $modelName . $i), 0, 12));
                $asset->notes = $notesPool[array_rand($notesPool)];

                // Custom field values (dynamic column names)
                if ($cf['ekipman_durumu']) {
                    $asset->{$cf['ekipman_durumu']} = $isRepair ? 'Bakımda' : $ekipmanDurumlari[array_rand($ekipmanDurumlari)];
                }
                if ($cf['saha_lokasyonu']) {
                    $asset->{$cf['saha_lokasyonu']} = $sahaMap[$locKey] ?? 'Hat 1';
                }

                if ($warranty > 0) {
                    $kalibDate = $this->randomDate('2024-01-01', '2025-12-31');
                    if ($cf['kalibrasyon_tarihi']) {
                        $asset->{$cf['kalibrasyon_tarihi']} = $kalibDate;
                    }
                    $bakimPeriyod = $bakimPeriyotlar[array_rand($bakimPeriyotlar)];
                    if ($cf['bakim_periyodu']) {
                        $asset->{$cf['bakim_periyodu']} = $bakimPeriyod;
                    }
                    if ($cf['sonraki_bakim']) {
                        $asset->{$cf['sonraki_bakim']} = date('Y-m-d', strtotime($kalibDate . " +{$bakimPeriyod} days"));
                    }
                    if ($cf['kalibrasyon_sorumlusu']) {
                        $asset->{$cf['kalibrasyon_sorumlusu']} = $sorumlular[array_rand($sorumlular)];
                    }
                }

                $asset->save();
                $totalAssets++;
            }

            $this->command->info("  {$count}x {$modelName} [{$modelInfo['code']}]");
        }

        $this->command->info('');
        $this->command->info("Done. {$totalAssets} assets created.");
    }

    private function randomDate(string $start, string $end): string
    {
        return date('Y-m-d', rand(strtotime($start), strtotime($end)));
    }
}
