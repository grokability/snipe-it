<?php

declare(strict_types=1);

namespace Tests\Support\Importing;

/**
 * Build an asset-history import file at runtime for testing.
 *
 * @template Row of array{
 *  assetTag?: string,
 *  name?: string,
 *  email?: string,
 *  checkoutDate?: string,
 *  checkinDate?: string,
 * }
 *
 * @extends FileBuilder<Row>
 */
class AssetHistoryImportFileBuilder extends FileBuilder
{
    protected function getDictionary(): array
    {
        return [
            'assetTag' => 'Asset Tag',
            'name' => 'Name',
            'email' => 'Email',
            'checkoutDate' => 'Checkout Date',
            'checkinDate' => 'Checkin Date',
        ];
    }

    public function definition(): array
    {
        return [
            'assetTag' => 'AH-'.fake()->unique()->randomNumber(6),
            'name' => fake()->userName,
            'email' => fake()->safeEmail,
            'checkoutDate' => fake()->date,
            'checkinDate' => '',
        ];
    }
}
