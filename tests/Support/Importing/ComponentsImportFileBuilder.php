<?php

declare(strict_types=1);

namespace Tests\Support\Importing;

use Illuminate\Support\Str;

/**
 * Build a components import file at runtime for testing.
 *
 * @template Row of array{
 *  category?: string,
 *  companyName?: string,
 *  itemName?: string,
 *  location?: string,
 *  orderNumber?: string,
 *  purchaseCost?: int,
 *  purchaseDate?: string,
 *  quantity?: int,
 *  serialNumber?: string,
 * }
 *
 * @extends FileBuilder<Row>
 */
class ComponentsImportFileBuilder extends FileBuilder
{
    /**
     * @inheritdoc
     */
    protected function getDictionary(): array
    {
        return [
            'category'     => 'Category',
            'companyName'  => 'Company',
            'itemName'     => 'item Name',
            'location'     => 'Location',
            'orderNumber'  => 'Order Number',
            'purchaseCost' => 'Purchase Cost',
            'purchaseDate' => 'Purchase Date',
            'quantity'     => 'Quantity',
            'serialNumber' => 'Serial number',
        ];
    }

    /**
     * @inheritdoc
     */
    public function definition(): array
    {
        $faker = fake();

        return [
            'category'     => Str::random(),
            'companyName'  => Str::random() . " {$faker->companySuffix}",
            'itemName'     => Str::random(),
            'location'     => "{$faker->city}, {$faker->country}",
            'orderNumber'  => "ON:COM:{$faker->uuid}",
            'purchaseCost' => rand(1, 100_000),
            'purchaseDate' => $faker->date,
            'quantity'     => rand(1, 100_000),
            'serialNumber' => 'SN:COM:' . Str::random(),
        ];
    }
}
