<?php

namespace Database\Seeders;

use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Seeder;

class ActionlogSeeder extends Seeder
{
    public function run()
    {
        Actionlog::truncate();

        if (! Asset::count()) {
            $this->call(AssetSeeder::class);
        }

        if (! Location::count()) {
            $this->call(LocationSeeder::class);
        }

        $admin = User::where('permissions->superuser', '1')->first() ?? User::factory()->firstAdmin()->create();

        Actionlog::factory()
            ->count(300)
            ->assetCheckoutToUser()
            ->create(['created_by' => $admin->id]);

        Actionlog::factory()
            ->count(100)
            ->assetCheckoutToLocation()
            ->create(['created_by' => $admin->id]);

        Actionlog::factory()
            ->count(20)
            ->licenseCheckoutToUser()
            ->create(['created_by' => $admin->id]);
    }
}
