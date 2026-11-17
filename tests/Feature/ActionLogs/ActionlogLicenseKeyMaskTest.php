<?php

namespace Tests\Feature\ActionLogs;

use App\Models\Actionlog;
use App\Models\Company;
use App\Models\License;
use App\Models\User;
use Tests\TestCase;

/**
 * Regression coverage for the license-key mask half of
 * GHSA-m955-p5pg-78mv.
 *
 * Actionlog::logMeta masks the raw product-key values in the diff blob
 * for any caller without the viewKeys gate on the owning License.
 * Before the fix, the mask was keyed off `$this->item instanceof
 * License`. For a cross-tenant license, CompanyableScope filters the
 * related model to null, so `null instanceof License === false` and
 * the raw product-key diff returned unmasked.
 *
 * After the fix, the mask is keyed off the row's item_type, not the
 * loaded relationship, and the License is reloaded via
 * withoutGlobalScopes to drive the viewKeys gate. Fail-closed: a
 * missing license row (hard-deleted) applies the mask too.
 */
class ActionlogLicenseKeyMaskTest extends TestCase
{
    private function makeViewerInCompany(Company $company, array $permissionKeys = []): User
    {
        $permissions = [];
        foreach ($permissionKeys as $key) {
            $permissions[$key] = '1';
        }

        return $company->users()->save(
            User::factory()->make(['permissions' => json_encode($permissions)])
        );
    }

    private function seedKeyDiffLog(License $license, ?int $companyId = null): Actionlog
    {
        $metaJson = json_encode([
            'serial' => [
                'old' => 'OLD-KEY-1111-2222',
                'new' => 'NEW-KEY-3333-4444',
            ],
        ]);

        return Actionlog::factory()->create([
            'item_type' => License::class,
            'item_id' => $license->id,
            'log_meta' => $metaJson,
            'company_id' => $companyId,
        ]);
    }

    public function test_viewer_without_viewkeys_sees_masked_product_keys(): void
    {
        // Baseline: in-tenant viewer without licenses.keys sees masks,
        // not the raw product key. Pins the current unaltered behavior
        // so a future regression on the gate side surfaces here.
        $company = Company::factory()->create();
        $viewer = $this->makeViewerInCompany($company, ['licenses.view']);

        $license = License::factory()->create(['company_id' => $company->id]);
        $log = $this->seedKeyDiffLog($license, $company->id);

        $this->actingAs($viewer);

        $fetched = Actionlog::query()->find($log->id);
        $decoded = json_decode($fetched->log_meta, true);

        $this->assertSame(License::PRODUCT_KEY_MASK, $decoded['serial']['old']);
        $this->assertSame(License::PRODUCT_KEY_MASK, $decoded['serial']['new']);
    }

    public function test_viewer_with_viewkeys_sees_raw_product_keys(): void
    {
        // Positive regression: the mask unmasks for a caller with the
        // viewKeys gate. licenses.keys grants viewKeys per LicensePolicy.
        $company = Company::factory()->create();
        $viewer = $this->makeViewerInCompany($company, ['licenses.view', 'licenses.keys']);

        $license = License::factory()->create(['company_id' => $company->id]);
        $log = $this->seedKeyDiffLog($license, $company->id);

        $this->actingAs($viewer);

        // Use withoutGlobalScopes so this assertion drives the mask path
        // directly and doesn't depend on whether the row is visible
        // through the FMCS tenant scope separately.
        $fetched = Actionlog::withoutGlobalScopes()->find($log->id);
        $decoded = json_decode($fetched->log_meta, true);

        $this->assertSame('OLD-KEY-1111-2222', $decoded['serial']['old']);
        $this->assertSame('NEW-KEY-3333-4444', $decoded['serial']['new']);
    }

    public function test_mask_fires_when_related_license_is_scope_filtered_to_null(): void
    {
        // The exact bypass the advisory describes. Viewer is in Company B
        // with licenses.keys ON. The license lives in Company A. Even if
        // CompanyableScope were to let the cross-tenant action_log through
        // (it does not any more, see the sibling tenant-scope test), the
        // License object loaded through $this->item is null for this
        // viewer, so the pre-fix mask branch would have returned raw keys.
        $this->settings->enableMultipleFullCompanySupport();
        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $viewer = $this->makeViewerInCompany($companyB, ['licenses.view', 'licenses.keys']);

        $license = License::factory()->create(['company_id' => $companyA->id]);
        $log = $this->seedKeyDiffLog($license, $companyA->id);

        $this->actingAs($viewer);

        // Bypass CompanyableScope to force-read the row and confirm the
        // mask still fires when the viewer can't see the owning license
        // through the normal scope. licenses.keys is held but gates on a
        // License instance the viewer does not own in strict FMCS.
        $fetched = Actionlog::withoutGlobalScopes()->find($log->id);
        $decoded = json_decode($fetched->log_meta, true);

        $this->assertSame(
            License::PRODUCT_KEY_MASK,
            $decoded['serial']['old'],
            'Cross-tenant log_meta leaked raw product key. Mask must not depend on $this->item resolving to a non-null License.'
        );
        $this->assertSame(License::PRODUCT_KEY_MASK, $decoded['serial']['new']);
    }

    public function test_mask_fires_when_underlying_license_is_hard_deleted(): void
    {
        // Fail-closed. If the owning License row no longer exists (hard
        // delete, not soft delete), the viewKeys gate can't evaluate and
        // the mask must still apply. Prevents a null-license grant from
        // becoming the new bypass surface.
        $company = Company::factory()->create();
        $viewer = $this->makeViewerInCompany($company, ['licenses.view', 'licenses.keys']);

        $license = License::factory()->create(['company_id' => $company->id]);
        $log = $this->seedKeyDiffLog($license, $company->id);

        // Hard-delete the license without going through the SoftDeletes
        // trait, since its action_logs are the thing under test.
        License::where('id', $license->id)->forceDelete();

        $this->actingAs($viewer);

        $fetched = Actionlog::withoutGlobalScopes()->find($log->id);
        $decoded = json_decode($fetched->log_meta, true);

        $this->assertSame(License::PRODUCT_KEY_MASK, $decoded['serial']['old']);
        $this->assertSame(License::PRODUCT_KEY_MASK, $decoded['serial']['new']);
    }

    public function test_non_license_log_meta_passes_through_unmasked(): void
    {
        // Regression guard: the mask dispatch must only fire for License
        // item_type. Asset / User / Accessory / etc. log_meta diffs are
        // legitimate audit data and have never been masked.
        $company = Company::factory()->create();
        $viewer = $this->makeViewerInCompany($company, ['reports.view']);

        $meta = json_encode([
            'name' => ['old' => 'Old Name', 'new' => 'New Name'],
            'serial' => ['old' => 'ASSET-SERIAL-OLD', 'new' => 'ASSET-SERIAL-NEW'],
        ]);

        $log = Actionlog::factory()->create([
            'item_type' => \App\Models\Asset::class,
            'item_id' => \App\Models\Asset::factory()->create(['company_id' => $company->id])->id,
            'log_meta' => $meta,
            'company_id' => $company->id,
        ]);

        $this->actingAs($viewer);

        $fetched = Actionlog::query()->find($log->id);
        $decoded = json_decode($fetched->log_meta, true);

        $this->assertSame('ASSET-SERIAL-OLD', $decoded['serial']['old']);
        $this->assertSame('ASSET-SERIAL-NEW', $decoded['serial']['new']);
    }
}
