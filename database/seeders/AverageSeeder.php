<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Location;
use App\Models\Manufacturer;
use App\Models\Statuslabel;
use App\Models\User;
use App\Models\Company;


class AverageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (['assets','asset_models','locations','manufacturers','statuslabels','users','companies'] as $t) {
            if (!Schema::hasTable($t)) { echo "Skip: missing table {$t}\n"; return; }
        }

        Manufacturer::factory()->count(10)->create();
        Statuslabel::factory()->count(5)->create();
        Company::factory()->count(3)->create();
        Location::factory()->count(15)->create();
        User::factory()->count(50)->create();
        AssetModel::factory()->count(50)->create();

        $total = 2000;
        $chunk = 500;
        for ($i = 0; $i < $total; $i += $chunk) {
            Asset::factory()->count(min($chunk, $total - $i))->create();
            echo "Assets: ".min($i+$chunk, $total)."/{$total}\n";
        }
    }
}