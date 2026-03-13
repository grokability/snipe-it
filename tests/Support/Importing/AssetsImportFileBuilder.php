<?php

declare(strict_types=1);

namespace Tests\Support\Importing;

use Illuminate\Support\Str;

/**
 * Build an assets import file at runtime for testing.
 *
 * @template Row of array{
 *  assigneeFullName?: string,
 *  assigneeEmail?: string,
 *  assigneeUsername?: string,
 *  category?: string,
 *  companyName?: string,
 *  itemName?: string,
 *  location?: string,
 *  manufacturerName?: int,
 *  model?: string,
 *  modelNumber?: string,
 *  notes?: string,
 *  purchaseCost?: int,
 *  purchaseDate?: string,
 *  serialNumber?: string,
 *  supplierName?: string,
 *  status?: string,
 *  tag?: string,
 *  warrantyInMonths?: int,
 * }
 *
 * @extends FileBuilder<Row>
 */
class AssetsImportFileBuilder extends FileBuilder
{
    /**
     * @inheritdoc
     */
    protected function getDictionary(): array
    {
        return [
            'id'                  => 'ID',
            'assigneeFullName'    => 'Full Name',
            'assigneeEmail'       => 'Email',
            'assigneeUsername'    => 'Username',
            'category'            => 'Category',
            'companyName'         => 'Company',
            'itemName'            => 'item Name',
            'location'            => 'Location',
            'manufacturerName'    => 'Manufacturer',
            'model'               => 'Model name',
            'modelNumber'         => 'Model Number',
            'notes'               => 'Notes',
            'purchaseCost'        => 'Purchase Cost',
            'purchaseDate'        => 'Purchase Date',
            'serialNumber'        => 'Serial number',
            'supplierName'        => 'Supplier',
            'status'              => 'Status',
            'tag'                 => 'Asset Tag',
            'warrantyInMonths'    => 'Warranty',
        ];
    }

    /**
     * @inheritdoc
     */
    public function definition(): array
    {
        $faker = fake();

        return [
            'asset_tag'           => Str::random(),
            'assigneeFullName'    => $faker->name,
            'assigneeEmail'       => $faker->email,
            'assigneeUsername'    => $faker->userName,
            'category'            => Str::random(),
            'companyName'         => Str::random() . " {$faker->companySuffix}",
            'itemName'            => Str::random(),
            'location'            => "{$faker->country},{$faker->city}",
            'manufacturerName'    => $faker->company,
            'model'               => Str::random(),
            'modelNumber'         => Str::random(),
            'notes'               => $faker->sentence(5),
            'purchaseCost'        => rand(1, 100_000),
            'purchaseDate'        => $faker->date,
            'serialNumber'        => $faker->uuid,
            'supplierName'        => $faker->company,
            'status'              => $faker->randomElement(['Ready to Deploy', 'Archived', 'Pending']),
            'tag'                 => Str::random(),
            'warrantyInMonths'    => rand(1, 12),
        ];
    }
}
