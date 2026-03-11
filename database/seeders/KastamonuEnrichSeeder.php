<?php

namespace Database\Seeders;

use App\Models\Accessory;
use App\Models\Asset;
use App\Models\Category;
use App\Models\Consumable;
use App\Models\Department;
use App\Models\License;
use App\Models\LicenseSeat;
use App\Models\Location;
use App\Models\Manufacturer;
use App\Models\User;
use Illuminate\Database\Seeder;

class KastamonuEnrichSeeder extends Seeder
{
    public function run(): void
    {
        // ══════════════════════════════════════════
        //  1. Users
        // ══════════════════════════════════════════
        $this->command->info('=== [1/5] Kullanıcılar ===');

        $departments = Department::pluck('id', 'name');
        $locations = Location::pluck('id', 'name');

        $userDefs = [
            // [first, last, jobtitle, department, location]
            ['Ahmet',   'Yılmaz',    'Vardiya Şefi',            'Üretim',         'Hat 1 - Pres Bölümü'],
            ['Mehmet',  'Kaya',      'Operatör',                'Üretim',         'Hat 2 - Kaplama Bölümü'],
            ['Hasan',   'Demir',     'Operatör',                'Üretim',         'Hat 3 - Kesim Bölümü'],
            ['Ali',     'Çelik',     'Bakım Mühendisi',         'Bakım & Onarım', 'Bakım Atölyesi'],
            ['Fatih',   'Öztürk',    'Teknisyen',               'Bakım & Onarım', 'Bakım Atölyesi'],
            ['Emre',    'Aydın',     'Teknisyen',               'Bakım & Onarım', 'MDF Üretim Hattı'],
            ['Burak',   'Şahin',     'Kalite Kontrol Uzmanı',   'Kalite Kontrol', 'Kalite Kontrol Laboratuvarı'],
            ['Mustafa', 'Arslan',    'Kalite Kontrol Uzmanı',   'Kalite Kontrol', 'Kalite Kontrol Laboratuvarı'],
            ['Serkan',  'Koç',       'Forklift Operatörü',      'Lojistik',       'Depo ve Lojistik'],
            ['Oğuz',    'Yıldız',    'Depo Sorumlusu',          'Lojistik',       'Depo ve Lojistik'],
            ['Tolga',   'Erdoğan',   'IT Uzmanı',               'IT',             'Yönetim Ofisleri'],
            ['Deniz',   'Aktaş',     'Otomasyon Mühendisi',     'IT',             'Yönetim Ofisleri'],
            ['Ayşe',    'Korkmaz',   'Vardiya Şefi',            'Üretim',         'Hat 1 - Pres Bölümü'],
            ['Elif',    'Özkan',     'Operatör',                'Üretim',         'Hat 2 - Kaplama Bölümü'],
            ['Zeynep',  'Polat',     'Bakım Mühendisi',         'Bakım & Onarım', 'Bakım Atölyesi'],
        ];

        $users = [];
        foreach ($userDefs as $def) {
            [$first, $last, $job, $deptName, $locName] = $def;
            $username = $this->turkishSlug(strtolower($first)) . '.' . $this->turkishSlug(strtolower($last));

            $user = User::firstOrCreate(
                ['username' => $username],
                [
                    'first_name'    => $first,
                    'last_name'     => $last,
                    'email'         => $username . '@kastamonu.com.tr',
                    'password'      => bcrypt('password'),
                    'activated'     => 1,
                    'jobtitle'      => $job,
                    'department_id' => $departments[$deptName] ?? null,
                    'location_id'   => $locations[$locName] ?? null,
                    'locale'        => 'tr-TR',
                    'employee_num'  => 'KE-' . str_pad(rand(1000, 9999), 4, '0'),
                ]
            );
            $users[] = $user;
            $this->command->info("  {$user->first_name} {$user->last_name} - {$job} ({$username})");
        }

        // ══════════════════════════════════════════
        //  2. Asset Checkouts
        // ══════════════════════════════════════════
        $this->command->info('');
        $this->command->info('=== [2/5] Varlık Atamaları ===');

        $availableAssets = Asset::whereNull('assigned_to')
            ->where('asset_tag', 'like', 'KE-%')
            ->inRandomOrder()
            ->limit(50)
            ->get();

        $checkoutCount = 0;
        foreach ($availableAssets as $asset) {
            $user = $users[array_rand($users)];

            $checkoutDate = now()->subDays(rand(1, 180));
            $expectedCheckin = now()->addDays(rand(30, 365));

            $asset->assigned_to = $user->id;
            $asset->assigned_type = User::class;
            $asset->last_checkout = $checkoutDate->format('Y-m-d H:i:s');
            $asset->expected_checkin = $expectedCheckin->format('Y-m-d');
            $asset->save();

            $checkoutCount++;
        }

        $this->command->info("  {$checkoutCount} varlık {$checkoutCount} kullanıcıya atandı.");

        // ══════════════════════════════════════════
        //  3. Licenses
        // ══════════════════════════════════════════
        $this->command->info('');
        $this->command->info('=== [3/5] Yazılım Lisansları ===');

        // Ensure license category exists
        $licenseCat = Category::firstOrCreate(
            ['name' => 'Yazılım Lisansları'],
            ['category_type' => 'license']
        );

        // Ensure manufacturers
        $mfrIds = [];
        foreach (['Siemens', 'SAP', 'Autodesk', 'Microsoft'] as $m) {
            $mfrIds[$m] = Manufacturer::firstOrCreate(['name' => $m])->id;
        }

        $licenseDefs = [
            ['SCADA Sistemi Lisansı',           5,  'Siemens',   'SCADA-WCC-OA-5',        85000,  '2023-03-15', '2026-03-15'],
            ['SAP ERP Lisansı',                 10, 'SAP',       'SAP-ERP-S4H-ENT',       250000, '2022-01-01', '2027-01-01'],
            ['AutoCAD 2024',                    3,  'Autodesk',  'ACD-2024-ENT',           45000,  '2024-01-10', '2025-01-10'],
            ['Microsoft Office 365 E3',         15, 'Microsoft', 'O365-E3-ENT',            12000,  '2024-06-01', '2025-06-01'],
            ['Antivirus Kurumsal Lisansı',      20, 'Microsoft', 'DEF-ENT-P2',             8500,   '2024-04-01', '2025-04-01'],
        ];

        foreach ($licenseDefs as $def) {
            [$name, $seats, $mfr, $serial, $cost, $purchaseDate, $expDate] = $def;

            $license = License::firstOrCreate(
                ['name' => $name],
                [
                    'seats'           => $seats,
                    'serial'          => $serial,
                    'manufacturer_id' => $mfrIds[$mfr],
                    'category_id'     => $licenseCat->id,
                    'purchase_cost'   => $cost,
                    'purchase_date'   => $purchaseDate,
                    'expiration_date' => $expDate,
                    'notes'           => "{$seats} kullanıcılık lisans",
                ]
            );

            // Assign some seats to users
            $seatsToAssign = min(intval($seats * 0.6), count($users));
            $assignedUsers = collect($users)->random($seatsToAssign);
            $seatIndex = 0;

            foreach ($license->licenseSeats as $seat) {
                if ($seatIndex >= $seatsToAssign) {
                    break;
                }
                if (!$seat->assigned_to) {
                    $seat->assigned_to = $assignedUsers[$seatIndex]->id;
                    $seat->save();
                    $seatIndex++;
                }
            }

            $this->command->info("  {$name}: {$seats} koltuk ({$seatIndex} atandı) - {$serial}");
        }

        // ══════════════════════════════════════════
        //  4. Consumables
        // ══════════════════════════════════════════
        $this->command->info('');
        $this->command->info('=== [4/5] Sarf Malzemeleri ===');

        $consumableCat = Category::firstOrCreate(
            ['name' => 'Sarf Malzemeleri'],
            ['category_type' => 'consumable']
        );

        $depoId = $locations['Depo ve Lojistik'] ?? null;
        $bakimId = $locations['Bakım Atölyesi'] ?? null;

        $consumableDefs = [
            ['Endüstriyel Yağ 5L',          50,  'Bosch',  350,   $bakimId,  'Makine yağlama ve bakım için'],
            ['HEPA Filtre',                  30,  'Bosch',  1200,  $bakimId,  'Toz toplama sistemleri için'],
            ['Zımpara Diski 120 Kum',       200, 'Bosch',  45,    $depoId,   'Bant zımpara makineleri için'],
            ['Koruyucu Eldiven (kutu)',      100, 'Bosch',  280,   $depoId,   'Nitril eldiven, 100lü kutu'],
        ];

        $boschId = Manufacturer::where('name', 'Bosch')->first()?->id;

        foreach ($consumableDefs as $def) {
            [$name, $qty, $mfr, $cost, $locId, $notes] = $def;

            $consumable = Consumable::firstOrCreate(
                ['name' => $name],
                [
                    'qty'             => $qty,
                    'category_id'     => $consumableCat->id,
                    'manufacturer_id' => $boschId,
                    'location_id'     => $locId,
                    'purchase_cost'   => $cost,
                    'purchase_date'   => $this->randomDate('2024-01-01', '2025-06-01'),
                    'min_amt'         => intval($qty * 0.2),
                    'notes'           => $notes,
                ]
            );

            $this->command->info("  {$name}: {$qty} adet (min: {$consumable->min_amt})");
        }

        // ══════════════════════════════════════════
        //  5. Accessories
        // ══════════════════════════════════════════
        $this->command->info('');
        $this->command->info('=== [5/5] Aksesuarlar ===');

        $accessoryCat = Category::firstOrCreate(
            ['name' => 'Fabrika Aksesuarları'],
            ['category_type' => 'accessory']
        );

        $siemensId = Manufacturer::where('name', 'Siemens')->first()?->id;
        $hpId = Manufacturer::where('name', 'HP')->first()?->id;

        $accessoryDefs = [
            ['USB Barkod Okuyucu',                10, $hpId,      2500,  $depoId,  'El tipi USB barkod tarayıcı'],
            ['Endüstriyel Ethernet Kablosu 5m',   25, $siemensId, 450,   $bakimId, 'Cat6 endüstriyel ethernet, IP67'],
            ['PLC Yedek Modül',                   5,  $siemensId, 8500,  $bakimId, 'S7-1500 yedek I/O modülü'],
        ];

        foreach ($accessoryDefs as $def) {
            [$name, $qty, $mfrId, $cost, $locId, $notes] = $def;

            $accessory = Accessory::firstOrCreate(
                ['name' => $name],
                [
                    'qty'             => $qty,
                    'category_id'     => $accessoryCat->id,
                    'manufacturer_id' => $mfrId,
                    'location_id'     => $locId,
                    'purchase_cost'   => $cost,
                    'purchase_date'   => $this->randomDate('2024-01-01', '2025-06-01'),
                    'min_amt'         => max(1, intval($qty * 0.2)),
                    'notes'           => $notes,
                ]
            );

            $this->command->info("  {$name}: {$qty} adet");
        }

        // ── Summary ──
        $this->command->info('');
        $this->command->info('=== Zenginleştirme tamamlandı ===');
        $this->command->info("  Kullanıcılar:      " . count($users));
        $this->command->info("  Atanan varlıklar:  {$checkoutCount}");
        $this->command->info("  Lisanslar:         " . count($licenseDefs));
        $this->command->info("  Sarf malzemeleri:  " . count($consumableDefs));
        $this->command->info("  Aksesuarlar:       " . count($accessoryDefs));
    }

    private function randomDate(string $start, string $end): string
    {
        return date('Y-m-d', rand(strtotime($start), strtotime($end)));
    }

    private function turkishSlug(string $str): string
    {
        $map = [
            'ç' => 'c', 'ğ' => 'g', 'ı' => 'i', 'ö' => 'o', 'ş' => 's', 'ü' => 'u',
            'Ç' => 'C', 'Ğ' => 'G', 'İ' => 'I', 'Ö' => 'O', 'Ş' => 'S', 'Ü' => 'U',
        ];
        return strtr($str, $map);
    }
}
