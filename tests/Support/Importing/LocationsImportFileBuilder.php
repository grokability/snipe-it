<?php

declare(strict_types=1);

namespace Tests\Support\Importing;

use Illuminate\Support\Str;

/**
 * Build a users import file at runtime for testing.
 *
 * @template Row of array{
 *  companyName?: string,
 *  email?: string,
 *  employeeNumber?: int,
 *  firstName?: string,
 *  lastName?: string,
 *  location?: string,
 *  phoneNumber?: string,
 *  position?: string,
 *  username?: string,
 * }
 *
 * @extends FileBuilder<Row>
 */
class LocationsImportFileBuilder extends FileBuilder
{
    /**
     * @inheritdoc
     */
    protected function getDictionary(): array
    {
        return [
            'name'           => 'name',
            'phone'          => 'Phone',
            'address'             => 'address',
            'address2'             => 'address2',
            'city'             => 'city',
            'state'             => 'state',
            'country'             => 'country',
            'zip'             => 'zip',
            'notes'             => 'notes',
        ];
    }

    /**
     * @inheritdoc
     */
    public function definition(): array
    {
        $faker = fake();

        return [
            'name'    => $faker->company,
            'phone'    => $faker->phoneNumber,
        ];
    }
}
