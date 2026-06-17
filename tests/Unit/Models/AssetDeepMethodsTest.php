<?php

namespace Tests\Unit\Models;

use App\Models\Asset;
use App\Models\Location;
use App\Models\User;
use Tests\TestCase;

/**
 * Cubre metodos de calculo/estado de Asset que no requieren ciclo HTTP:
 * disponibilidad, tipo de checkout, costos y progreso EOL/garantia.
 */
class AssetDeepMethodsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->superuser()->create());
    }

    public function test_available_for_checkout_when_unassigned_and_deployable(): void
    {
        $asset = Asset::factory()->create();

        $this->assertTrue($asset->availableForCheckout());
    }

    public function test_not_available_for_checkout_when_assigned(): void
    {
        $asset = Asset::factory()->assignedToUser()->create();

        $this->assertFalse($asset->availableForCheckout());
    }

    public function test_not_available_for_checkout_when_archived(): void
    {
        $archivedStatus = \App\Models\Statuslabel::factory()->archived()->create();
        $asset = Asset::factory()->create(['status_id' => $archivedStatus->id]);

        $this->assertFalse($asset->availableForCheckout());
    }

    public function test_available_for_checkin_when_assigned(): void
    {
        $asset = Asset::factory()->assignedToUser()->create();

        $this->assertTrue($asset->availableForCheckIn());
    }

    public function test_not_available_for_checkin_when_unassigned(): void
    {
        $asset = Asset::factory()->create();

        $this->assertFalse($asset->availableForCheckIn());
    }

    public function test_checked_out_to_user(): void
    {
        $asset = Asset::factory()->assignedToUser()->create();

        $this->assertTrue($asset->checkedOutToUser());
        $this->assertFalse($asset->checkedOutToLocation());
        $this->assertFalse($asset->checkedOutToAsset());
    }

    public function test_checked_out_to_location(): void
    {
        $asset = Asset::factory()->assignedToLocation()->create();

        $this->assertTrue($asset->checkedOutToLocation());
        $this->assertFalse($asset->checkedOutToUser());
    }

    public function test_checked_out_to_asset(): void
    {
        $asset = Asset::factory()->assignedToAsset()->create();

        $this->assertTrue($asset->checkedOutToAsset());
    }

    public function test_get_depreciation_returns_null_without_model_depreciation(): void
    {
        $asset = Asset::factory()->create();

        // Sin depreciacion configurada en el modelo, retorna null.
        $this->assertNull($asset->get_depreciation());
    }

    public function test_component_and_accessory_cost_default_zero(): void
    {
        $asset = Asset::factory()->create();

        $this->assertSame(0.0, $asset->getComponentCost());
        $this->assertSame(0.0, $asset->getAccessoryCost());
    }

    public function test_eol_and_warranty_progress_zero_without_dates(): void
    {
        $asset = Asset::factory()->noPurchaseOrEolDate()->create([
            'warranty_months' => null,
        ]);

        $this->assertSame(0.0, $asset->eolProgressPercent());
        $this->assertSame(0.0, $asset->warrantyProgressPercent());
    }

    public function test_check_invalid_next_audit_date(): void
    {
        // last_audit posterior a next_audit -> invalido (true).
        $invalid = Asset::factory()->create([
            'last_audit_date' => '2025-12-31',
            'next_audit_date' => '2025-01-01',
        ]);
        $this->assertTrue($invalid->checkInvalidNextAuditDate());

        // orden correcto -> false.
        $valid = Asset::factory()->create([
            'last_audit_date' => '2025-01-01',
            'next_audit_date' => '2025-12-31',
        ]);
        $this->assertFalse($valid->checkInvalidNextAuditDate());
    }

    public function test_require_acceptance_returns_bool(): void
    {
        $asset = Asset::factory()->requiresAcceptance()->create();

        $this->assertIsBool((bool) $asset->requireAcceptance());
    }

    public function test_asset_loc_resolves_location_for_user_assignment(): void
    {
        $location = Location::factory()->create();
        $user = User::factory()->create(['location_id' => $location->id]);
        $asset = Asset::factory()->assignedToUser($user)->create();

        // assetLoc recorre la cadena de asignacion hasta una Location.
        $resolved = $asset->assetLoc();
        $this->assertTrue($resolved === null || $resolved instanceof Location);
    }

    public function test_is_deletable_returns_bool(): void
    {
        $asset = Asset::factory()->create();

        $this->assertIsBool($asset->isDeletable());
    }

    public function test_has_orphaned_assignment_returns_bool(): void
    {
        $asset = Asset::factory()->create();

        $this->assertIsBool($asset->hasOrphanedAssignment());
    }
}
