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

        $filters = [
            [
                'name'         => 'Company Filter',
                'filter_data'  => ['company_id' => 1],
            ],
            [
                'name'         => 'Category: Desktop',
                'filter_data'  => ['category_id' => 2],
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
                'name'         => 'Asset Tag Like 123',

                'filter_data'  => ['asset_tag' => '123'],
            ],
            [
                'name'         => 'Combo: Company + RAM',
                'filter_data'  => [
                    'company_id'    => 1,
                    'custom_fields' => ['_snipeit_ram_3' => '32'],
                ],
            ],
        ];

        $user = User::first();

        try {
            foreach ($filters as $filter) {
                PredefinedFilter::create([
                    'name'         => $filter['name'],
                    'created_by'   => $user->id,
                    'filter_data'  => $filter['filter_data'],
                ]);
            }
        }catch (\Exception $e) {
            echo "Error: " . $e->getMessage() . "\n";
            echo $e->getTraceAsString();
        }
    }
}
