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
class CategoriesImportFileBuilder extends FileBuilder
{
    /**
     * @inheritdoc
     */
    protected function getDictionary(): array
    {
        return [
            'name'           => 'name',
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
            'notes'   => $faker->sentence,
            'category_type'   => 'asset',
            'require_acceptance' => 1,
            'use_default_eula'  => 1,
            'checkin_email'  => 1,
        ];
    }
}
