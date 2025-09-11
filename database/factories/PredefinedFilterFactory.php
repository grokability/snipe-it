<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\User;
use App\Models\Category;
use App\Models\PredefinedFilter;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PredefinedFilter>
 */
class PredefinedFilterFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'created_by' => User::factory(),
        ];
    }

    public function company(Company $company, User $user) {
        return $this->state(fn () => [
            'name'       => $company->name,
            'created_by' => $user->id,
            'company_id' => $company->id,
            'notes'      => 'Created by DB seeder',
        ]);
    }

    public function category(Category $category, User $user) {
        return $this->state(fn () => [
            'name'       => $category->name,
            'created_by' => $user->id,
            'company_id' => $category->id,
            'notes'      => 'Created by DB seeder',
        ]);
    }
}
