<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run()
    {
        Department::truncate();

        if (! Location::count()) {
            $this->call(LocationSeeder::class);
        }

        $locationIds = Location::all()->pluck('id');

        $admin = User::where('permissions->superuser', '1')->first() ?? User::factory()->firstAdmin()->create();

        Department::factory()->count(1)->hr()->create([
            'location_id' => $locationIds->random(),
            'created_by' => $admin->id,
        ]);

        Department::factory()->count(1)->engineering()->create([
            'location_id' => $locationIds->random(),
            'created_by' => $admin->id,
        ]);

        Department::factory()->count(1)->marketing()->create([
            'location_id' => $locationIds->random(),
            'created_by' => $admin->id,
        ]);

        Department::factory()->count(1)->client()->create([
            'location_id' => $locationIds->random(),
            'created_by' => $admin->id,
        ]);

        Department::factory()->count(1)->product()->create([
            'location_id' => $locationIds->random(),
            'created_by' => $admin->id,
        ]);

        Department::factory()->count(1)->silly()->create([
            'location_id' => $locationIds->random(),
            'created_by' => $admin->id,
        ]);
    }
}
