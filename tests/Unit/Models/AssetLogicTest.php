<?php

namespace Tests\Unit\Models;

use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Statuslabel;
use App\Models\User;
use Tests\TestCase;

class AssetLogicTest extends TestCase
{
    private function deployableStatus(): Statuslabel
    {
        return Statuslabel::factory()->create(['deployable' => 1, 'archived' => 0, 'pending' => 0]);
    }

    private function archivedStatus(): Statuslabel
    {
        return Statuslabel::factory()->create(['deployable' => 0, 'archived' => 1, 'pending' => 0]);
    }

    public function test_available_for_checkout_true_when_unassigned_and_deployable(): void
    {
        $asset = Asset::factory()->create(['assigned_to' => null, 'status_id' => $this->deployableStatus()->id]);

        $this->assertTrue($asset->availableForCheckout());
    }

    public function test_available_for_checkout_false_when_assigned(): void
    {
        $user = User::factory()->create();
        $asset = Asset::factory()->create([
            'assigned_to' => $user->id,
            'assigned_type' => User::class,
            'status_id' => $this->deployableStatus()->id,
        ]);

        $this->assertFalse($asset->availableForCheckout());
    }

    public function test_available_for_checkout_false_when_archived(): void
    {
        $asset = Asset::factory()->create(['assigned_to' => null, 'status_id' => $this->archivedStatus()->id]);

        $this->assertFalse($asset->availableForCheckout());
    }

    public function test_available_for_checkin_true_when_assigned_and_deployable(): void
    {
        $user = User::factory()->create();
        $asset = Asset::factory()->create([
            'assigned_to' => $user->id,
            'assigned_type' => User::class,
            'status_id' => $this->deployableStatus()->id,
        ]);

        $this->assertTrue($asset->availableForCheckIn());
    }

    public function test_available_for_checkin_false_when_unassigned(): void
    {
        $asset = Asset::factory()->create(['assigned_to' => null, 'status_id' => $this->deployableStatus()->id]);

        $this->assertFalse($asset->availableForCheckIn());
    }

    public function test_checked_out_to_user_helpers(): void
    {
        $user = User::factory()->create();
        $asset = Asset::factory()->create([
            'assigned_to' => $user->id,
            'assigned_type' => User::class,
        ]);

        $this->assertTrue($asset->checkedOutToUser());
        $this->assertFalse($asset->checkedOutToLocation());
        $this->assertFalse($asset->checkedOutToAsset());
    }

    public function test_is_deletable_true_for_superuser(): void
    {
        $this->actingAs(User::factory()->superuser()->create());
        $asset = Asset::factory()->create();

        $this->assertTrue($asset->isDeletable());
    }

    public function test_display_and_detailed_name_attributes(): void
    {
        $asset = Asset::factory()->create(['name' => 'Laptop 1', 'asset_tag' => 'TAG-1']);

        $this->assertStringContainsString('Laptop 1', $asset->display_name);
        $this->assertNotEmpty($asset->detailed_name);
    }

    public function test_warranty_and_eol_date_accessors(): void
    {
        $model = AssetModel::factory()->create(['eol' => 12]);
        $asset = Asset::factory()->for($model, 'model')->create([
            'purchase_date' => '2020-01-01',
            'warranty_months' => 24,
        ]);

        $this->assertEquals('2022-01-01', $asset->warranty_expires->format('Y-m-d'));
        $this->assertNotNull($asset->warranty_expires_formatted_date);
        $this->assertNotNull($asset->warranty_expires_diff);
        $this->assertIsString($asset->warranty_expires_diff_for_humans);
    }

    public function test_audit_date_accessors_are_accessible(): void
    {
        $asset = Asset::factory()->create([
            'last_audit_date' => '2023-01-01 00:00:00',
            'next_audit_date' => '2025-01-01',
        ]);

        // Acceder a los accessors ejercita el formateo de fechas de auditoria.
        $this->assertNotNull($asset->last_audit_formatted_date);
        $this->assertNotNull($asset->next_audit_formatted_date);
        $this->assertTrue(is_numeric($asset->next_audit_diff_in_days));
    }

    public function test_get_depreciation_returns_value_or_null(): void
    {
        $asset = Asset::factory()->create();

        // Sin depreciacion asociada no debe lanzar excepcion.
        $this->assertTrue($asset->get_depreciation() === null || is_object($asset->get_depreciation()));
    }
}
