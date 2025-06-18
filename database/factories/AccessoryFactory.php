<?php

namespace Database\Factories;

use App\Models\Accessory;
use App\Models\Asset;
use App\Models\Category;
use App\Models\Location;
use App\Models\Manufacturer;
use App\Models\Supplier;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class AccessoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Accessory::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => sprintf(
                '%s %s',
                $this->faker->randomElement(['Bluetooth', 'Wired']),
                $this->faker->randomElement(['Keyboard', 'Wired'])
            ),
            'created_by' => User::factory()->superuser(),
            'category_id' => Category::factory()->forAccessories(),
            'model_number' => $this->faker->numberBetween(1000000, 50000000),
            'location_id' => Location::factory(),
            'qty' => 1,
        ];
    }

    public function appleBtKeyboard()
    {
        return $this->state(function () {
            return [
                'name' => 'Bluetooth Keyboard',
                'image' => 'bluetooth.jpg',
                'category_id' => function () {
                    return Category::where('name', 'Keyboards')->first() ?? Category::factory()->accessoryKeyboardCategory();
                },
                'manufacturer_id' => function () {
                    return Manufacturer::where('name', 'Apple')->first() ?? Manufacturer::factory()->apple();
                },
                'qty' => 10,
                'min_amt' => 2,
                'supplier_id' => Supplier::factory(),
            ];
        });
    }

    public function appleUsbKeyboard()
    {
        return $this->state(function () {
            return [
                'name' => 'USB Keyboard',
                'image' => 'usb-keyboard.jpg',
                'category_id' => function () {
                    return Category::where('name', 'Keyboards')->first() ?? Category::factory()->accessoryKeyboardCategory();
                },
                'manufacturer_id' => function () {
                    return Manufacturer::where('name', 'Apple')->first() ?? Manufacturer::factory()->apple();
                },
                'qty' => 15,
                'min_amt' => 2,
                'supplier_id' => Supplier::factory(),
            ];
        });
    }

    public function appleMouse()
    {
        return $this->state(function () {
            return [
                'name' => 'Magic Mouse',
                'image' => 'magic-mouse.jpg',
                'category_id' => function () {
                    return Category::where('name', 'Mouse')->first() ?? Category::factory()->accessoryMouseCategory();
                },
                'manufacturer_id' => function () {
                    return Manufacturer::where('name', 'Apple')->first() ?? Manufacturer::factory()->apple();
                },
                'qty' => 13,
                'min_amt' => 2,
                'supplier_id' => Supplier::factory(),
            ];
        });
    }

    public function microsoftMouse()
    {
        return $this->state(function () {
            return [
                'name' => 'Sculpt Comfort Mouse',
                'image' => 'comfort-mouse.jpg',
                'category_id' => function () {
                    return Category::where('name', 'Mouse')->first() ?? Category::factory()->accessoryMouseCategory();
                },
                'manufacturer_id' => function () {
                    return Manufacturer::where('name', 'Microsoft')->first() ?? Manufacturer::factory()->microsoft();
                },
                'qty' => 13,
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
        })->afterCreating(function ($accessory) {
            $user = User::factory()->create();

            $accessory->checkouts()->create([
                'accessory_id' => $accessory->id,
                'created_at' => Carbon::now(),
                'created_by' => $user->id,
                'assigned_to' => $user->id,
                'assigned_type' => User::class,
                'note' => '',
            ]);
        });
    }

    public function requiringAcceptance()
    {
        return $this->afterCreating(function ($accessory) {
            $accessory->category->update(['require_acceptance' => 1]);
        });
    }

    public function checkedOutToUser(User $user = null)
    {
        return $this->afterCreating(function (Accessory $accessory) use ($user) {
            $accessory->checkouts()->create([
                'accessory_id' => $accessory->id,
                'created_at' => Carbon::now(),
                'created_by' => 1,
                'assigned_to' => $user->id ?? User::factory()->create()->id,
                'assigned_type' => User::class,
            ]);
        });
    }

    public function checkedOutToUsers(array $users)
    {
        return $this->afterCreating(function (Accessory $accessory) use ($users) {
            foreach ($users as $user) {
                $accessory->checkouts()->create([
                    'accessory_id' => $accessory->id,
                    'created_at' => Carbon::now(),
                    'created_by' => 1,
                    'assigned_to' => $user->id,
                    'assigned_type' => User::class,
                ]);
            }
        });
    }

    public function checkedOutToAsset(Asset $asset = null)
    {
        return $this->afterCreating(function (Accessory $accessory) use ($asset) {
            $accessory->checkouts()->create([
                'accessory_id' => $accessory->id,
                'created_at' => Carbon::now(),
                'created_by' => 1,
                'assigned_to' => $asset->id ?? Asset::factory()->create()->id,
                'assigned_type' => Asset::class,
            ]);
        });
    }

    public function checkedOutToLocation(Location $location = null)
    {
        return $this->afterCreating(function (Accessory $accessory) use ($location) {
            $accessory->checkouts()->create([
                'accessory_id' => $accessory->id,
                'created_at' => Carbon::now(),
                'created_by' => 1,
                'assigned_to' => $location->id ?? Location::factory()->create()->id,
                'assigned_type' => Location::class,
            ]);
        });
    }
}
