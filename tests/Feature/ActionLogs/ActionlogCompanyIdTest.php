<?php

namespace Tests\Feature\ActionLogs;

use App\Models\Accessory;
use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Company;
use App\Models\Component;
use App\Models\Consumable;
use App\Models\License;
use App\Models\LicenseSeat;
use App\Models\Location;
use App\Models\Statuslabel;
use App\Models\User;
use Tests\TestCase;

/**
 * Confirms that action_logs.company_id is correctly stamped for every
 * logged event so that FMCS scoping works correctly.
 *
 * Each test creates an item belonging to a specific company and triggers the
 * relevant action, then asserts that the resulting action log row carries the
 * same company_id as the item.
 */
class ActionlogCompanyIdTest extends TestCase
{
    // -------------------------------------------------------------------------
    // Asset events
    // -------------------------------------------------------------------------

    public function test_asset_audit_log_stores_the_assets_company_id(): void
    {
        $company = Company::factory()->create();
        $asset = Asset::factory()->create(['company_id' => $company->id]);
        $admin = User::factory()->superuser()->create();

        $this->actingAsForApi($admin)
            ->postJson(route('api.asset.audit', $asset), ['note' => 'audit test'])
            ->assertStatusMessageIs('success');

        $this->assertDatabaseHas('action_logs', [
            'item_type' => Asset::class,
            'item_id' => $asset->id,
            'action_type' => 'audit',
            'company_id' => $company->id,
        ]);
    }

    public function test_asset_checkout_to_user_log_stores_the_assets_company_id(): void
    {
        $company = Company::factory()->create();
        $asset = Asset::factory()->create(['company_id' => $company->id]);
        $user = User::factory()->create();
        $admin = User::factory()->superuser()->create();

        $this->actingAsForApi($admin)
            ->postJson(route('api.asset.checkout', $asset), [
                'checkout_to_type' => 'user',
                'assigned_user' => $user->id,
                'status_id' => $asset->status_id,
            ])
            ->assertStatusMessageIs('success');

        $this->assertDatabaseHas('action_logs', [
            'item_type' => Asset::class,
            'item_id' => $asset->id,
            'action_type' => 'checkout',
            'company_id' => $company->id,
        ]);
    }

    public function test_asset_checkout_to_location_log_stores_the_assets_company_id(): void
    {
        $company = Company::factory()->create();
        $asset = Asset::factory()->create(['company_id' => $company->id]);
        $location = Location::factory()->create();
        $admin = User::factory()->superuser()->create();

        $this->actingAsForApi($admin)
            ->postJson(route('api.asset.checkout', $asset), [
                'checkout_to_type' => 'location',
                'assigned_location' => $location->id,
                'status_id' => $asset->status_id,
            ])
            ->assertStatusMessageIs('success');

        $this->assertDatabaseHas('action_logs', [
            'item_type' => Asset::class,
            'item_id' => $asset->id,
            'action_type' => 'checkout',
            'company_id' => $company->id,
        ]);
    }

    public function test_asset_checkin_log_stores_the_assets_company_id(): void
    {
        $company = Company::factory()->create();
        $asset = Asset::factory()->assignedToUser()->create(['company_id' => $company->id]);
        $admin = User::factory()->superuser()->create();

        $this->actingAsForApi($admin)
            ->postJson(route('api.asset.checkin', $asset))
            ->assertStatusMessageIs('success');

        $this->assertDatabaseHas('action_logs', [
            'item_type' => Asset::class,
            'item_id' => $asset->id,
            'action_type' => 'checkin from',
            'company_id' => $company->id,
        ]);
    }

