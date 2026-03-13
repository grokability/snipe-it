<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class LocationSeeder extends Seeder
{
    public function run()
    {
        Location::truncate();
        Location::factory()->count(10)->create();

        $src = public_path('/img/demo/locations/');
        $dst = 'locations'.'/';
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
