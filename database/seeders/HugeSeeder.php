<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Location;
use App\Models\Manufacturer;
use App\Models\Statuslabel;
use App\Models\User;
use App\Models\Company;

class HugeSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['assets','asset_models','locations','manufacturers','statuslabels','users','companies'] as $t) {
            if (!Schema::hasTable($t)) { echo "Skip: missing table {$t}\n"; return; }
        }

        $models    = (int) env('SEED_MODELS', 300);
        $locations = (int) env('SEED_LOCATIONS', 120);
        $users     = (int) env('SEED_USERS', 2000);
        $assets    = (int) env('SEED_ASSETS', 50000);

        Manufacturer::factory()->count(40)->create();
        Statuslabel::factory()->count(10)->create();
        Company::factory()->count(8)->create();
        Location::factory()->count($locations)->create();
        User::factory()->count($users)->create();
        AssetModel::factory()->count($models)->create();

        $chunk = 2000;
        for ($i = 0; $i < $assets; $i += $chunk) {
            Asset::factory()->count(min($chunk, $assets - $i))->create();
            echo "Assets: ".min($i+$chunk, $assets)."/{$assets}\n";
        }

        if (Schema::hasTable('predefined_filters')
            && class_exists(\App\Models\PredefinedFilter::class)
            && method_exists(\App\Models\PredefinedFilter::class, 'factory')) {
            \App\Models\PredefinedFilter::factory()->count(50)->create();
        }
    }
}