    public function test_asset_create_log_stores_the_assets_company_id(): void
    {
        $company = Company::factory()->create();
        $admin = User::factory()->superuser()->create();
        $model = AssetModel::factory()->create();
        $status = Statuslabel::factory()->readyToDeploy()->create();
        $tag = 'COMPANY-ID-TEST-'.uniqid();

        $this->actingAsForApi($admin)
            ->postJson(route('api.assets.store'), [
                'asset_tag' => $tag,
                'model_id' => $model->id,
                'status_id' => $status->id,
                'company_id' => $company->id,
            ])
            ->assertStatusMessageIs('success');

        $asset = Asset::where('asset_tag', $tag)->firstOrFail();

        $this->assertDatabaseHas('action_logs', [
            'item_type' => Asset::class,
            'item_id' => $asset->id,
            'action_type' => 'create',
            'company_id' => $company->id,
        ]);
    }

    // -------------------------------------------------------------------------
    // Accessory events
    // -------------------------------------------------------------------------

    public function test_accessory_checkout_log_stores_the_accessorys_company_id(): void
    {
        $company = Company::factory()->create();
        $accessory = Accessory::factory()->create(['company_id' => $company->id]);
        $user = User::factory()->create();
        $admin = User::factory()->superuser()->create();

        $this->actingAsForApi($admin)
            ->postJson(route('api.accessories.checkout', $accessory), [
                'assigned_user' => $user->id,
                'checkout_to_type' => 'user',
            ])
            ->assertStatusMessageIs('success');

        $this->assertDatabaseHas('action_logs', [
            'item_type' => Accessory::class,
            'item_id' => $accessory->id,
            'action_type' => 'checkout',
            'company_id' => $company->id,
        ]);
    }

    public function test_accessory_checkin_log_stores_the_accessorys_company_id(): void
    {
        $company = Company::factory()->create();
        $accessory = Accessory::factory()->checkedOutToUser()->create(['company_id' => $company->id]);
        $admin = User::factory()->superuser()->create();

        $checkoutRecord = $accessory->checkouts->first();

        $this->actingAsForApi($admin)
            ->postJson(route('api.accessories.checkin', $checkoutRecord))
            ->assertStatusMessageIs('success');

        $this->assertDatabaseHas('action_logs', [
            'item_type' => Accessory::class,
            'item_id' => $accessory->id,
            'action_type' => 'checkin from',
            'company_id' => $company->id,
        ]);
    }

    // -------------------------------------------------------------------------
    // Consumable events
    // -------------------------------------------------------------------------

    public function test_consumable_checkout_log_stores_the_consumables_company_id(): void
    {
        $company = Company::factory()->create();
        $consumable = Consumable::factory()->create(['company_id' => $company->id]);
        $user = User::factory()->create();
        $admin = User::factory()->superuser()->create();

        $this->actingAsForApi($admin)
            ->postJson(route('api.consumables.checkout', $consumable), [
                'assigned_to' => $user->id,
            ])
            ->assertStatusMessageIs('success');

        $this->assertDatabaseHas('action_logs', [
            'item_type' => Consumable::class,
            'item_id' => $consumable->id,
            'action_type' => 'checkout',
            'company_id' => $company->id,
        ]);
    }

    // -------------------------------------------------------------------------
    // Component events
    // -------------------------------------------------------------------------

    public function test_component_checkout_log_stores_the_components_company_id(): void
    {
        $company = Company::factory()->create();
        $component = Component::factory()->create(['company_id' => $company->id]);
        $asset = Asset::factory()->create();
        $admin = User::factory()->superuser()->create();

        $this->actingAsForApi($admin)
            ->postJson(route('api.components.checkout', $component->id), [
                'assigned_to' => $asset->id,
                'assigned_qty' => 1,
            ])
            ->assertStatusMessageIs('success');

        $this->assertDatabaseHas('action_logs', [
            'item_type' => Component::class,
            'item_id' => $component->id,
            'action_type' => 'checkout',
            'company_id' => $company->id,
        ]);
    }

