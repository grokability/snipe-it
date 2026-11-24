<?php

namespace Tests\Unit\Contracts;

use App\Models\Accessory;
use App\Models\AccessoryCheckout;
use App\Models\Asset;
use App\Models\Component;
use App\Models\ComponentAssignment;
use App\Models\Consumable;
use App\Models\ConsumableAssignment;
use App\Models\Contracts\Checkoutable;
use App\Models\License;
use App\Models\LicenseSeat;
use App\Models\Location;
use App\Models\User;
use PHPUnit\Framework\TestCase;

class CheckoutableContractTest extends TestCase
{
    public function test_asset_leaf_can_be_checked_out_to_user_asset_or_location(): void
    {
        // Asset is both parent and leaf.
        $this->assertSame(
            [User::class, Asset::class, Location::class],
            Asset::allowedCheckoutTargetTypes(),
        );
    }

    public function test_accessory_checkout_leaf_can_be_checked_out_to_user_asset_or_location(): void
    {
        $this->assertSame(
            [User::class, Asset::class, Location::class],
            AccessoryCheckout::allowedCheckoutTargetTypes(),
        );
    }

    public function test_consumable_assignment_leaf_can_only_be_checked_out_to_user(): void
    {
        $this->assertSame(
            [User::class],
            ConsumableAssignment::allowedCheckoutTargetTypes(),
        );
    }

    public function test_component_assignment_leaf_can_only_be_checked_out_to_asset(): void
    {
        $this->assertSame(
            [Asset::class],
            ComponentAssignment::allowedCheckoutTargetTypes(),
        );
    }

    public function test_license_seat_leaf_can_be_checked_out_to_user_or_asset(): void
    {
        $this->assertSame(
            [User::class, Asset::class],
            LicenseSeat::allowedCheckoutTargetTypes(),
        );
    }

    public function test_accessory_parent_passthroughs_to_its_leaf(): void
    {
        $this->assertSame(
            AccessoryCheckout::allowedCheckoutTargetTypes(),
            Accessory::allowedCheckoutTargetTypes(),
        );
    }

    public function test_consumable_parent_passthroughs_to_its_leaf(): void
    {
        $this->assertSame(
            ConsumableAssignment::allowedCheckoutTargetTypes(),
            Consumable::allowedCheckoutTargetTypes(),
        );
    }

    public function test_component_parent_passthroughs_to_its_leaf(): void
    {
        $this->assertSame(
            ComponentAssignment::allowedCheckoutTargetTypes(),
            Component::allowedCheckoutTargetTypes(),
        );
    }

    public function test_license_parent_passthroughs_to_its_leaf(): void
    {
        $this->assertSame(
            LicenseSeat::allowedCheckoutTargetTypes(),
            License::allowedCheckoutTargetTypes(),
        );
    }

    public function test_every_leaf_and_parent_implements_the_contract(): void
    {
        $classes = [
            // Leaves
            Asset::class,
            AccessoryCheckout::class,
            ComponentAssignment::class,
            ConsumableAssignment::class,
            LicenseSeat::class,
            // Parents
            Accessory::class,
            Component::class,
            Consumable::class,
            License::class,
        ];

        foreach ($classes as $class) {
            $this->assertContains(
                Checkoutable::class,
                class_implements($class),
                $class . ' must implement the Checkoutable contract',
            );
        }
    }
}
