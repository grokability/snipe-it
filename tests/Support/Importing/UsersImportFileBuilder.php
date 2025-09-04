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
class UsersImportFileBuilder extends FileBuilder
{
    /**
     * @inheritdoc
     */
    protected function getDictionary(): array
    {
        return [
            'companyName'     => 'Company',
            'email'           => 'email',
            'employeeNumber'  => 'Employee Number',
            'firstName'       => 'First Name',
            'lastName'        => 'Last Name',
            'displayName'     => 'Display Name',
            'location'        => 'Location',
            'phoneNumber'     => 'Phone Number',
            'position'        => 'Job Title',
            'username'        => 'Username',
        ];
    }

    /**
     * @inheritdoc
     */
    public function definition(): array
    {
        $faker = fake();

        return [
            'companyName'     => $faker->company,
            'email'           => $faker->safeEmail(),
            'employeeNumber'  => $faker->uuid,
            'firstName'       => $faker->firstName,
            'lastName'        => $faker->lastName,
            'displayName'     => $faker->firstName,
            'location'        => "{$faker->city}, {$faker->country}",
            'phoneNumber'     => $faker->phoneNumber,
            'position'        => $faker->jobTitle,
            'username'        => $faker->userName(),
        ];
    }
}
