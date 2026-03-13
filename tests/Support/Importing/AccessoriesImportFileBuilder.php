<?php

declare(strict_types=1);

namespace Tests\Support\Importing;

use Illuminate\Support\Str;

/**
 * Build an accessories import file at runtime for testing.
 *
 * @template Row of array{
 * category?: string,
 * companyName?: string,
 * itemName?: string,
 * location?: string,
 * manufacturerName?: string,
 * modelNumber?: string,
 * notes?: string,
 * orderNumber?: string,
 * purchaseCost?: int,
 * purchaseDate?: string,
 * quantity?: int,
 * supplierName?: string
 * }
 *
 * @extends FileBuilder<Row>
 */
class AccessoriesImportFileBuilder extends FileBuilder
{
    /**
     * @inheritdoc
     */
    protected function getDictionary(): array
    {
        return [
            'category'         => 'Category',
            'companyName'      => 'Company',
            'itemName'         => 'Item Name',
            'location'         => 'Location',
            'manufacturerName' => 'Manufacturer',
            'modelNumber'      => 'Model Number',
            'notes'            => 'Notes',
            'orderNumber'      => 'Order Number',
            'purchaseCost'     => 'Purchase Cost',
            'purchaseDate'     => 'Purchase Date',
            'quantity'         => 'Quantity',
            'supplierName'     => 'Supplier',
        ];
    }

    /**
     * @inheritdoc
     */
    public function definition(): array
    {
        $faker = fake();

        return [
            'category'         => Str::random(),
            'companyName'      => Str::random(),
            'itemName'         => Str::random(),
            'location'         => "{$faker->city}, {$faker->country}",
            'manufacturerName' => $faker->company,
            'modelNumber'      => Str::random(),
            'notes'            => $faker->sentence,
            'orderNumber'      => Str::random(),
            'purchaseDate'     => $faker->date(),
            'purchaseCost'     => rand(1, 100),
            'quantity'         => rand(1, 100),
            'supplierName'     => $faker->company,
        ];
    }
}
