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
class SuppliersImportFileBuilder extends FileBuilder
{
    /**
     * @inheritdoc
     */
    protected function getDictionary(): array
    {
        return [
            'name'           => 'name',
            'email'          => 'email',
            'contact'          => 'contact',
            'phone'          => 'Phone',
            'address'             => 'address',
            'address2'             => 'address2',
            'city'             => 'city',
            'state'             => 'state',
            'country'             => 'country',
            'zip'             => 'zip',
            'notes'             => 'notes',
            'fax'             => 'fax',
            'url'             => 'url',
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
            'email'          => Str::random(32) . "@{$faker->freeEmailDomain}",
            'contact'       => $faker->firstName,
            'phone'    => $faker->phoneNumber,
            'fax'    => $faker->phoneNumber,
            'url'    => $faker->url(),
        ];
    }
}
