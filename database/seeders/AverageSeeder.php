<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\{Asset, AssetModel, Location, Manufacturer, Statuslabel, User, Company};

class AverageSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['assets','models','locations','manufacturers','status_labels','users','companies','settings'] as $t) {
            if (!Schema::hasTable($t)) { echo "Skip: missing table {$t}\n"; return; }
        }

        DB::table('settings')->updateOrInsert(
            ['id' => 1],
            ['scope_locations_fmcs' => 0]
        );

        DB::disableQueryLog();

        $models    = (int) env('AVG_MODELS', 50);
        $locations = (int) env('AVG_LOCATIONS', 15);
        $users     = (int) env('AVG_USERS', 50);
        $assets    = (int) env('AVG_ASSETS', 2000);
        $chunk     = (int) env('AVG_CHUNK', 500);

        Manufacturer::factory()->count(10)->create();
        Statuslabel::factory()->count(5)->create();
        Company::factory()->count(3)->create();
        Location::factory()->count($locations)->create();
        User::factory()->count($users)->create();
        AssetModel::factory()->count($models)->create();

        Asset::withoutEvents(function () use ($assets, $chunk) {
            for ($i = 0; $i < $assets; $i += $chunk) {
                $count = min($chunk, $assets - $i);
                Asset::factory()->count($count)->create();
                echo "Assets: ".($i + $count)."/{$assets}\n";
            }
        });
    }
}
