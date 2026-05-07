<?php

namespace Database\Seeders;

use App\Models\Maintenance;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MaintenanceSeeder extends Seeder
{
    public function run()
    {
        Maintenance::truncate();
        Maintenance::factory()->create(['image' => '1.png']);
        Maintenance::factory()->create(['image' => '2.png']);
        Maintenance::factory()->create(['image' => '3.png']);
        Maintenance::factory()->create(['image' => '4.png']);
        Maintenance::factory()->create(['image' => '5.png']);
        Maintenance::factory()->create(['image' => '6.png']);
        Maintenance::factory()->create(['image' => '7.png']);
        Maintenance::factory()->create(['image' => '8.png']);
        Maintenance::factory()->create(['image' => '9.png']);
        Maintenance::factory()->create(['image' => '10.png']);
        Maintenance::factory()->create(['image' => '11.png']);

        $src = public_path('/img/demo/maintenances/');
        $dst = 'maintenances'.'/';
        $del_files = Storage::files($dst);

        foreach ($del_files as $del_file) { // iterate files
            $file_to_delete = str_replace($src, '', $del_file);
            Log::debug('Deleting: '.$file_to_delete);
            try {
                Storage::disk('public')->delete($dst.$del_file);
            } catch (\Exception $e) {
                Log::debug($e);
            }
        }

        $add_files = glob($src.'/*.*');
        foreach ($add_files as $add_file) {
            $file_to_copy = str_replace($src, '', $add_file);
            Log::debug('Copying: '.$file_to_copy);
            try {
                Storage::disk('public')->put($dst.$file_to_copy, file_get_contents($src.$file_to_copy));
            } catch (\Exception $e) {
                Log::debug($e);
            }
        }
    }
}
