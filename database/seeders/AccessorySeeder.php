<?php

namespace Database\Seeders;

use App\Models\Accessory;
use App\Models\AccessoryCheckout;
use App\Models\Location;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AccessorySeeder extends Seeder
{
    public function run()
    {
        Accessory::truncate();
        DB::table('accessories_checkout')->truncate();

        if (! Location::count()) {
            $this->call(LocationSeeder::class);
        }

        $locationIds = Location::all()->pluck('id');

        if (! Supplier::count()) {
            $this->call(SupplierSeeder::class);
        }

        $supplierIds = Supplier::all()->pluck('id');

        $admin = User::where('permissions->superuser', '1')->first() ?? User::factory()->firstAdmin()->create();

        Accessory::factory()->appleUsbKeyboard()->create([
            'location_id' => $locationIds->random(),
            'supplier_id' => $supplierIds->random(),
            'created_by' => $admin->id,
        ]);

        Accessory::factory()->appleBtKeyboard()->create([
            'location_id' => $locationIds->random(),
            'supplier_id' => $supplierIds->random(),
            'created_by' => $admin->id,
        ]);

        Accessory::factory()->appleMouse()->create([
            'location_id' => $locationIds->random(),
            'supplier_id' => $supplierIds->random(),
            'created_by' => $admin->id,
        ]);

        Accessory::factory()->microsoftMouse()->create([
            'location_id' => $locationIds->random(),
            'supplier_id' => $supplierIds->random(),
            'created_by' => $admin->id,
        ]);

        // Check out a handful of each accessory to random users so the
        // view page doesn't render empty. Uses the AccessoryCheckout
        // model directly (which is what the checkout controller writes
        // to) rather than trying to run controller actions from a seeder.
        $checkoutTargets = User::where('activated', 1)
            ->where('show_in_list', '!=', '0')
            ->inRandomOrder()
            ->limit(6)
            ->get();
        foreach (Accessory::all() as $accessory) {
            foreach ($checkoutTargets->random(min(rand(2, 4), $checkoutTargets->count())) as $user) {
                AccessoryCheckout::create([
                    'accessory_id' => $accessory->id,
                    'assigned_to' => $user->id,
                    'assigned_type' => User::class,
                    'created_by' => $admin->id,
                    'note' => 'Seeded demo checkout',
                ]);
            }
        }

        $src = public_path('/img/demo/accessories/');
        $dst = 'accessories'.'/';
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
