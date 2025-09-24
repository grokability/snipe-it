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
                ['name' => 'Name Filter',              'filter_data' => ['name' => 'Test']],
                ['name' => 'Asset Tag Like 123',       'filter_data' => ['asset_tag' => '123']],
                ['name' => 'Model Filter',             'filter_data' => ['model_id' => (string)$model->id]],
                ['name' => 'Serial Filter',            'filter_data' => ['serial' => 'FooBar']],
                ['name' => 'Purchase Date',            'filter_data' => ['purchase_date' => '2025-02-02']],
                ['name' => 'Status',                   'filter_data' => ['status_id' => (string)$status->id]],
                ['name' => 'Supplier',                 'filter_data' => ['supplier_id' => (string)$supplier->id]],
                ['name' => 'Company Id',               'filter_data' => ['company_id' => (string)$company->id]],
                ['name' => 'RTD Location',             'filter_data' => ['rtd_location_id' => (string)$location->id]],
                ['name' => 'Custom RAM Filter',        'filter_data' => ['custom_fields' => ['_snipeit_ram_3' => '32']]],
                ['name' => 'Checked Out Between Dates','filter_data' => ['checked_out_start' => '2025-08-01','checked_out_end' => '2025-08-20']],
                ['name' => 'Combo: Company + RAM',     'filter_data' => ['company_id' => (string)$company->id, 'custom_fields' => ['_snipeit_ram_3' => '32']]],
                ['name' => 'ShouldNotBeVisible',       'created_by' => $hidden->id, 'filter_data' => ['company_id' => (string)$company->id]],
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