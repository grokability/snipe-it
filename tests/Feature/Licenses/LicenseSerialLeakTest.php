<?php

namespace Tests\Feature\Licenses;

use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\License;
use App\Models\LicenseSeat;
use App\Models\User;
use Tests\TestCase;

class LicenseSerialLeakTest extends TestCase
{
    private const SENTINEL = 'ARPC-SECRET-PRODUCT-KEY-DO-NOT-LEAK';

    private function seedCheckoutHistory(License $license): void
    {
        // Every license factory-create writes a "create" actionlog with the
        // license row as $log->item, so the history endpoint has content
        // immediately. Add an explicit checkout event so more than one row
        // exercises the transformer, and so log_meta scrubbing has a diff to
        // scrub on some rows.
        $log = new Actionlog;
        $log->item_type = License::class;
        $log->item_id = $license->id;
        $log->action_type = 'checkout';
        $log->target_type = User::class;
        $log->target_id = User::factory()->create()->id;
        $log->log_meta = json_encode([
            'serial' => ['old' => 'PREVIOUS-'.self::SENTINEL, 'new' => self::SENTINEL],
        ]);
        $log->save();
    }

    public function test_history_endpoint_masks_serial_for_users_without_view_keys(): void
    {
        $license = License::factory()->create(['serial' => self::SENTINEL]);
        $this->seedCheckoutHistory($license);

        $viewOnly = User::factory()->viewLicenses()->create();

        $response = $this->actingAsForApi($viewOnly)
            ->getJson(route('api.licenses.history', $license))
            ->assertOk();

        $body = $response->getContent();
        $this->assertStringNotContainsString(self::SENTINEL, $body);
        $this->assertStringContainsString(License::PRODUCT_KEY_MASK, $body);
    }

    public function test_history_endpoint_emits_real_serial_for_users_with_view_keys(): void
    {
        $license = License::factory()->create(['serial' => self::SENTINEL]);
        $this->seedCheckoutHistory($license);

        $keyHolder = User::factory()->viewLicenses()->viewKeysLicenses()->create();

        $response = $this->actingAsForApi($keyHolder)
            ->getJson(route('api.licenses.history', $license))
            ->assertOk();

        $this->assertStringContainsString(self::SENTINEL, $response->getContent());
    }

    public function test_history_endpoint_masks_serial_in_log_meta_diff_for_view_only(): void
    {
        $license = License::factory()->create(['serial' => self::SENTINEL]);
        $this->seedCheckoutHistory($license);

        $viewOnly = User::factory()->viewLicenses()->create();

        $response = $this->actingAsForApi($viewOnly)
            ->getJson(route('api.licenses.history', $license))
            ->assertOk();

        $body = $response->getContent();
        // Both old and new serial values in log_meta must be masked.
        $this->assertStringNotContainsString('PREVIOUS-'.self::SENTINEL, $body);
        $this->assertStringNotContainsString(self::SENTINEL, $body);
    }

    public function test_activity_report_search_cannot_oracle_license_serial(): void
    {
        // Regression test for GHSA-68gh-qwvc-qccg. Before the fix,
        // GET /api/v1/reports/activity?item_type=license&item_id=X&search=<sub>
        // leaked the real product key one character at a time through
        // result-count differences on licenses.serial. The fix strips serial
        // from Actionlog::$searchableRelations['licenses'].
        $license = License::factory()->create(['name' => 'Adobe CC', 'serial' => self::SENTINEL]);
        LicenseSeat::factory()->for($license)->create();
        $this->seedCheckoutHistory($license);

        $viewOnly = User::factory()->canViewReports()->viewLicenses()->create();

        $bySerial = $this->actingAsForApi($viewOnly)
            ->getJson(route('api.activity.index', [
                'item_type' => 'license',
                'item_id' => $license->id,
                'search' => 'SECRET-PRODUCT-KEY',
            ]))
            ->assertOk()
            ->json();
        $this->assertSame(0, $bySerial['total'], 'Activity report search must not expose license.serial as a text-search column, even for superusers.');

        // viewKeys does not re-open the hole. Unlike the license list, the
        // activity report has no legitimate caller for serial-substring
        // search, so the column is stripped unconditionally.
        $superuser = User::factory()->superuser()->create();
        $superuserBySerial = $this->actingAsForApi($superuser)
            ->getJson(route('api.activity.index', [
                'item_type' => 'license',
                'item_id' => $license->id,
                'search' => 'SECRET-PRODUCT-KEY',
            ]))
            ->assertOk()
            ->json();
        $this->assertSame(0, $superuserBySerial['total']);

        // Search on a non-serial license column still works: proves we
        // haven't torched search for the licenses relation entirely.
        $byName = $this->actingAsForApi($superuser)
            ->getJson(route('api.activity.index', [
                'item_type' => 'license',
                'item_id' => $license->id,
                'search' => 'Adobe',
            ]))
            ->assertOk()
            ->json();
        $this->assertGreaterThan(0, $byName['total']);
    }

