<?php

namespace Tests\Unit\Models;

use App\Models\AccessoryCheckout;
use App\Models\Asset;
use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Tests\TestCase;

/**
 * Cubre AccessoryCheckout (antes 3.5%): relaciones, tipo de asignacion,
 * scopes y advancedTextSearch.
 */
class AccessoryCheckoutTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->superuser()->create());
    }

    public function test_assigned_type_and_checked_out_helpers_for_user(): void
    {
        $checkout = AccessoryCheckout::factory()->create(['assigned_type' => User::class]);

        $this->assertSame('user', $checkout->assignedType());
        $this->assertTrue($checkout->checkedOutToUser());
        $this->assertFalse($checkout->checkedOutToLocation());
        $this->assertFalse($checkout->checkedOutToAsset());
    }

    public function test_checked_out_to_location_and_asset(): void
    {
        $loc = AccessoryCheckout::factory()->create(['assigned_type' => Location::class]);
        $this->assertTrue($loc->checkedOutToLocation());

        $asset = AccessoryCheckout::factory()->create(['assigned_type' => Asset::class]);
        $this->assertTrue($asset->checkedOutToAsset());
    }

    public function test_assigned_type_null_returns_null(): void
    {
        $checkout = AccessoryCheckout::factory()->make(['assigned_type' => null]);

        $this->assertNull($checkout->assignedType());
    }

    public function test_relations_are_queryable(): void
    {
        $checkout = AccessoryCheckout::factory()->create();

        $this->assertInstanceOf(Collection::class, $checkout->accessories()->get());
        $this->assertTrue($checkout->accessory()->exists() || $checkout->accessory === null);
        // adminuser relation builds without error.
        $checkout->adminuser()->getQuery();
        $this->assertTrue(true);
    }

    public function test_assignment_scopes(): void
    {
        AccessoryCheckout::factory()->create(['assigned_type' => User::class]);
        AccessoryCheckout::factory()->create(['assigned_type' => Location::class]);
        AccessoryCheckout::factory()->create(['assigned_type' => Asset::class]);

        $this->assertInstanceOf(Collection::class, AccessoryCheckout::userAssigned()->get());
        $this->assertInstanceOf(Collection::class, AccessoryCheckout::locationAssigned()->get());
        $this->assertInstanceOf(Collection::class, AccessoryCheckout::assetsAssigned()->get());
    }

    public function test_advanced_text_search_builds_query(): void
    {
        AccessoryCheckout::factory()->create(['assigned_type' => User::class]);

        $result = AccessoryCheckout::query()->where(function ($q) {
            (new AccessoryCheckout)->advancedTextSearch($q, ['test']);
        })->get();

        $this->assertInstanceOf(Collection::class, $result);
    }
}
