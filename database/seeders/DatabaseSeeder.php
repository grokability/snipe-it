<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Only create default settings if they do not exist in the db.
        if (! Setting::first()) {
            // factory(Setting::class)->create();
            $this->call(SettingsSeeder::class);
        }

        $this->call(CompanySeeder::class);
        $this->call(CategorySeeder::class);
        $this->call(LocationSeeder::class);
        $this->call(DepartmentSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(DepreciationSeeder::class);
        $this->call(ManufacturerSeeder::class);
        $this->call(SupplierSeeder::class);
        $this->call(AssetModelSeeder::class);
        $this->call(DepreciationSeeder::class);
        $this->call(StatuslabelSeeder::class);
        $this->call(AccessorySeeder::class);
        $this->call(CustomFieldSeeder::class);
        $this->call(AssetSeeder::class);
        $this->call(LicenseSeeder::class);
        $this->call(ComponentSeeder::class);
        $this->call(ConsumableSeeder::class);
        $this->call(ActionlogSeeder::class);
        $this->call(MaintenanceSeeder::class);

        Artisan::call('snipeit:sync-asset-locations', ['--output' => 'all']);
        $output = Artisan::output();
        Log::info($output);

        Model::reguard();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        DB::table('imports')->truncate();
        DB::table('requested_assets')->truncate();
    }
}
