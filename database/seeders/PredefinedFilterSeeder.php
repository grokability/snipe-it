<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PredefinedFilter;
use App\Models\User;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Facades\Hash;

class PredefinedFilterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PredefinedFilter::query()->delete();

        $user_to_delete = User::where("email","predefined@filter.com")->first();

        if ($user_to_delete) {
            $user_to_delete->delete();
        }

        
        $user = User::firstOrCreate(
            ['email'=> 'predefined@filter.com'],
            [
            'activated' => 1,
            'first_name' => 'Filter',
            'last_name'=> 'Predefined',
            'username' => 'filter',
            'email'=> 'predefined@filter.com',
            'password'=> Hash::make('1234567890'),
            'permissions' => '{"superuser":"1"}',
        ]);

        if (!$user instanceof User) {
            throw new \Exception('user could not be created.. seeder aborting..');
        }

        $filters = [
            [
                'name'         => 'Name Filter',
                'filter_data'  => ['name' => 'Test'],
            ],
            [
                'name'         => 'Asset Tag Like 123',
                'filter_data'  => ['asset_tag' => '123'],
            ],
            [
                'name'         => 'Model Filter',
                'filter_data'  => ['model_id' => '1'], // not tested
            ],
            [
                'name'         => 'Serial Filter',
                'filter_data'  => ['serial' => 'FooBar'], // not tested 
            ],
            [
                'name'         => 'purchase Filter',
                'filter_data'  => ['purchase_date' => '2025-02-02'], // not tested
            ],
            [
                'name'         => 'asset eol Filter',
                'filter_data'  => ['asset_eol_date' => '2025-02-02'], // not tested 
            ],
            [
                'name'         => 'eol explicit Filter',
                'filter_data'  => ['eol_explicit' => '2025-02-02'], // not tested
            ],
            [
                'name'         => 'purchase_cost',
                'filter_data'  => ['purchas_cost' => '42'], // not tested
            ],
            [
                'name'         => 'order number',
                'filter_data'  => ['order_number' => '42'], // not tested
            ],
            [
                'name'         => 'assigned to',
                'filter_data'  => ['assigned_to' => '1337'], // not tested needsSet
            ],
            [
                'name'         => 'notes',
                'filter_data'  => ['notes' => 'FooBar'], // not tested
            ],
            [
                'name'         => 'created by',
                'filter_data'  => ['created_by' => '1'], // not tested
            ],
            [
                'name'         => 'created at',
                'filter_data'  => ['created_at' => '2025-01-01 13:37:37'], // not tested
            ],
            [
                'name'         => 'updadet at',
                'filter_data'  => ['updated_at' => '2025-01-01 13:37:37'], // not tested
            ],
            [
                'name'         => 'physical',
                'filter_data'  => ['physical' => '1'], // not tested
            ],
            [
                'name'         => 'deleted at',
                'filter_data'  => ['deleted_at' => '2025-01-01 13:37:37'], // not tested
            ],
            [
                'name'         => 'status id',
                'filter_data'  => ['status_id' => '13'], // not tested
            ],
            [
                'name'         => 'archived',
                'filter_data'  => ['archived' => '1'], // not tested
            ],
            [
                'name'         => 'warranty months',
                'filter_data'  => ['warranty_months' => '42'], // not tested
            ],
            [
                'name'         => 'depreciate',
                'filter_data'  => ['depreciate' => '1'], // not tested
            ],
            [
                'name'         => 'supplier id',
                'filter_data'  => ['supplier_id' => '42'], // not tested
            ],
            [
                'name'         => 'requestable',
                'filter_data'  => ['requestable' => '1'], // not tested
            ],
            [
                'name'         => 'rtd location',
                'filter_data'  => ['rtd_location_id' => '42'], // not tested
            ],
            [
                'name'         => 'accepted',
                'filter_data'  => ['accepted' => '2025-02-02'], // not tested TBD
            ],
            [
                'name'         => 'last checkout',
                'filter_data'  => ['last_checkout' => '2025-01-01 13:37:37'], // not tested
            ],
            [
                'name'         => 'last checkin',
                'filter_data'  => ['last_checkin' => '2025-01-01 13:37:37'], // not tested
            ],
            [
                'name'         => 'expected checkin',
                'filter_data'  => ['expected_checkin' => '2025-02-02'], // not tested
            ],
            [
                'name'         => 'Company Id',
                'filter_data'  => ['company_id' => 1],
            ],
            [
                'name'         => 'assigned type',
                'filter_data'  => ['assigned_type' => 'App\Models\User'], // not tested
            ],
            [
                'name'         => 'last audit date',
                'filter_data'  => ['last_audit_date' => '2025-01-01 13:37:37'], // not tested
            ],
            [
                'name'         => 'next audit date',
                'filter_data'  => ['next_audit_date' => '2025-02-02'], // not tested
            ],
            [
                'name'         => 'location id',
                'filter_data'  => ['location_id' => '42'], // not tested
            ],
            [
                'name'         => 'checkin counter',
                'filter_data'  => ['checkin_counter' => '1337'], // not tested
            ],
            [
                'name'         => 'checkout_counter',
                'filter_data'  => ['checkout_counter' => '1337'],
            ],
            [
                'name'         => 'requests counter',
                'filter_data'  => ['requests_counter' => '1337'], // not tested
            ],
            [
                'name'         => 'byod',
                'filter_data'  => ['byod' => '1'], // not tested
            ],
            [
                'name'         => 'Custom RAM Filter',
                'filter_data'  => ['custom_fields' => ['_snipeit_ram_3' => '32']],
            ],
            
            [
                'name'         => 'Checked Out Between Dates',
                'filter_data'  => [
                    'checked_out_start' => '2025-08-01',
                    'checked_out_end'   => '2025-08-20',
                ],
            ],
            [
                'name'         => 'Combo: Company + RAM',
                'filter_data'  => [
                    'company_id'    => 1,
                    'custom_fields' => ['_snipeit_ram_3' => '32'],
                ],
            ],
            [
                'name'         => 'ShouldNotBeVisible',
                'created_by'    => 404,
                'filter_data'  => [
                    'company_id'    => 1,
                ],
            ],

        ];

        try {
            
            
            foreach ($filters as $filter) {
                $createdBy = array_key_exists('created_by', $filter) ? $filter['created_by'] : $user->id;
                
                PredefinedFilter::create([
                    'name'         => $filter['name'],
                    'created_by'   => $createdBy,
                    'filter_data'  => $filter['filter_data'],
                ]);
                
            }
        }catch (\Exception $e) {
            echo "Error: " . $e->getMessage() . "\n";
            echo $e->getTraceAsString();
        }
    }
}
