<?php

namespace Database\Seeders;

use App\Models\AssetModel;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AssetModelSeeder extends Seeder
{
    public function run()
    {
        AssetModel::truncate();

        $admin = User::where('permissions->superuser', '1')->first() ?? User::factory()->firstAdmin()->create();

        // Laptops
        AssetModel::factory()->count(1)->mbp13Model()->create(['created_by' => $admin->id]);
        AssetModel::factory()->count(1)->mbpAirModel()->create(['created_by' => $admin->id]);
        AssetModel::factory()->count(1)->surfaceModel()->create(['created_by' => $admin->id]);
        AssetModel::factory()->count(1)->xps13Model()->create(['created_by' => $admin->id]);
        AssetModel::factory()->count(1)->spectreModel()->create(['created_by' => $admin->id]);
        AssetModel::factory()->count(1)->zenbookModel()->create(['created_by' => $admin->id]);
        AssetModel::factory()->count(1)->yogaModel()->create(['created_by' => $admin->id]);

        // Desktops
        AssetModel::factory()->count(1)->macproModel()->create(['created_by' => $admin->id]);
        AssetModel::factory()->count(1)->lenovoI5Model()->create(['created_by' => $admin->id]);
        AssetModel::factory()->count(1)->optiplexModel()->create(['created_by' => $admin->id]);

        // Conference Phones
        AssetModel::factory()->count(1)->polycomModel()->create(['created_by' => $admin->id]);
        AssetModel::factory()->count(1)->polycomcxModel()->create(['created_by' => $admin->id]);

        // Tablets
        AssetModel::factory()->count(1)->ipadModel()->create(['created_by' => $admin->id]);
        AssetModel::factory()->count(1)->tab3Model()->create(['created_by' => $admin->id]);

        // Phones
        AssetModel::factory()->count(1)->iphone11Model()->create(['created_by' => $admin->id]);
        AssetModel::factory()->count(1)->iphone12Model()->create(['created_by' => $admin->id]);

        // Displays
        AssetModel::factory()->count(1)->ultrafine()->create(['created_by' => $admin->id]);
        AssetModel::factory()->count(1)->ultrasharp()->create(['created_by' => $admin->id]);

        $src = public_path('/img/demo/models/');
        $dst = 'models'.'/';
        $del_files = Storage::files($dst);

        foreach ($del_files as $del_file) { // iterate files
            try {
                Storage::disk('public')->delete($dst.$del_file);
            } catch (\Exception $e) {
                Log::debug($e);
            }
        }

        $add_files = glob($src.'/*.*');
        foreach ($add_files as $add_file) {
            $file_to_copy = str_replace($src, '', $add_file);
            try {
                Storage::disk('public')->put($dst.$file_to_copy, file_get_contents($src.$file_to_copy));
            } catch (\Exception $e) {
                Log::debug($e);
            }
        }
    }
}
