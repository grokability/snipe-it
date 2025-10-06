<?php

namespace Database\Factories;

use App\Models\Accessory;
use App\Models\Asset;
use App\Models\CheckoutAcceptance;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CheckoutAcceptanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'checkoutable_type' => Asset::class,
            'checkoutable_id' => Asset::factory(),
            'assigned_to_id' => User::factory(),
        ];
    }
    protected static bool $skipActionLog = false;

    public function withoutActionLog(): static
    {
        // turn off for this create() call
        static::$skipActionLog = true;

        // ensure it turns back on AFTER creating
        return $this->afterCreating(function () {
            static::$skipActionLog = false;
        });
    }

    public function configure(): static
    {
        return $this->afterCreating(function (CheckoutAcceptance $acceptance) {
            if (static::$skipActionLog) {
                return; // short-circuit
            }
            if ($acceptance->checkoutable instanceof Asset) {
                $this->createdAssociatedActionLogEntry($acceptance);
            }

            if ($acceptance->checkoutable instanceof Asset && $acceptance->assignedTo instanceof User) {
                $acceptance->checkoutable->update([
                    'assigned_to'  => $acceptance->assigned_to_id,
                    'assigned_type'=> get_class($acceptance->assignedTo),
                ]);
            }
        });
    }


    public function forAccessory()
    {
        return $this->state([
            'checkoutable_type' => Accessory::class,
            'checkoutable_id' => Accessory::factory(),
        ]);
    }

    public function pending()
    {
        return $this->state([
            'accepted_at' => null,
            'declined_at' => null,
        ]);
    }

    public function accepted()
    {
        return $this->state([
            'accepted_at' => now()->subDay(),
            'declined_at' => null,
        ]);
    }

    public function withoutAlerting()
    {
        return $this->state(function () {
            return [
                'alert_on_response_id' => null,
            ];
        });
    }

    public function withAlertingTo(User $user)
    {
        return $this->state(function () use ($user) {
            return [
                'alert_on_response_id' => $user->id,
            ];
        });
    }

    private function createdAssociatedActionLogEntry(CheckoutAcceptance $acceptance): void
    {
        $acceptance->checkoutable->assetlog()->create([
            'action_type' => 'checkout',
            'target_id' => $acceptance->assigned_to_id,
            'target_type' => get_class($acceptance->assignedTo),
            'item_id' => $acceptance->checkoutable_id,
            'item_type' => $acceptance->checkoutable_type,
        ]);
    }
}
