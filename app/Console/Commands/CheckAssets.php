<?php

namespace App\Console\Commands;

use App\Models\Asset;
use Illuminate\Console\Command;

class CheckAssets extends Command
{
    protected $signature = 'test:check-assets';
    protected $description = 'Debug asset count';

    public function handle()
    {
        $this->info("Checking assets...");
        
        // Try creating one directly
        $this->info("\nAttempting to create a test asset...");
        try {
            $asset = new Asset();
            $asset->name = "Direct Test Asset";
            $asset->asset_tag = "TEST-" . time();
            $asset->model_id = 1;
            $asset->status_id = 1;
            $result = $asset->save();
            
            $this->info("Save result: " . ($result ? 'TRUE' : 'FALSE'));
            $this->info("Asset ID after save: " . ($asset->id ?? 'NULL'));
            
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
        }
        
        // Different ways to count
        $count1 = Asset::count();
        $count2 = Asset::query()->count();
        $count3 = Asset::all()->count();
        
        $this->table(
            ['Method', 'Count'],
            [
                ['Asset::count()', $count1],
                ['Asset::query()->count()', $count2],
                ['Asset::all()->count()', $count3],
            ]
        );
        
        if ($count3 > 0) {
            $this->info("\nFirst 5 assets:");
            foreach (Asset::limit(5)->get() as $asset) {
                $this->line("- ID: {$asset->id}, Tag: {$asset->asset_tag}, Name: {$asset->name}");
            }
        }
        
        return 0;
    }
}
