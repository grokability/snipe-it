<?php

declare(strict_types=1);

namespace Tests\Support\Importing;

use Illuminate\Support\Str;

/**
 * Build a consumables import file at runtime for testing.
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
 *  supplier?: string,
 * }
 *
 * @extends FileBuilder<Row>
 */
class ConsumablesImportFileBuilder extends FileBuilder
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
            'supplier'     => 'Supplier',
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
            'orderNumber'  => "ON:CON:{$faker->uuid}",
            'purchaseCost' => rand(1, 100_000),
            'purchaseDate' => $faker->date,
            'quantity'     => rand(1, 100_000),
            'supplier'     => Str::random() . " {$faker->companySuffix}",
        ];
    }
}
