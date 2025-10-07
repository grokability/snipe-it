<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\{Asset, AssetModel, Location, Manufacturer, Statuslabel, User, Company, PredefinedFilter};

class HugeSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['assets','models','locations','manufacturers','status_labels','users','companies','settings'] as $t) {
            if (!Schema::hasTable($t)) {
                echo "Skip: missing table {$t}\n";
                return;
            }
        }

        DB::table('settings')->updateOrInsert(['id' => 1], ['scope_locations_fmcs' => 0]);

        DB::disableQueryLog();

        $models    = (int) env('SEED_MODELS',    300);
        $locations = (int) env('SEED_LOCATIONS', 120);
        $users     = (int) env('SEED_USERS',     2000);
        $assets    = (int) env('SEED_ASSETS',    50000);
        $chunk     = (int) env('SEED_CHUNK',     2000);

        Statuslabel::factory()->count(10)->create();

        $creator = User::inRandomOrder()->first() ?? User::factory()->create();
        $now = now();

        $companyRows = collect(range(1, 8))->map(fn () => [
            'name'       => fake()->company(),
            'created_by' => $creator->id,
            'notes'      => 'Created by DB seeder',
            'created_at' => $now,
            'updated_at' => $now,
        ])->all();
        DB::table('companies')->upsert($companyRows, ['name'], ['notes','updated_at','created_by']);

        $manufacturerRows = collect(range(1, 40))->map(fn () => [
            'name'       => fake()->company(),
            'created_at' => $now,
            'updated_at' => $now,
        ])->all();
        DB::table('manufacturers')->upsert($manufacturerRows, ['name'], ['updated_at']);

        Location::factory()->count($locations)->create();
        User::factory()->count($users)->create();
        AssetModel::factory()->count($models)->create();

        if (Schema::hasTable('predefined_filters')
            && class_exists(PredefinedFilter::class)
            && method_exists(PredefinedFilter::class, 'factory')) {

            $pfCreator = User::inRandomOrder()->first() ?? $creator;
            $companyId = Company::inRandomOrder()->value('id') ?? Company::factory()->create()->id;

            PredefinedFilter::factory()
                ->count(50)
                ->state(fn () => ['filter_data' => ['company_id' => [$companyId]]])
                ->for($pfCreator, 'createdBy')
                ->create();
        }

        Asset::withoutEvents(function () use ($assets, $chunk) {
            for ($i = 0; $i < $assets; $i += $chunk) {
                $count = min($chunk, $assets - $i);
                Asset::factory()->count($count)->create();
                echo "Assets: ".($i + $count)."/{$assets}\n";
            }
        });
    }
}
