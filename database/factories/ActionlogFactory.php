<?php

namespace Database\Factories;

use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\License;
use App\Models\LicenseSeat;
use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ActionlogFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Actionlog::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'item_id' => Asset::factory(),
            'item_type' => Asset::class,
            'created_by' => User::factory()->superuser(),
            'action_type' => 'uploaded',
        ];
    }

    public function assetCheckoutToUser()
    {
        return $this->state(function () {
            $target = User::inRandomOrder()->first();
            $asset = Asset::inRandomOrder()->RTD()->first();

            $asset->update(
                    [
                        'assigned_to' => $target->id,
                        'assigned_type' => User::class,
                        'location_id' => $target->location_id,
                    ]
                );
    
            return [
                'created_at'  => $this->faker->dateTimeBetween('-1 years', 'now', date_default_timezone_get()),
                'action_type' => 'checkout',
                'item_id' => $asset->id,
                'item_type'  => Asset::class,
                'target_id' => $target->id,
                'target_type' => User::class,
            ];
        });
    }

    public function assetCheckoutToLocation()
    {
        return $this->state(function () {
            $target = Location::inRandomOrder()->first();
            $asset = Asset::inRandomOrder()->RTD()->first();

            $asset->update(
                    [
                        'assigned_to' => $target->id,
                        'assigned_type' => Location::class,
                        'location_id' => $target->id,
                    ]
                );

            return [
                'created_at'  => $this->faker->dateTimeBetween('-1 years', 'now', date_default_timezone_get()),
                'action_type' => 'checkout',
                'item_id' => $asset->id,
                'item_type'  => Asset::class,
                'target_id' => $target->id,
                'target_type' => Location::class,
            ];
        });
    }

    /**
     * This sets up an ActionLog representing a manually added note tied to an Asset,
     * with an optional User as the creator. If no User is provided, one is generated.
     *
     * @param User|null $user Optional user to associate as the creator of the note.
     * @return \Illuminate\Database\Eloquent\Factories\Factory<ActionLog>
     */
    public function assetNote(?User $user=null)
    {
        return $this
            ->state(function () use ($user) {
                return [
                    'action_type' => 'note added',
                    'item_type' => Asset::class,
                    'target_type' => 'asset',
                    'note' => 'Factory-generated manual note',
                    'created_by' => $user?->id ?? User::factory(),
                ];
            })
            ->for($user ?? User::factory(), 'user');
    }

    public function licenseCheckoutToUser()
    {
        return $this->state(function () {
            $target = User::inRandomOrder()->first();
            $licenseSeat = LicenseSeat::whereNull('assigned_to')->inRandomOrder()->first();

            $licenseSeat->update([
                'assigned_to' => $target->id,
                'created_by' => 1, // not ideal but works
            ]);

            return [
                'created_at'  => $this->faker->dateTimeBetween('-1 years', 'now', date_default_timezone_get()),
                'action_type' => 'checkout',
                'item_id' => $licenseSeat->license->id,
                'item_type'  => License::class,
                'target_id' => $target->id,
                'target_type' => User::class,
            ];
        });
    }

    public function filesUploaded()
    {
        return $this->state(function () {

            return [
                'created_at'  => $this->faker->dateTimeBetween('-1 years', 'now', date_default_timezone_get()),
                'action_type' => 'uploaded',
                'item_type'  => User::class,
                'filename'  => $this->faker->unixTime('now'),
            ];
        });
    }

    public function acceptedSignature()
    {
        return $this->state(function () {

            $asset = Asset::factory()->create();

            return [
                'created_at'  => $this->faker->dateTimeBetween('-1 years', 'now', date_default_timezone_get()),
                'action_type' => 'accepted',
                'item_id' => $asset->id,
                'item_type'  => Asset::class,
                'target_type'  => User::class,
                'accept_signature'  => $this->faker->unixTime('now'),
            ];
        });
    }

    public function acceptedEula()
    {
        return $this->state(function () {

            $asset = Asset::factory()->create();

            return [
                'created_at'  => $this->faker->dateTimeBetween('-1 years', 'now', date_default_timezone_get()),
                'action_type' => 'accepted',
                'item_id' => $asset->id,
                'item_type'  => Asset::class,
                'target_type'  => User::class,
                'filename'  => $this->faker->unixTime('now'),
            ];
        });
    }

    public function userUpdated()
    {
        return $this->state(function () {

            return [
                'created_at'  => $this->faker->dateTimeBetween('-1 years', 'now', date_default_timezone_get()),
                'action_type' => 'update',
                'target_type'  => User::class,
                'item_type'  => User::class,
            ];
        });
    }
}