    public function test_component_checkin_log_stores_the_components_company_id(): void
    {
        $company = Company::factory()->create();
        $component = Component::factory()->create(['company_id' => $company->id]);
        $asset = Asset::factory()->create();
        $admin = User::factory()->superuser()->create();

        // Check out first
        $this->actingAsForApi($admin)
            ->postJson(route('api.components.checkout', $component->id), [
                'assigned_to' => $asset->id,
                'assigned_qty' => 1,
            ])
            ->assertStatusMessageIs('success');

        $pivotId = $component->assets()->first()->pivot->id;

        $this->actingAsForApi($admin)
            ->postJson(route('api.components.checkin', $pivotId), [
                'checkin_qty' => 1,
            ])
            ->assertStatusMessageIs('success');

        $this->assertDatabaseHas('action_logs', [
            'item_type' => Component::class,
            'item_id' => $component->id,
            'action_type' => 'checkin from',
            'company_id' => $company->id,
        ]);
    }

    // -------------------------------------------------------------------------
    // License events
    // -------------------------------------------------------------------------

    public function test_license_checkout_log_stores_the_licenses_company_id(): void
    {
        $company = Company::factory()->create();
        $license = License::factory()->create(['company_id' => $company->id]);
        $seat = $license->freeSeats()->first();
        $user = User::factory()->create();
        $admin = User::factory()->superuser()->create();

        $this->actingAsForApi($admin)
            ->patchJson(route('api.licenses.seats.update', [$license->id, $seat->id]), [
                'assigned_to' => $user->id,
            ])
            ->assertStatusMessageIs('success');

        // The log is stored against the License (item_type), not the LicenseSeat
        $this->assertDatabaseHas('action_logs', [
            'item_type' => License::class,
            'item_id' => $license->id,
            'action_type' => 'checkout',
            'company_id' => $company->id,
        ]);
    }

    public function test_license_checkin_log_stores_the_licenses_company_id(): void
    {
        $company = Company::factory()->create();
        $license = License::factory()->create(['company_id' => $company->id]);
        $seat = $license->freeSeats()->first();
        $user = User::factory()->create();
        $admin = User::factory()->superuser()->create();

        // Check out first
        $seat->assigned_to = $user->id;
        $seat->save();

        $this->actingAsForApi($admin)
            ->patchJson(route('api.licenses.seats.update', [$license->id, $seat->id]), [
                'assigned_to' => null,
            ])
            ->assertStatusMessageIs('success');

        $this->assertDatabaseHas('action_logs', [
            'item_type' => License::class,
            'item_id' => $license->id,
            'action_type' => 'checkin from',
            'company_id' => $company->id,
        ]);
    }

    // -------------------------------------------------------------------------
    // Null company_id — items without a company should log null, not an error
    // -------------------------------------------------------------------------

    public function test_asset_audit_log_company_id_is_null_when_asset_has_no_company(): void
    {
        $asset = Asset::factory()->create(['company_id' => null]);
        $admin = User::factory()->superuser()->create();

        $this->actingAsForApi($admin)
            ->postJson(route('api.asset.audit', $asset), ['note' => 'no company'])
            ->assertStatusMessageIs('success');

        $this->assertDatabaseHas('action_logs', [
            'item_type' => Asset::class,
            'item_id' => $asset->id,
            'action_type' => 'audit',
            'company_id' => null,
        ]);
    }

    public function test_asset_checkout_log_company_id_is_null_when_asset_has_no_company(): void
    {
        $asset = Asset::factory()->create(['company_id' => null]);
        $user = User::factory()->create();
        $admin = User::factory()->superuser()->create();

        $this->actingAsForApi($admin)
            ->postJson(route('api.asset.checkout', $asset), [
                'checkout_to_type' => 'user',
                'assigned_user' => $user->id,
                'status_id' => $asset->status_id,
            ])
            ->assertStatusMessageIs('success');

        $this->assertDatabaseHas('action_logs', [
            'item_type' => Asset::class,
            'item_id' => $asset->id,
            'action_type' => 'checkout',
            'company_id' => null,
        ]);
    }
}
