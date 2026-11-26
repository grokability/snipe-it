<?php

namespace Tests\Feature\PredefinedKits\Api;

use App\Models\Accessory;
use App\Models\AssetModel;
use App\Models\Company;
use App\Models\Consumable;
use App\Models\License;
use App\Models\PredefinedKit;
use App\Models\User;
use Tests\TestCase;

class DetachKitItemsAuthorizationTest extends TestCase
{
    // -------------------------------------------------------------------------
    // Licenses
    // -------------------------------------------------------------------------

    public function test_detaching_kit_license_requires_view_permission_on_license(): void
    {
        $kit = PredefinedKit::factory()->create();
        $license = License::factory()->create();
        $kit->licenses()->attach($license->id, ['quantity' => 1]);

        $this->actingAsForApi(User::factory()->editPredefinedKits()->create())
            ->deleteJson(route('api.kits.licenses.destroy', ['kit_id' => $kit->id, 'license_id' => $license->id]))
            ->assertForbidden();

        $this->assertDatabaseHas('kits_licenses', [
            'kit_id' => $kit->id,
            'license_id' => $license->id,
        ]);
    }

    public function test_detaching_kit_license_succeeds_with_both_permissions(): void
    {
        $kit = PredefinedKit::factory()->create();
        $license = License::factory()->create();
        $kit->licenses()->attach($license->id, ['quantity' => 1]);

        $this->actingAsForApi(User::factory()->editPredefinedKits()->viewLicenses()->create())
            ->deleteJson(route('api.kits.licenses.destroy', ['kit_id' => $kit->id, 'license_id' => $license->id]))
            ->assertOk()
            ->assertStatusMessageIs('success');

        $this->assertDatabaseMissing('kits_licenses', [
            'kit_id' => $kit->id,
            'license_id' => $license->id,
        ]);
    }

    public function test_detaching_unattached_license_returns_404(): void
    {
        $kit = PredefinedKit::factory()->create();
        $license = License::factory()->create();

        $this->actingAsForApi(User::factory()->editPredefinedKits()->viewLicenses()->create())
            ->deleteJson(route('api.kits.licenses.destroy', ['kit_id' => $kit->id, 'license_id' => $license->id]))
            ->assertNotFound();
    }

    // -------------------------------------------------------------------------
    // Models
    // -------------------------------------------------------------------------

    public function test_detaching_kit_model_requires_view_permission_on_model(): void
    {
        $kit = PredefinedKit::factory()->create();
        $model = AssetModel::factory()->create();
        $kit->models()->attach($model->id, ['quantity' => 1]);

        $this->actingAsForApi(User::factory()->editPredefinedKits()->create())
            ->deleteJson(route('api.kits.models.destroy', ['kit_id' => $kit->id, 'model_id' => $model->id]))
            ->assertForbidden();

        $this->assertDatabaseHas('kits_models', [
            'kit_id' => $kit->id,
            'model_id' => $model->id,
        ]);
    }

    public function test_detaching_kit_model_succeeds_with_both_permissions(): void
    {
        $kit = PredefinedKit::factory()->create();
        $model = AssetModel::factory()->create();
        $kit->models()->attach($model->id, ['quantity' => 1]);

        $this->actingAsForApi(User::factory()->editPredefinedKits()->viewAssetModels()->create())
            ->deleteJson(route('api.kits.models.destroy', ['kit_id' => $kit->id, 'model_id' => $model->id]))
            ->assertOk()
            ->assertStatusMessageIs('success');

        $this->assertDatabaseMissing('kits_models', [
            'kit_id' => $kit->id,
            'model_id' => $model->id,
        ]);
    }

    // -------------------------------------------------------------------------
    // Consumables
    // -------------------------------------------------------------------------

