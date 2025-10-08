<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\{
    PredefinedFilter, User, Company, AssetModel, Location, Statuslabel, Supplier
};

class PredefinedFilterSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            if (method_exists(PredefinedFilter::class, 'groups')) {
                PredefinedFilter::all()->each(fn($pf) => $pf->groups()->detach());
            }
            PredefinedFilter::query()->delete();

            $owner = User::firstOrCreate(
                ['email'=> 'predefined@filter.com'],
                [
                    'activated' => 1,
                    'first_name' => 'Filter',
                    'last_name'=> 'Predefined',
                    'username' => 'filter',
                    'password'=> Hash::make('1234567890'),
                    'permissions' => '{"superuser":"1"}',
                ]
            );

            $hidden = User::firstOrCreate(
                ['email'=> 'hidden_predefined@filter.com'],
                [
                    'activated' => 0,
                    'first_name' => 'Hidden',
                    'last_name'=> 'Owner',
                    'username' => 'hidden_owner',
                    'password'=> Hash::make('1234567890'),
                ]
            );

            $company  = Company::factory()->create();
            $model    = AssetModel::factory()->create();
            $location = Location::factory()->create();
            $status   = Statuslabel::factory()->create();
            $supplier = Supplier::factory()->create();

            $filters = [
                [
                    'name' => 'Asset Tag UI Copy',
                    'filter_data' => json_decode('[{"field":"asset_tag","value":"123","operator":"contains","logic":"AND"}]', true),
                ],
                [
                    'name'          => 'Test Name Filter',
                    'filter_data'   => [
                        [
                        'field'         => 'name',
                        'value'         => 'Test',
                        'operator'      => 'equals',
                        'logic'         => 'AND'
                        ],
                    ],
                ],
                [
                    'name'          => 'Asset TAG Like 123',
                    'filter_data'   => [
                        [
                        'field'         => 'asset_tag',
                        'value'         => '123',
                        'operator'      => 'contains',
                        'logic'         => 'AND'
                        ],
                    ],
                ],
                [
                    'name'          => 'Model filter',
                    'filter_data'   => [
                        [
                        'field'         => 'model',
                        'value'         => [$model->id],
                        'operator'      => 'equals',
                        'logic'         => 'AND'
                        ],
                    ],
                ],
                [
                    'name'          => 'Serial filter',
                    'filter_data'   => [
                        [
                        'field'         => 'serial',
                        'value'         => 'FooBar',
                        'operator'      => 'equals',
                        'logic'         => 'AND'
                        ],
                    ],
                ],
                [
                    'name'          => 'Status filter',
                    'filter_data'   => [
                        [
                        'field'         => 'status',
                        'value'         => [$status->id],
                        'operator'      => 'equals',
                        'logic'         => 'AND'
                        ],
                    ],
                ],
                [
                    'name'          => 'Supplier filter',
                    'filter_data'   => [
                        [
                        'field'         => 'supplier',
                        'value'         => [$supplier->id],
                        'operator'      => 'equals',
                        'logic'         => 'AND'
                        ],
                    ],
                ],
                [
                    'name'          => 'Company filter',
                    'filter_data'   => [
                        [
                        'field'         => 'company',
                        'value'         => [$company->id],
                        'operator'      => 'equals',
                        'logic'         => 'AND'
                        ],
                    ],
                ],
                [
                    'name'          => 'RTD-Location filter',
                    'filter_data'   => [
                        [
                        'field'         => 'rtd_location',
                        'value'         => [$location->id],
                        'operator'      => 'equals',
                        'logic'         => 'AND'
                        ],
                    ],
                ],
                [
                    'name'          => 'Custom Field Ram',
                    'filter_data'   => [
                        [
                        'field'         => '_snipeit_ram_3',
                        'value'         => '32',
                        'operator'      => 'equals',
                        'logic'         => 'AND'
                        ],
                    ],
                ],
                [
                    'name'          => 'Purchased Between',
                    'filter_data'   => [
                        [
                        'field'         => 'purchase_date_start',
                        'value'         => '2024-10-15',
                        'operator'      => 'equals',
                        'logic'         => 'AND'
                        ],
                        [
                        'field'         => 'purchase_date_end',
                        'value'         => '2024-10-30',
                        'operator'      => 'equals',
                        'logic'         => 'AND'
                        ],
                    ],
                ],
                [
                    'name'          => 'Combo contains: Model AND CustomField_RAM',
                    'filter_data'   => [
                        [
                        'field'         => 'model',
                        'value'         => ['book'],
                        'operator'      => 'contains',
                        'logic'         => 'AND'
                        ],
                        [
                        'field'         => '_snipeit_ram_3',
                        'value'         => '32',
                        'operator'      => 'equals',
                        'logic'         => 'AND'
                        ],
                    ],
                ],
                [
                    'name'          => 'Combo contains: Model AND Manufacturer',
                    'filter_data'   => [
                        [
                        'field'         => 'model',
                        'value'         => ['book'],
                        'operator'      => 'contains',
                        'logic'         => 'AND'
                        ],
                        [
                        'field'         => 'manufacturer',
                        'value'         => [1],
                        'operator'      => 'equals',
                        'logic'         => 'AND'
                        ],
                    ],
                ],
                                [
                    'name'          => 'Combo contains: Model NOT Manufacturer',
                    'filter_data'   => [
                        [
                        'field'         => 'model',
                        'value'         => ['book'],
                        'operator'      => 'contains',
                        'logic'         => 'AND'
                        ],
                        [
                        'field'         => 'manufacturer',
                        'value'         => ['apple'],
                        'operator'      => 'contains',
                        'logic'         => 'NOT'
                        ],
                    ],
                ],
                [
                    'name'          => 'ShouldNotBeVisibleForUserFilter',
                    'created_by'    => $hidden->id,
                    'filter_data'       => [
                        [
                            'field'     => 'company',
                            'value'     => [$company->id],
                            'operator'  => 'equals',
                            'logic'     => 'AND'
                        ]
                    ]
                ],
            ];

            foreach ($filters as $f) {
                PredefinedFilter::create([
                    'name'        => $f['name'],
                    'created_by'  => $f['created_by'] ?? $owner->id,
                    'filter_data' => $f['filter_data'],
                ]);
            }
        });
    }
}