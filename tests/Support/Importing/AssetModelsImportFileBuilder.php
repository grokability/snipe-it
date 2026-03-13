<?php

declare(strict_types=1);

namespace Tests\Support\Importing;

use Illuminate\Support\Str;

/**
 * Build a users import file at runtime for testing.
 *
 * @template Row of array{
 *  name?: string,
 *  manufacturer?: string,
 *  category?: string,
 *  model_number?: string,
 *  requestable?: int,
 * }
 *
 * @extends FileBuilder<Row>
 */
class AssetModelsImportFileBuilder extends FileBuilder
{
    /**
     * @inheritdoc
     */
    protected function getDictionary(): array
    {
        return [
            'name'           => 'Name',
            'category'       => 'Category',
            'manufacturer'   => 'Manufacturer',
            'model_number'   => 'Model Number',
            'fieldset'       => 'Fieldset',
            'eol'            => 'EOL',
            'min_amt'        => 'Min Amount',
            'notes'          => 'Notes',
            'requestable'    => 'Requestable',

        ];
    }

    /**
     * @inheritdoc
     */
    public function definition(): array
    {
        $faker = fake();

        return [
            'name'            => $faker->catchPhrase,
            'category'        => Str::random(),
            'model_number'    => $faker->creditCardNumber(),
            'notes'           => 'Created by demo seeder',
        ];
    }
}
