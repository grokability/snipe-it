<?php

namespace App\Console\Commands;

use App\Models\Asset;
use App\Models\Category;
use App\Models\AssetModel;
use App\Models\Statuslabel;
use App\Models\Location;
use App\Models\Company;
use App\Models\Manufacturer;
use Illuminate\Console\Command;

class GenerateTestAssets extends Command
{
    protected $signature = 'assets:generate-test {--count=20}';
    protected $description = 'Generate test assets for the system';

    public function handle()
    {
        $count = $this->option('count');
        $this->info("Generating {$count} test assets...");
        
        // Get or create required data
        $category = $this->getOrCreateCategory();
        $manufacturer = $this->getOrCreateManufacturer();
        $model = $this->getOrCreateModel($category, $manufacturer);
        $status = $this->getOrCreateStatus();
        $location = $this->getOrCreateLocation();
        
        $assetTypes = [
            'Server', 'Workstation', 'Laptop', 'Printer', 'Scanner',
            'Router', 'Switch', 'Monitor', 'UPS', 'HVAC System',
            'Generator', 'Conveyor', 'Machine Tool', 'Vehicle', 'Forklift'
        ];
        
        $created = 0;
        
        for ($i = 1; $i <= $count; $i++) {
            $type = $assetTypes[array_rand($assetTypes)];
            $assetTag = 'ASSET-' . str_pad($i, 5, '0', STR_PAD_LEFT);
            
            try {
                $asset = new Asset();
                $asset->name = "{$type} {$i}";
                $asset->asset_tag = $assetTag;
                $asset->model_id = $model->id;
                $asset->status_id = $status->id;
                $asset->location_id = $location->id;
                $asset->rtd_location_id = $location->id;
                $asset->purchase_date = now()->subDays(rand(30, 365));
                $asset->purchase_cost = rand(500, 50000);
                $asset->notes = "Auto-generated test asset #{$i}";
                
                if ($asset->save()) {
                    $created++;
                    $this->line("  ✓ Created: {$asset->asset_tag} - {$asset->name} (ID: {$asset->id})");
                } else {
                    $this->error("  ✗ Save returned false for asset #{$i}");
                }
                
            } catch (\Exception $e) {
                $this->error("  ✗ Failed to create asset #{$i}: {$e->getMessage()}");
            }
        }
        
        $this->info("\n✅ Successfully created {$created} assets!");
        return 0;
    }
    
    private function getOrCreateCategory()
    {
        $category = Category::where('name', 'Test Equipment')->first();
        
        if (!$category) {
            $category = Category::create([
                'name' => 'Test Equipment',
                'category_type' => 'asset',
                'eula_text' => null,
                'require_acceptance' => 0,
                'checkin_email' => 0,
            ]);
            $this->info("Created category: Test Equipment");
        }
        
        return $category;
    }
    
    private function getOrCreateManufacturer()
    {
        $manufacturer = Manufacturer::where('name', 'Generic Manufacturer')->first();
        
        if (!$manufacturer) {
            $manufacturer = Manufacturer::create([
                'name' => 'Generic Manufacturer',
                'support_email' => 'support@example.com',
                'support_phone' => '1-800-TEST',
            ]);
            $this->info("Created manufacturer: Generic Manufacturer");
        }
        
        return $manufacturer;
    }
    
    private function getOrCreateModel($category, $manufacturer)
    {
        $model = AssetModel::where('name', 'Generic Model')->first();
        
        if (!$model) {
            $model = AssetModel::create([
                'name' => 'Generic Model',
                'model_number' => 'GEN-001',
                'category_id' => $category->id,
                'manufacturer_id' => $manufacturer->id,
            ]);
            $this->info("Created model: Generic Model");
        }
        
        return $model;
    }
    
    private function getOrCreateStatus()
    {
        $status = Statuslabel::where('name', 'Ready to Deploy')->first();
        
        if (!$status) {
            $status = Statuslabel::create([
                'name' => 'Ready to Deploy',
                'deployable' => 1,
                'pending' => 0,
                'archived' => 0,
                'color' => 'green',
                'show_in_nav' => 0,
                'default_label' => 1,
            ]);
            $this->info("Created status: Ready to Deploy");
        }
        
        return $status;
    }
    
    private function getOrCreateLocation()
    {
        $location = Location::where('name', 'Test Facility')->first();
        
        if (!$location) {
            $location = Location::create([
                'name' => 'Test Facility',
                'address' => '123 Test Street',
                'city' => 'Test City',
                'state' => 'TS',
                'country' => 'TC',
                'zip' => '12345',
            ]);
            $this->info("Created location: Test Facility");
        }
        
        return $location;
    }
}
