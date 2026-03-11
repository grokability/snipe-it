<?php

namespace Database\Seeders;

use App\Custom\Models\PurchaseRequest;
use App\Custom\Models\ShiftHandover;
use App\Custom\Models\ShiftHandoverItem;
use App\Custom\Models\SparePart;
use App\Custom\Models\SparePartMovement;
use App\Custom\Models\WorkOrder;
use App\Custom\Models\WorkOrderPart;
use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Location;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class FomDemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Creating FOM demo data...');

        // Ensure we have a supplier
        $supplier = Supplier::first();
        if (! $supplier) {
            $supplier = Supplier::create([
                'name' => 'Kastamonu Endüstriyel Tedarik',
                'address' => 'Organize Sanayi Bölgesi, Kastamonu',
                'phone' => '0366 212 00 00',
            ]);
        }

        $locations = Location::pluck('id')->toArray();
        $warehouseLocId = Location::where('name', 'like', '%Depo%')->value('id') ?? $locations[0];
        $workshopLocId = Location::where('name', 'like', '%Bakım%')->value('id') ?? $locations[0];
        $modelIds = AssetModel::whereIn('id', [1, 2, 3, 4, 7, 13])->pluck('id')->toArray();

        // ─── 1. Spare Parts ──────────────────────────────────────────
        $partsData = [
            ['name' => 'Pres Contası',          'part_number' => 'YP-CONT-001', 'min' => 5,  'qty' => 12, 'cost' => 850,   'lead' => 7,  'models' => [2]],
            ['name' => 'Hidrolik Pompa',         'part_number' => 'YP-HIDR-002', 'min' => 2,  'qty' => 3,  'cost' => 12500, 'lead' => 21, 'models' => [2, 4]],
            ['name' => 'Rulman 6205',            'part_number' => 'YP-RULM-003', 'min' => 10, 'qty' => 25, 'cost' => 520,   'lead' => 3,  'models' => [1, 2, 3]],
            ['name' => 'Filtre Elemanı',         'part_number' => 'YP-FILT-004', 'min' => 8,  'qty' => 3,  'cost' => 1200,  'lead' => 5,  'models' => [2, 7]],    // LOW
            ['name' => 'Kayış V-Belt A68',       'part_number' => 'YP-KAYS-005', 'min' => 4,  'qty' => 6,  'cost' => 680,   'lead' => 5,  'models' => [3]],
            ['name' => 'PLC Sigorta 10A',        'part_number' => 'YP-PLCS-006', 'min' => 6,  'qty' => 2,  'cost' => 950,   'lead' => 10, 'models' => [13]],       // LOW
            ['name' => 'Enkoder 1024 PPR',       'part_number' => 'YP-ENCD-007', 'min' => 3,  'qty' => 4,  'cost' => 3800,  'lead' => 14, 'models' => [1, 3]],
            ['name' => 'Redüktör Yağı 5L',      'part_number' => 'YP-RYAG-008', 'min' => 4,  'qty' => 8,  'cost' => 1450,  'lead' => 3,  'models' => [1, 2, 3, 4]],
            ['name' => 'Zincir 08B-1',           'part_number' => 'YP-ZINC-009', 'min' => 3,  'qty' => 5,  'cost' => 2200,  'lead' => 7,  'models' => [7]],
            ['name' => 'Hız Kontrol Kartı',      'part_number' => 'YP-HZKK-010', 'min' => 2,  'qty' => 1,  'cost' => 8500,  'lead' => 30, 'models' => [1, 4]],     // LOW (critical)
            ['name' => 'Termokupl Tip-K',        'part_number' => 'YP-TERM-011', 'min' => 5,  'qty' => 7,  'cost' => 750,   'lead' => 5,  'models' => [2]],
            ['name' => 'Solenoid Valf 24V',      'part_number' => 'YP-SOLV-012', 'min' => 3,  'qty' => 4,  'cost' => 2800,  'lead' => 10, 'models' => [2, 4]],
            ['name' => 'Kompresör Pistonu',      'part_number' => 'YP-KOMP-013', 'min' => 2,  'qty' => 2,  'cost' => 5600,  'lead' => 21, 'models' => [2]],
            ['name' => 'Motor Fırçası Seti',     'part_number' => 'YP-MOTF-014', 'min' => 6,  'qty' => 1,  'cost' => 1100,  'lead' => 7,  'models' => [1, 3]],     // LOW
            ['name' => 'Şalter 3P 63A',          'part_number' => 'YP-SALT-015', 'min' => 3,  'qty' => 5,  'cost' => 1850,  'lead' => 5,  'models' => [1, 2, 3, 4, 13]],
        ];

        $spareParts = [];
        foreach ($partsData as $pd) {
            $part = SparePart::create([
                'name'             => $pd['name'],
                'part_number'      => $pd['part_number'],
                'minimum_quantity' => $pd['min'],
                'quantity_on_hand' => $pd['qty'],
                'unit_cost'        => $pd['cost'],
                'lead_time_days'   => $pd['lead'],
                'location_id'      => $warehouseLocId,
                'supplier_id'      => $supplier->id,
            ]);

            // Attach compatible asset models
            $validModels = array_intersect($pd['models'], $modelIds);
            if (! empty($validModels)) {
                $syncData = [];
                foreach ($validModels as $mid) {
                    $syncData[$mid] = ['is_critical' => ($pd['qty'] < $pd['min']), 'recommended_qty' => $pd['min']];
                }
                $part->assetModels()->sync($syncData);
            }

            $spareParts[$pd['part_number']] = $part;
        }
        $this->command->info('  ✓ 15 spare parts created (4 low stock)');

        // ─── 2. Work Orders ──────────────────────────────────────────
        $assets = Asset::take(20)->get();
        $users = User::take(10)->get();
        $admin = User::where('username', 'admin')->first() ?? $users->first();
        $technicians = $users->filter(fn ($u) => $u->username !== 'admin')->values();

        $workOrders = [];
        $woData = [
            // 3x bekliyor
            ['asset_idx' => 0,  'priority' => 'kritik',  'status' => 'bekliyor',      'desc' => 'Pres makinesi ana silindir sızıntısı - acil müdahale gerekiyor, üretim durdu',            'reporter' => 0, 'days_ago' => 0],
            ['asset_idx' => 5,  'priority' => 'yuksek',  'status' => 'bekliyor',      'desc' => 'CNC tezgah X ekseni hata kodu E-4021 veriyor, kalibrasyon kaymış olabilir',               'reporter' => 1, 'days_ago' => 0],
            ['asset_idx' => 10, 'priority' => 'yuksek',  'status' => 'bekliyor',      'desc' => 'Bant zımpara titreşim sensörü alarm veriyor, rulman kontrolü gerekli',                     'reporter' => 2, 'days_ago' => 1],
            // 3x atandi
            ['asset_idx' => 1,  'priority' => 'yuksek',  'status' => 'atandi',        'desc' => 'Konveyör bant kayması, germe mekanizması ayarlanmalı',                                     'reporter' => 0, 'days_ago' => 2, 'tech' => 0],
            ['asset_idx' => 6,  'priority' => 'normal',  'status' => 'atandi',        'desc' => 'Pres makinesi periyodik bakım zamanı geldi (1000 saat)',                                    'reporter' => 3, 'days_ago' => 2, 'tech' => 1],
            ['asset_idx' => 11, 'priority' => 'yuksek',  'status' => 'atandi',        'desc' => 'Panel kesme ünitesi testere diski aşınmış, değişim gerekiyor',                              'reporter' => 1, 'days_ago' => 1, 'tech' => 2],
            // 2x devam_ediyor
            ['asset_idx' => 2,  'priority' => 'kritik',  'status' => 'devam_ediyor',  'desc' => 'Hidrolik sistem basınç düşüklüğü, pompa ve hortumlar kontrol ediliyor',                     'reporter' => 0, 'days_ago' => 3, 'tech' => 0],
            ['asset_idx' => 7,  'priority' => 'normal',  'status' => 'devam_ediyor',  'desc' => 'PLC haberleşme kartı arıza, yedek kart ile değişim yapılıyor',                              'reporter' => 2, 'days_ago' => 2, 'tech' => 3],
            // 2x tamamlandi
            ['asset_idx' => 3,  'priority' => 'yuksek',  'status' => 'tamamlandi',    'desc' => 'Soğutma sistemi fan motoru arızası',                                                        'reporter' => 1, 'days_ago' => 5, 'tech' => 1, 'resolution' => 'Fan motoru sökülüp yenisi takıldı. Rulmanlar değiştirildi, soğutma sıcaklığı normal seviyeye döndü.'],
            ['asset_idx' => 8,  'priority' => 'normal',  'status' => 'tamamlandi',    'desc' => 'Bant zımpara kayış değişimi ve germe ayarı',                                                'reporter' => 3, 'days_ago' => 4, 'tech' => 2, 'resolution' => 'V-kayış değiştirildi, germe rulmanı yağlandı. Test üretimi yapıldı, sonuç normal.'],
        ];

        foreach ($woData as $wd) {
            $asset = $assets[$wd['asset_idx']] ?? $assets->first();
            $reporter = $technicians[$wd['reporter']] ?? $admin;
            $createdAt = now()->subDays($wd['days_ago'])->subHours(rand(1, 8));

            $wo = new WorkOrder();
            $wo->work_order_number = null; // Let boot auto-generate
            $wo->asset_id = $asset->id;
            $wo->priority = $wd['priority'];
            $wo->status = $wd['status'];
            $wo->description = $wd['desc'];
            $wo->reported_by = $reporter->id;
            $wo->location_id = $asset->rtd_location_id ?? $locations[array_rand($locations)];
            $wo->created_at = $createdAt;
            $wo->updated_at = $createdAt;

            if (isset($wd['tech'])) {
                $wo->assigned_to = $technicians[$wd['tech']]->id ?? $admin->id;
            }

            if ($wd['status'] === 'devam_ediyor') {
                $wo->started_at = $createdAt->copy()->addHours(rand(1, 4));
            }

            if ($wd['status'] === 'tamamlandi') {
                $wo->started_at = $createdAt->copy()->addHours(rand(1, 4));
                $wo->completed_at = $createdAt->copy()->addDays(rand(1, 2))->addHours(rand(1, 6));
                $wo->resolution_notes = $wd['resolution'] ?? null;
            }

            $wo->save();
            $workOrders[] = $wo;
        }

        // Add parts used to completed work orders
        if (count($workOrders) >= 10) {
            // WO #9 (completed - fan motor): used Motor Fırçası + Rulman
            WorkOrderPart::create([
                'work_order_id' => $workOrders[8]->id,
                'spare_part_id' => $spareParts['YP-MOTF-014']->id,
                'quantity_used' => 1,
            ]);
            WorkOrderPart::create([
                'work_order_id' => $workOrders[8]->id,
                'spare_part_id' => $spareParts['YP-RULM-003']->id,
                'quantity_used' => 2,
            ]);

            // WO #10 (completed - kayış): used Kayış V-Belt
            WorkOrderPart::create([
                'work_order_id' => $workOrders[9]->id,
                'spare_part_id' => $spareParts['YP-KAYS-005']->id,
                'quantity_used' => 1,
            ]);
        }
        $this->command->info('  ✓ 10 work orders created (3 bekliyor, 3 atandı, 2 devam ediyor, 2 tamamlandı)');

        // ─── 3. Shift Handovers ──────────────────────────────────────
        $yesterday = Carbon::yesterday();
        $pressLocId = Location::where('name', 'like', '%Pres%')->value('id') ?? $locations[0];
        $cuttingLocId = Location::where('name', 'like', '%Kesim%')->value('id') ?? $locations[0];

        $handoverData = [
            [
                'location_id' => $pressLocId,
                'shift_date'  => $yesterday,
                'shift_type'  => '06-14',
                'outgoing'    => $technicians[0] ?? $admin,
                'incoming'    => $technicians[1] ?? $admin,
                'assets'      => Asset::where('rtd_location_id', $pressLocId)->take(6)->get(),
                'note'        => 'Pres 01 hidrolik sızıntı devam ediyor, bakım ekibine bilgi verildi.',
            ],
            [
                'location_id' => $cuttingLocId,
                'shift_date'  => $yesterday,
                'shift_type'  => '14-22',
                'outgoing'    => $technicians[2] ?? $admin,
                'incoming'    => $technicians[3] ?? $admin,
                'assets'      => Asset::where('rtd_location_id', $cuttingLocId)->take(8)->get(),
                'note'        => 'Kesim hattı normal çalıştı. Testere 03 biraz ses yapıyor, takip edilmeli.',
            ],
        ];

        foreach ($handoverData as $hd) {
            $handover = ShiftHandover::create([
                'location_id'      => $hd['location_id'],
                'shift_date'       => $hd['shift_date'],
                'shift_type'       => $hd['shift_type'],
                'outgoing_user_id' => $hd['outgoing']->id,
                'incoming_user_id' => $hd['incoming']->id,
                'outgoing_notes'   => $hd['note'],
                'status'           => 'tamamlandi',
                'completed_at'     => $hd['shift_date']->copy()->addHours($hd['shift_type'] === '06-14' ? 14 : 22),
                'created_at'       => $hd['shift_date']->copy()->addHours($hd['shift_type'] === '06-14' ? 13 : 21),
            ]);

            $assetsForHandover = $hd['assets'];
            if ($assetsForHandover->isEmpty()) {
                $assetsForHandover = Asset::take(5)->get();
            }

            foreach ($assetsForHandover as $idx => $asset) {
                // Make 1-2 assets 'hasarli'
                $condition = 'iyi';
                $notes = null;
                if ($idx === 1) {
                    $condition = 'hasarli';
                    $notes = 'Titreşim normalin üzerinde, rulman kontrolü gerekli';
                } elseif ($idx === 4 && $assetsForHandover->count() > 4) {
                    $condition = 'hasarli';
                    $notes = 'Yağ sızıntısı tespit edildi, conta değişimi planlanmalı';
                }

                ShiftHandoverItem::create([
                    'shift_handover_id' => $handover->id,
                    'asset_id'          => $asset->id,
                    'condition'         => $condition,
                    'notes'             => $notes,
                    'work_order_id'     => ($condition === 'hasarli' && ! empty($workOrders)) ? $workOrders[0]->id : null,
                ]);
            }
        }
        $this->command->info('  ✓ 2 shift handovers created (yesterday, with damaged items)');

        // ─── 4. Purchase Requests ────────────────────────────────────
        $lowStockParts = SparePart::whereColumn('quantity_on_hand', '<', 'minimum_quantity')->get();

        $prData = [
            ['part_idx' => 0, 'status' => 'bekliyor',  'qty' => 10, 'reason' => 'Stok minimum seviyenin altında, acil tedarik gerekli'],
            ['part_idx' => 1, 'status' => 'bekliyor',  'qty' => 8,  'reason' => 'Planlı bakım için stok takviyesi'],
            ['part_idx' => 2, 'status' => 'onaylandi', 'qty' => 5,  'reason' => 'Kritik parça, hız kontrol kartı stoğu tükenmek üzere'],
        ];

        foreach ($prData as $idx => $prd) {
            $part = $lowStockParts[$prd['part_idx']] ?? $lowStockParts->first();
            if (! $part) {
                $part = SparePart::first();
            }

            $pr = new PurchaseRequest();
            $pr->request_number = null; // Let boot auto-generate
            $pr->spare_part_id = $part->id;
            $pr->requested_quantity = $prd['qty'];
            $pr->estimated_cost = $part->unit_cost * $prd['qty'];
            $pr->status = $prd['status'];
            $pr->reason = $prd['reason'];
            $pr->requested_by = $admin->id;
            $pr->approved_by = $prd['status'] === 'onaylandi' ? $admin->id : null;
            $pr->created_at = now()->subDays(3 - $idx);
            $pr->updated_at = now()->subDays(3 - $idx);
            $pr->save();
        }
        $this->command->info('  ✓ 3 purchase requests created (2 bekliyor, 1 onaylandı)');

        // ─── 5. Stock Movements (history for completed WOs) ──────────
        // Add some initial stock-in movements
        foreach ($spareParts as $part) {
            SparePartMovement::create([
                'spare_part_id'  => $part->id,
                'movement_type'  => 'giris',
                'quantity'       => $part->quantity_on_hand,
                'reference_type' => 'initial_stock',
                'reference_id'   => 0,
                'performed_by'   => $admin->id,
                'notes'          => 'İlk stok girişi',
                'created_at'     => now()->subDays(30),
            ]);
        }
        $this->command->info('  ✓ Stock movements created');

        $this->command->info('');
        $this->command->info('FOM demo data seeding complete!');
        $this->command->info('  Dashboard: /fom');
        $this->command->info('  Work Orders: /fom/work-orders');
        $this->command->info('  Low Stock: /fom/spare-parts/low-stock');
    }
}
