<?php

namespace Database\Factories;

use App\Models\AssetModel;
use App\Models\CustomField;
use App\Models\CustomFieldDefault;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CustomFieldDefault>
 */
class CustomFieldDefaultFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'custom_field_id' => CustomField::factory(),
            'asset_model_id' => function (array $attributes): int {
                $field = CustomField::whereKey($attributes['custom_field_id'])->firstOrFail();

                return AssetModel::factory()->hasMultipleCustomFields([$field])->create()->getKey();
            },
            'default_value' => $this->faker->word(),
        ];
    }
}
