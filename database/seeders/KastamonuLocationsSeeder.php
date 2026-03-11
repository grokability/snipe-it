<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Location;
use Illuminate\Database\Seeder;

class KastamonuLocationsSeeder extends Seeder
{
    public function run(): void
    {
        // ── Root location ──
        $fabrika = $this->createLocation('Kastamonu Entegre Fabrika', null, [
            'address' => 'Organize Sanayi Bölgesi',
            'city'    => 'Kastamonu',
            'country' => 'TR',
        ]);

        // ── Level 2 ──
        $mdf = $this->createLocation('MDF Üretim Hattı', $fabrika->id);
        $depo = $this->createLocation('Depo ve Lojistik', $fabrika->id);
        $bakim = $this->createLocation('Bakım Atölyesi', $fabrika->id);
        $kalite = $this->createLocation('Kalite Kontrol Laboratuvarı', $fabrika->id);
        $ofis = $this->createLocation('Yönetim Ofisleri', $fabrika->id);

        // ── Level 3 (under MDF Üretim Hattı) ──
        $this->createLocation('Hat 1 - Pres Bölümü', $mdf->id);
        $this->createLocation('Hat 2 - Kaplama Bölümü', $mdf->id);
        $this->createLocation('Hat 3 - Kesim Bölümü', $mdf->id);

        $this->command->info('');

        // ── Departments ──
        $departments = [
            ['name' => 'Üretim',         'location_id' => $mdf->id],
            ['name' => 'Bakım & Onarım', 'location_id' => $bakim->id],
            ['name' => 'Kalite Kontrol',  'location_id' => $kalite->id],
            ['name' => 'Lojistik',        'location_id' => $depo->id],
            ['name' => 'IT',              'location_id' => $ofis->id],
        ];

        foreach ($departments as $dept) {
            $department = Department::firstOrCreate(
                ['name' => $dept['name']],
                ['location_id' => $dept['location_id']]
            );
            $this->command->info("  Department: {$department->name} (ID: {$department->id})");
        }

        $this->command->info('');
        $this->command->info('Done. ' . Location::where('name', 'like', '%Kastamonu%')->orWhere('parent_id', $fabrika->id)->count() . ' locations, ' . count($departments) . ' departments created.');
    }

    private function createLocation(string $name, ?int $parentId, array $extra = []): Location
    {
        $location = Location::firstOrCreate(
            ['name' => $name],
            array_merge(['parent_id' => $parentId], $extra)
        );

        $indent = $parentId ? '    ' : '  ';
        if ($location->parent_id && Location::find($location->parent_id)?->parent_id) {
            $indent = '      ';
        }
        $this->command->info("{$indent}Location: {$location->name} (ID: {$location->id})");

        return $location;
    }
}
