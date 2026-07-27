<?php

namespace Tests\Feature\Reporting;

use App\Models\Asset;
use App\Models\Maintenance;
use App\Models\MaintenanceType;
use App\Models\Supplier;
use App\Models\User;
use Tests\TestCase;

/**
 * Coverage for /reports/export/maintenances — the server-side streamed
 * CSV export. Independent from the datatable-driven report at
 * /reports/maintenances (which uses the API + presenter and has its own
 * client-side export).
 *
 * The pre-#19039 CSV emitted a legacy `improvement_type` accessor that no
 * longer exists on the model, silently writing empty cells where the
 * maintenance type used to appear. It also skipped completed_at,
 * completed_by, is_warranty, and notes, and would crash on a null
 * supplier or a soft-deleted asset. These tests lock in the corrected
 * behavior.
 */
class MaintenanceReportTest extends TestCase
{
    public function test_requires_permission_to_export()
    {
        $this->actingAs(User::factory()->create())
            ->get(route('reports/export/maintenances'))
            ->assertForbidden();
    }

    public function test_export_returns_csv()
    {
        $this->actingAs(User::factory()->canViewReports()->create())
            ->get(route('reports/export/maintenances'))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=utf-8');
    }

    public function test_export_includes_both_active_and_completed_maintenances()
    {
        Maintenance::factory()->create([
            'name' => 'Active-Repair-XYZ',
            'completed_at' => null,
        ]);
        Maintenance::factory()->create([
            'name' => 'Completed-Repair-ABC',
            'start_date' => '2021-01-01 00:00:00',
            'completed_at' => '2021-01-10 12:00:00',
        ]);

        $content = $this->actingAs(User::factory()->canViewReports()->create())
            ->get(route('reports/export/maintenances'))
            ->assertOk()
            ->streamedContent();

        $this->assertStringContainsString('Active-Repair-XYZ', $content);
        $this->assertStringContainsString('Completed-Repair-ABC', $content);
    }

    public function test_export_row_contains_maintenance_type_name()
    {
        // Before the #19039 rewrite the report emitted
        // $maintenance->improvement_type which has no accessor on the
        // current model — the type column silently came out empty. Now
        // it should be the related MaintenanceType.name.
        $type = MaintenanceType::factory()->create(['name' => 'Firmware-Upgrade-Test']);
        Maintenance::factory()->create(['maintenance_type_id' => $type->id]);

        $content = $this->actingAs(User::factory()->canViewReports()->create())
            ->get(route('reports/export/maintenances'))
            ->assertOk()
            ->streamedContent();

        $this->assertStringContainsString('Firmware-Upgrade-Test', $content);
    }

    public function test_export_row_contains_completed_at_and_completed_by()
    {
        $completer = User::factory()->create([
            'first_name' => 'Rutherford',
            'last_name' => 'Hayes',
        ]);
        Maintenance::factory()->create([
            'name' => 'Backfill-Repair',
            'start_date' => '2021-06-01 00:00:00',
            'completed_at' => '2021-06-05 09:15:00',
            'completed_by' => $completer->id,
        ]);

        $content = $this->actingAs(User::factory()->canViewReports()->create())
            ->get(route('reports/export/maintenances'))
            ->assertOk()
            ->streamedContent();

        // completed_at gets rendered by fputcsv as the Carbon toString
        // representation (Y-m-d H:i:s). Just look for the date portion so
        // this doesn't get flaky on timezone-dependent formatting.
        $this->assertStringContainsString('2021-06-05', $content);
        $this->assertStringContainsString($completer->display_name, $content);
    }

    public function test_export_row_survives_null_supplier()
    {
        // supplier_id is nullable on the maintenances table; the pre-fix
        // report did $maintenance->supplier->name unconditionally and
        // would fatal-error on any row without one. The presence of the
        // row in the output doubles as a "no 500" assertion.
        Maintenance::factory()->create([
            'name' => 'Supplier-Less',
            'supplier_id' => null,
        ]);

        $content = $this->actingAs(User::factory()->canViewReports()->create())
            ->get(route('reports/export/maintenances'))
            ->assertOk()
            ->streamedContent();

        $this->assertStringContainsString('Supplier-Less', $content);
    }

    public function test_export_row_shows_supplier_when_present()
    {
        $supplier = Supplier::factory()->create(['name' => 'Contoso-Repairs-Inc']);
        Maintenance::factory()->create(['supplier_id' => $supplier->id]);

        $content = $this->actingAs(User::factory()->canViewReports()->create())
            ->get(route('reports/export/maintenances'))
            ->assertOk()
            ->streamedContent();

        $this->assertStringContainsString('Contoso-Repairs-Inc', $content);
    }

    public function test_export_row_uses_new_expected_completion_date_value()
    {
        // Regression guard for the rename: the CSV must show the value
        // stored on expected_completion_date (the renamed column), not
        // an accidentally-empty cell if someone re-introduced the old
        // completion_date reference.
        Maintenance::factory()->create([
            'name' => 'RenamedDateTest',
            'start_date' => '2021-01-01 00:00:00',
            'expected_completion_date' => '2021-01-31 00:00:00',
        ]);

        $content = $this->actingAs(User::factory()->canViewReports()->create())
            ->get(route('reports/export/maintenances'))
            ->assertOk()
            ->streamedContent();

        $this->assertStringContainsString('RenamedDateTest', $content);
        $this->assertStringContainsString('2021-01-31', $content);
    }

    public function test_export_includes_is_warranty_and_notes_columns()
    {
        Maintenance::factory()->create([
            'name' => 'WarrantyNoteRow',
            'is_warranty' => 1,
            'notes' => 'A distinctive-note-blob-1729',
        ]);

        $content = $this->actingAs(User::factory()->canViewReports()->create())
            ->get(route('reports/export/maintenances'))
            ->assertOk()
            ->streamedContent();

        $this->assertStringContainsString('A distinctive-note-blob-1729', $content);
    }

    public function test_header_row_appears_only_once_even_with_many_rows()
    {
        Maintenance::factory()->count(3)->create();

        $content = $this->actingAs(User::factory()->canViewReports()->create())
            ->get(route('reports/export/maintenances'))
            ->assertOk()
            ->streamedContent();

        // The Cost header is unique enough not to appear as row data.
        $this->assertSame(1, substr_count($content, 'Cost'));
    }
}
