<?php

namespace Database\Factories;

use App\Models\Accessory;
use App\Models\SavedSearch;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SavedSearch>
 */
class SavedSearchFactory extends Factory
{
    public function definition(): array
    {
        return [
            'created_by' => User::factory(),
            'table_name' => 'accessoriesListingTable',
            'model' => Accessory::class,
            'name' => $this->faker->unique()->words(3, true),
            'query_string' => 'filter[category]=1',
        ];
    }
}
