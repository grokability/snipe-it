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
class ManufacturersImportFileBuilder extends FileBuilder
{
    /**
     * @inheritdoc
     */
    protected function getDictionary(): array
    {
        return [
            'name'           => 'name',
            'support_email'          => 'support_email',
            'support_phone'          => 'Support Phone',
            'notes'             => 'notes',
            'support_fax'             => 'support_fax',
            'support_url'             => 'support_url',
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
            'support_email'          => Str::random(32) . "@{$faker->freeEmailDomain}",
            'support_phone'    => $faker->phoneNumber,
            'support_fax'    => $faker->phoneNumber,
            'support_url'    => $faker->url(),
        ];
    }
}