    public function test_activity_report_search_still_matches_log_meta_on_non_license_rows(): void
    {
        // Companion assertion to the serial-oracle regression test above:
        // the fix removes log_meta from the trait-generic search path, but
        // re-adds it in advancedTextSearch() scoped to item_type != License.
        // That preserves "find who changed asset status to Lost" and
        // similar admin workflows on non-license logs.
        $asset = Asset::factory()->create();
        $log = new Actionlog;
        $log->item_type = Asset::class;
        $log->item_id = $asset->id;
        $log->action_type = 'update';
        $log->log_meta = json_encode([
            'status_id' => ['old' => 'Ready to Deploy', 'new' => 'ARCHIVED-SENTINEL-STATUS'],
        ]);
        $log->save();

        $superuser = User::factory()->superuser()->create();

        $hit = $this->actingAsForApi($superuser)
            ->getJson(route('api.activity.index', [
                'search' => 'ARCHIVED-SENTINEL-STATUS',
            ]))
            ->assertOk()
            ->json();
        $this->assertGreaterThan(0, $hit['total'], 'log_meta search must still match on non-license action logs after the oracle fix.');
    }

    public function test_activity_report_api_masks_serial_for_users_without_view_keys(): void
    {
        $license = License::factory()->create(['serial' => self::SENTINEL]);
        LicenseSeat::factory()->for($license)->create();
        $this->seedCheckoutHistory($license);

        $viewOnly = User::factory()->canViewReports()->viewLicenses()->create();

        $response = $this->actingAsForApi($viewOnly)
            ->getJson(route('api.activity.index'))
            ->assertOk();

        $body = $response->getContent();
        $this->assertStringNotContainsString(self::SENTINEL, $body);
    }

    public function test_activity_report_csv_masks_serial_for_users_without_view_keys(): void
    {
        $license = License::factory()->create(['serial' => self::SENTINEL]);
        $this->seedCheckoutHistory($license);

        $viewOnly = User::factory()->canViewReports()->viewLicenses()->create();

        $this->actingAs($viewOnly)
            ->post(route('reports.activity.post'))
            ->assertOk()
            ->assertDontSeeTextInStreamedResponse(self::SENTINEL);
    }

    public function test_legacy_license_report_csv_masks_serial_for_users_without_view_keys(): void
    {
        $license = License::factory()->create([
            'name' => 'Definitely a real license',
            'serial' => self::SENTINEL,
        ]);

        $viewOnly = User::factory()->canViewReports()->viewLicenses()->create();

        $this->actingAs($viewOnly)
            ->get(route('reports/export/licenses'))
            ->assertOk()
            ->assertDontSeeTextInStreamedResponse(self::SENTINEL);
    }

    public function test_legacy_license_report_csv_emits_real_serial_for_users_with_view_keys(): void
    {
        $license = License::factory()->create([
            'name' => 'Definitely a real license',
            'serial' => self::SENTINEL,
        ]);

        $keyHolder = User::factory()->canViewReports()->viewLicenses()->viewKeysLicenses()->create();

        $this->actingAs($keyHolder)
            ->get(route('reports/export/licenses'))
            ->assertOk()
            ->assertSeeTextInStreamedResponse(self::SENTINEL);
    }
}
