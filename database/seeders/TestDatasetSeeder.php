<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TestDatasetSeeder extends Seeder
{
    public function run(): void
    {
        
        $this->call([
            SettingsSeeder::class,
            CompanySeeder::class,
            DepartmentSeeder::class,
            GroupMatrixSeeder::class,
            UserMatrixSeeder::class,
        ]);

        $dataset = env('TEST_DATASET', 'mini'); 

        if ($dataset === 'avg') {
            $this->call([
                ManufacturerSeeder::class,
                CategorySeeder::class,
                StatuslabelSeeder::class,
                LocationSeeder::class,
                SupplierSeeder::class,

                AssetModelSeeder::class,
                AssetSeeder::class,

                LicenseSeeder::class,          
                ComponentSeeder::class,
                ConsumableSeeder::class,
                AccessorySeeder::class,

                PredefinedFilterPermissionSeeder::class,
                PredefinedFilterSeeder::class,

                AverageSeeder::class,
                ActionlogSeeder::class,     
            ]);
        }

        if ($dataset === 'huge') {
            $this->call([
                ManufacturerSeeder::class,
                CategorySeeder::class,
                StatuslabelSeeder::class,
                LocationSeeder::class,
                SupplierSeeder::class,

                AssetModelSeeder::class,
                AssetSeeder::class,

                LicenseSeeder::class,          
                ComponentSeeder::class,
                ConsumableSeeder::class,
                AccessorySeeder::class,

                PredefinedFilterPermissionSeeder::class,
                PredefinedFilterSeeder::class,

                HugeSeeder::class,
                ActionlogSeeder::class,    

            ]);
        }
    }
}
