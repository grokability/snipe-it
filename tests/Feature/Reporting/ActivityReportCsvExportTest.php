<?php

namespace Tests\Feature\Reporting;

use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\Component;
use App\Models\License;
use App\Models\User;
use Tests\TestCase;

/**
 * CSV export path of the activity report page. Split from
 * ActivityReportPageAuthTest so each file stays under PHPMD's
 * per-class method threshold. Covers the export button surfacing
 * for reports.view holders and scoped viewers, the endpoint's
 * auth gate, and the per-viewable-type row filter applied to the
 * streamed CSV output.
 */
class ActivityReportCsvExportTest extends TestCase
{
    public function test_reports_view_holder_sees_csv_export_button()
    {
        $this->actingAs(User::factory()->canViewReports()->create())
            ->get(route('reports.activity'))
            ->assertOk()
            ->assertSee(route('reports.activity.post'));
    }

    public function test_scoped_viewer_also_sees_csv_export_button()
    {
        // Scoped viewers get an export gated to the same viewable
        // types they see on the page. Filtering happens inside
        // postActivityReport.
        $this->actingAs(User::factory()->viewComponents()->create())
            ->get(route('reports.activity'))
            ->assertOk()
            ->assertSee(route('reports.activity.post'));
    }

    public function test_permissionless_user_cannot_hit_csv_export_endpoint()
    {
        $this->actingAs(User::factory()->create())
            ->post(route('reports.activity.post'))
            ->assertForbidden();
    }

    public function test_scoped_viewer_csv_export_only_contains_rows_for_viewable_types()
    {
        // A components-only viewer's CSV must contain rows for
        // Component actionlogs and omit rows for Asset / License /
        // Consumable / Accessory actionlogs. Same viewable-type
        // filter the on-page endpoint applies.
        $component = Component::factory()->create();
        $asset = Asset::factory()->create();

        Actionlog::factory()->create([
            'item_type' => Component::class,
            'item_id' => $component->id,
            'action_type' => 'checkout',
            'note' => 'COMPONENT_ROW_SENTINEL',
        ]);
        Actionlog::factory()->create([
            'item_type' => Asset::class,
            'item_id' => $asset->id,
            'action_type' => 'checkout',
            'note' => 'ASSET_ROW_SENTINEL',
        ]);

        $body = $this->actingAs(User::factory()->viewComponents()->create())
            ->post(route('reports.activity.post'))
            ->assertOk()
            ->streamedContent();

        $this->assertStringContainsString('COMPONENT_ROW_SENTINEL', $body);
        $this->assertStringNotContainsString('ASSET_ROW_SENTINEL', $body);
    }

    public function test_activity_csv_masks_license_serial_and_log_meta_for_viewer_without_viewkeys()
    {
        // Regression for GHSA-qp3v-6792-5cpq. The CSV writer previously
        // emitted the raw action_logs.log_meta JSON into the Changed
        // column. LicenseObserver records every changed license
        // attribute including serial, so an edit left old / new product
        // keys in the row even when the serial column itself was masked.
        $oldKey = 'OLD-REAL-KEY-AAA111';
        $newKey = 'NEW-REAL-KEY-BBB222';

        $license = License::factory()->create([
            'name' => 'Real License',
            'serial' => $oldKey,
        ]);

        $license->serial = $newKey;
        $license->save();

        $this->assertDatabaseHas('action_logs', [
            'item_type' => License::class,
            'item_id' => $license->id,
            'action_type' => 'update',
        ]);

        $csv = $this->actingAs(User::factory()->canViewReports()->viewLicenses()->create())
            ->post(route('reports.activity.post'))
            ->assertOk()
            ->streamedContent();

        // Mask on the item.serial column AND inside the log_meta blob,
        // both of which previously leaked the raw product key.
        $this->assertStringNotContainsString($oldKey, $csv);
        $this->assertStringNotContainsString($newKey, $csv);
        $this->assertStringContainsString(License::PRODUCT_KEY_MASK, $csv);
    }

    public function test_activity_csv_shows_license_serial_and_log_meta_for_viewkeys_holder()
    {
        // Permitted viewers still see the real product keys so audit
        // history stays reconstructable. The accessors only mask on
        // the gate check failing, raw values stay in the DB regardless.
        $oldKey = 'OLD-REAL-KEY-CCC333';
        $newKey = 'NEW-REAL-KEY-DDD444';

        $license = License::factory()->create([
            'name' => 'Real License',
            'serial' => $oldKey,
        ]);

        $license->serial = $newKey;
        $license->save();

        $csv = $this->actingAs(User::factory()->canViewReports()->viewLicenses()->viewKeysLicenses()->create())
            ->post(route('reports.activity.post'))
            ->assertOk()
            ->streamedContent();

        $this->assertStringContainsString($oldKey, $csv);
        $this->assertStringContainsString($newKey, $csv);
    }

    public function test_action_log_raw_db_still_contains_license_keys_after_masking_fix()
    {
        // Audit integrity check. The accessors mask on read only.
        // getRawOriginal and the underlying column must still carry
        // the real values so a permitted viewer can reconstruct the
        // audit trail and so a key-rotation audit remains possible.
        $oldKey = 'OLD-REAL-KEY-EEE555';
        $newKey = 'NEW-REAL-KEY-FFF666';

        $license = License::factory()->create(['serial' => $oldKey]);
        $license->serial = $newKey;
        $license->save();

        $row = \DB::table('action_logs')
            ->where('item_type', License::class)
            ->where('item_id', $license->id)
            ->where('action_type', 'update')
            ->first();

        $this->assertNotNull($row);
        $this->assertStringContainsString($oldKey, $row->log_meta);
        $this->assertStringContainsString($newKey, $row->log_meta);
    }

    public function test_reports_view_holder_csv_export_contains_every_type()
    {
        // reports.view holders keep the full unfiltered CSV. Guard
        // against a future refactor that accidentally applies the
        // scoped-viewer filter to admin exports.
        $component = Component::factory()->create();
        $asset = Asset::factory()->create();

        Actionlog::factory()->create([
            'item_type' => Component::class,
            'item_id' => $component->id,
            'action_type' => 'checkout',
            'note' => 'COMPONENT_ROW_SENTINEL',
        ]);
        Actionlog::factory()->create([
            'item_type' => Asset::class,
            'item_id' => $asset->id,
            'action_type' => 'checkout',
            'note' => 'ASSET_ROW_SENTINEL',
        ]);

        $body = $this->actingAs(User::factory()->canViewReports()->create())
            ->post(route('reports.activity.post'))
            ->assertOk()
            ->streamedContent();

        $this->assertStringContainsString('COMPONENT_ROW_SENTINEL', $body);
        $this->assertStringContainsString('ASSET_ROW_SENTINEL', $body);
    }
}