    public function test_detaching_kit_consumable_requires_view_permission_on_consumable(): void
    {
        $kit = PredefinedKit::factory()->create();
        $consumable = Consumable::factory()->create();
        $kit->consumables()->attach($consumable->id, ['quantity' => 1]);

        $this->actingAsForApi(User::factory()->editPredefinedKits()->create())
            ->deleteJson(route('api.kits.consumables.destroy', ['kit_id' => $kit->id, 'consumable_id' => $consumable->id]))
            ->assertForbidden();

        $this->assertDatabaseHas('kits_consumables', [
            'kit_id' => $kit->id,
            'consumable_id' => $consumable->id,
        ]);
    }

    public function test_detaching_kit_consumable_succeeds_with_both_permissions(): void
    {
        $kit = PredefinedKit::factory()->create();
        $consumable = Consumable::factory()->create();
        $kit->consumables()->attach($consumable->id, ['quantity' => 1]);

        $this->actingAsForApi(User::factory()->editPredefinedKits()->viewConsumables()->create())
            ->deleteJson(route('api.kits.consumables.destroy', ['kit_id' => $kit->id, 'consumable_id' => $consumable->id]))
            ->assertOk()
            ->assertStatusMessageIs('success');

        $this->assertDatabaseMissing('kits_consumables', [
            'kit_id' => $kit->id,
            'consumable_id' => $consumable->id,
        ]);
    }

    // -------------------------------------------------------------------------
    // Accessories
    // -------------------------------------------------------------------------

    public function test_detaching_kit_accessory_requires_view_permission_on_accessory(): void
    {
        $kit = PredefinedKit::factory()->create();
        $accessory = Accessory::factory()->create();
        $kit->accessories()->attach($accessory->id, ['quantity' => 1]);

        $this->actingAsForApi(User::factory()->editPredefinedKits()->create())
            ->deleteJson(route('api.kits.accessories.destroy', ['kit_id' => $kit->id, 'accessory_id' => $accessory->id]))
            ->assertForbidden();

        $this->assertDatabaseHas('kits_accessories', [
            'kit_id' => $kit->id,
            'accessory_id' => $accessory->id,
        ]);
    }

    public function test_detaching_kit_accessory_succeeds_with_both_permissions(): void
    {
        $kit = PredefinedKit::factory()->create();
        $accessory = Accessory::factory()->create();
        $kit->accessories()->attach($accessory->id, ['quantity' => 1]);

        $this->actingAsForApi(User::factory()->editPredefinedKits()->viewAccessories()->create())
            ->deleteJson(route('api.kits.accessories.destroy', ['kit_id' => $kit->id, 'accessory_id' => $accessory->id]))
            ->assertOk()
            ->assertStatusMessageIs('success');

        $this->assertDatabaseMissing('kits_accessories', [
            'kit_id' => $kit->id,
            'accessory_id' => $accessory->id,
        ]);
    }

    // -------------------------------------------------------------------------
    // FMCS cross-company
    // -------------------------------------------------------------------------

    public function test_detaching_kit_license_scoped_out_under_fmcs_returns_404(): void
    {
        // Caller holds kits.edit and licenses.view but belongs only to
        // Company A. The attached license belongs to Company B. Under
        // strict FMCS the child model's CompanyableScope filters it out
        // of the kit relationship query, so the detach cannot resolve.
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();
        $kit = PredefinedKit::factory()->create();
        $licenseB = License::factory()->for($companyB, 'company')->create();
        $kit->licenses()->attach($licenseB->id, ['quantity' => 1]);

        $actor = User::factory()->editPredefinedKits()->viewLicenses()->forCompany($companyA->id)->create();

        $this->actingAsForApi($actor)
            ->deleteJson(route('api.kits.licenses.destroy', ['kit_id' => $kit->id, 'license_id' => $licenseB->id]))
            ->assertNotFound();

        $this->assertDatabaseHas('kits_licenses', [
            'kit_id' => $kit->id,
            'license_id' => $licenseB->id,
        ]);
    }
}
