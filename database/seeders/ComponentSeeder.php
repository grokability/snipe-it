<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\Company;
use App\Models\Component;
use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ComponentSeeder extends Seeder
{
    public function run()
    {
        Component::truncate();
        DB::table('components_assets')->truncate();

        if (! Company::count()) {
            $this->call(CompanySeeder::class);
        }

        $companyIds = Company::all()->pluck('id');

        if (! Location::count()) {
            $this->call(LocationSeeder::class);
        }

        $locationIds = Location::all()->pluck('id');

        Component::factory()->ramCrucial4()->create([
            'company_id' => $companyIds->random(),
            'location_id' => $locationIds->random(),
        ]);
        Component::factory()->ramCrucial8()->create([
            'company_id' => $companyIds->random(),
            'location_id' => $locationIds->random(),
        ]);
        Component::factory()->ssdCrucial120()->create([
            'company_id' => $companyIds->random(),
            'location_id' => $locationIds->random(),
        ]);
        Component::factory()->ssdCrucial240()->create([
            'company_id' => $companyIds->random(),
            'location_id' => $locationIds->random(),
        ]);

        // Check out a couple of each component to random assets so the
        // view page doesn't render an empty checkout list. Components
        // check out to assets (not users). Skipped gracefully if
        // AssetSeeder hasn't run yet.
        $admin = User::where('permissions->superuser', '1')->first();
        $checkoutTargets = Asset::inRandomOrder()->limit(6)->get();
        if ($admin && $checkoutTargets->isNotEmpty()) {
            foreach (Component::all() as $component) {
                foreach ($checkoutTargets->random(min(rand(2, 3), $checkoutTargets->count())) as $asset) {
                    $component->assets()->attach($asset->id, [
                        'created_by' => $admin->id,
                        'assigned_qty' => rand(1, 2),
                    ]);
                }
            }
        }
    }
}
