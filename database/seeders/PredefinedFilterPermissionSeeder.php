<?php

namespace Database\Seeders;

use App\Models\PredefinedFilter;

use App\Models\PredefinedFilterPermission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Facades\Hash;

class PredefinedFilterPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PredefinedFilterPermission::query()->delete();

        $user_to_delete = User::where("email","predefinedfilters@permission.com")->first();

        if ($user_to_delete) {
            $user_to_delete->delete();
        }

        $user = User::firstOrCreate(
            ['email'=> 'predefined@filter.com'],
            [
            'activated' => 1,
            'first_name' => 'Filter',
            'last_name'=> 'Permission',
            'username' => 'filterPermission',
            'email'=> 'predefinedfilters@permission.com',
            'password'=> Hash::make('1234567890'),
            'permissions' => '{"superuser":"1"}',
        ]);

        if (!$user instanceof User) {
            throw new \Exception('user could not be created.. seeder aborting..');
        }

        $filters = PredefinedFilter::limit(3)->get();       
        
        try {
            foreach ($filters as $filter) {
                PredefinedFilterPermission::create([
                    'predefined_filter_id' => $filter->id,
                    'permission_group_id'  => 1,
                    'can_view'             => true,
                    'can_create'           => false,
                    'can_edit'             => false,
                    'can_delete'           => false,
                    'created_by'           => $user->id,
                ]);
            }
        }catch (\Exception $e) {
            echo "Error: " . $e->getMessage() . "\n";
            echo $e->getTraceAsString();
        }
    }
}