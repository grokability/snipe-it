<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Company;
use App\Models\Consumable;
use App\Models\Manufacturer;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Supplier;

class ConsumableFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Consumable::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->words(3, true),
            'category_id' => Category::factory(),
            'created_by' => User::factory()->superuser(),
            'item_no' => $this->faker->numberBetween(1000000, 50000000),
            'order_number' => $this->faker->numberBetween(1000000, 50000000),
            'purchase_date' => $this->faker->dateTimeBetween('-1 years', 'now', date_default_timezone_get())->format('Y-m-d'),
            'purchase_cost' => $this->faker->randomFloat(2, 1, 50),
            'qty' => $this->faker->numberBetween(5, 10),
            'min_amt' => $this->faker->numberBetween($min = 1, $max = 2),
            'company_id' => Company::factory(),
            'supplier_id' => Supplier::factory(),
        ];
    }

    public function cardstock()
    {
        return $this->state(function () {
            return [
                'name' => 'Cardstock (White)',
                'category_id' => function () {
                    return Category::where('name', 'Printer Paper')->first() ?? Category::factory()->consumablePaperCategory();
                },
                'manufacturer_id' => function () {
                    return Manufacturer::where('name', 'Avery')->first() ?? Manufacturer::factory()->avery();
                },
                'qty' => 10,
                'min_amt' => 2,
            ];
        });
    }

    public function paper()
    {
        return $this->state(function () {
            return [
                'name' => 'Laserjet Paper (Ream)',
                'category_id' => function () {
                    return Category::where('name', 'Printer Paper')->first() ?? Category::factory()->consumablePaperCategory();
                },
                'manufacturer_id' => function () {
                    return Manufacturer::where('name', 'Avery')->first() ?? Manufacturer::factory()->avery();
                },
                'qty' => 20,
                'min_amt' => 2,
            ];
        });
    }

    public function ink()
    {
        return $this->state(function () {
            return [
                'name' => 'Laserjet Toner (black)',
                'category_id' => function () {
                    return Category::where('name', 'Printer Ink')->first() ?? Category::factory()->consumableInkCategory();
                },
                'manufacturer_id' => function () {
                    return Manufacturer::where('name', 'HP')->first() ?? Manufacturer::factory()->hp();
                },
                'qty' => 20,
                'min_amt' => 2,
            ];
        });
    }

    public function withoutItemsRemaining()
    {
        return $this->state(function () {
            return [
                'qty' => 1,
            ];
        })->afterCreating(function (Consumable $consumable) {
            $user = User::factory()->create();

            $consumable->users()->attach($consumable->id, [
                'consumable_id' => $consumable->id,
                'created_by' => $user->id,
                'assigned_to' => $user->id,
                'note' => '',
            ]);
        });
    }

    public function requiringAcceptance()
    {
        return $this->afterCreating(function (Consumable $consumable) {
            $consumable->category->update(['require_acceptance' => 1]);
        });
    }

    public function checkedOutToUser(User $user = null)
    {
        return $this->afterCreating(function (Consumable $consumable) use ($user) {
            $consumable->users()->attach($consumable->id, [
                'consumable_id' => $consumable->id,
                'created_at' => Carbon::now(),
                'created_by' => User::factory()->create()->id,
                'assigned_to' => $user->id ?? User::factory()->create()->id,
            ]);
        });
    }
}
