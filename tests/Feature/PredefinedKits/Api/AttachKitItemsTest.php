<?php

namespace Tests\Feature\PredefinedKits\Api;

use App\Models\Accessory;
use App\Models\Consumable;
use App\Models\License;
use App\Models\PredefinedKit;
use App\Models\User;
use Tests\TestCase;

class AttachKitItemsTest extends TestCase
{
    // -------------------------------------------------------------------------
    // Licenses
    // -------------------------------------------------------------------------

    public function test_attaching_license_requires_kit_edit_permission()
    {
        $kit = PredefinedKit::factory()->create();
        $license = License::factory()->create();

        $this->actingAsForApi(User::factory()->viewLicenses()->create())
            ->postJson(route('api.kits.licenses.store', $kit), ['license' => $license->id, 'quantity' => 1])
            ->assertForbidden();

        $this->assertDatabaseMissing('kits_licenses', ['kit_id' => $kit->id, 'license_id' => $license->id]);
    }

    public function test_attaching_license_requires_view_permission_on_license()
    {
        $kit = PredefinedKit::factory()->create();
        $license = License::factory()->create();

        $this->actingAsForApi(User::factory()->editPredefinedKits()->create())
            ->postJson(route('api.kits.licenses.store', $kit), ['license' => $license->id, 'quantity' => 1])
            ->assertForbidden();

        $this->assertDatabaseMissing('kits_licenses', ['kit_id' => $kit->id, 'license_id' => $license->id]);
    }

    public function test_can_attach_license_with_both_permissions()
    {
        $kit = PredefinedKit::factory()->create();
        $license = License::factory()->create();

        $this->actingAsForApi(User::factory()->editPredefinedKits()->viewLicenses()->create())
            ->postJson(route('api.kits.licenses.store', $kit), ['license' => $license->id, 'quantity' => 1])
            ->assertOk()
            ->assertStatusMessageIs('success');

        $this->assertDatabaseHas('kits_licenses', ['kit_id' => $kit->id, 'license_id' => $license->id]);
    }

    // -------------------------------------------------------------------------
    // Consumables
    // -------------------------------------------------------------------------

    public function test_attaching_consumable_requires_kit_edit_permission()
    {
        $kit = PredefinedKit::factory()->create();
        $consumable = Consumable::factory()->create();

        $this->actingAsForApi(User::factory()->viewConsumables()->create())
            ->postJson(route('api.kits.consumables.store', $kit), ['consumable' => $consumable->id, 'quantity' => 1])
            ->assertForbidden();

        $this->assertDatabaseMissing('kits_consumables', ['kit_id' => $kit->id, 'consumable_id' => $consumable->id]);
    }

    public function test_attaching_consumable_requires_view_permission_on_consumable()
    {
        $kit = PredefinedKit::factory()->create();
        $consumable = Consumable::factory()->create();

        $this->actingAsForApi(User::factory()->editPredefinedKits()->create())
            ->postJson(route('api.kits.consumables.store', $kit), ['consumable' => $consumable->id, 'quantity' => 1])
            ->assertForbidden();

        $this->assertDatabaseMissing('kits_consumables', ['kit_id' => $kit->id, 'consumable_id' => $consumable->id]);
    }

    public function test_can_attach_consumable_with_both_permissions()
    {
        $kit = PredefinedKit::factory()->create();
        $consumable = Consumable::factory()->create();

        $this->actingAsForApi(User::factory()->editPredefinedKits()->viewConsumables()->create())
            ->postJson(route('api.kits.consumables.store', $kit), ['consumable' => $consumable->id, 'quantity' => 1])
            ->assertOk()
            ->assertStatusMessageIs('success');

        $this->assertDatabaseHas('kits_consumables', ['kit_id' => $kit->id, 'consumable_id' => $consumable->id]);
    }

    // -------------------------------------------------------------------------
    // Accessories
    // -------------------------------------------------------------------------

    public function test_attaching_accessory_requires_kit_edit_permission()
    {
        $kit = PredefinedKit::factory()->create();
        $accessory = Accessory::factory()->create();

        $this->actingAsForApi(User::factory()->viewAccessories()->create())
            ->postJson(route('api.kits.accessories.store', $kit), ['accessory' => $accessory->id, 'quantity' => 1])
            ->assertForbidden();

        $this->assertDatabaseMissing('kits_accessories', ['kit_id' => $kit->id, 'accessory_id' => $accessory->id]);
    }

    public function test_attaching_accessory_requires_view_permission_on_accessory()
    {
        $kit = PredefinedKit::factory()->create();
        $accessory = Accessory::factory()->create();

        $this->actingAsForApi(User::factory()->editPredefinedKits()->create())
            ->postJson(route('api.kits.accessories.store', $kit), ['accessory' => $accessory->id, 'quantity' => 1])
            ->assertForbidden();

        $this->assertDatabaseMissing('kits_accessories', ['kit_id' => $kit->id, 'accessory_id' => $accessory->id]);
    }

    public function test_can_attach_accessory_with_both_permissions()
    {
        $kit = PredefinedKit::factory()->create();
        $accessory = Accessory::factory()->create();

        $this->actingAsForApi(User::factory()->editPredefinedKits()->viewAccessories()->create())
            ->postJson(route('api.kits.accessories.store', $kit), ['accessory' => $accessory->id, 'quantity' => 1])
            ->assertOk()
            ->assertStatusMessageIs('success');

        $this->assertDatabaseHas('kits_accessories', ['kit_id' => $kit->id, 'accessory_id' => $accessory->id]);
    }
}
