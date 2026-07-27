<?php

namespace Tests\Feature\Fmcs;

use App\Models\Asset;
use App\Models\Company;
use App\Models\Setting;
use App\Models\User;
use Tests\TestCase;

/**
 * Regression coverage for GitHub issue #19192, part 4 of 4.
 *
 * BulkAssetsController::update() calls $asset->update($updateArray)
 * per row, so the model-level fmcs_company rule reaches the bulk path
 * via ValidatingTrait. These tests pin the behavior on both the
 * "clearing Company should fail" and the "clearing Company is fine for
 * uncompanied actors working in the pseudo-company namespace" paths,
 * plus a sanity check that ordinary bulk edits touching only unrelated
 * fields still succeed.
 */
class FmcsStrictBulkAssetEditTest extends TestCase
{
    public function test_clearing_company_is_rejected_for_companied_non_superuser()
    {
        // Locked in so a future refactor of the bulk controller cannot
        // quietly bypass the gate.
        $this->settings->enableMultipleFullCompanySupport();
        $this->settings->disableFloaterMode();

        $company = Company::factory()->create();
        $actor = $company->users()->save(User::factory()->editAssets()->create());
        $target = Asset::factory()->create(['company_id' => $company->id]);

        $this->actingAs($actor)
            ->post(route('hardware/bulksave'), [
                'ids' => [$target->id],
                'company_id' => 'clear',
                'bulk_actions' => 'edit',
            ]);

        $this->assertEquals($company->id, $target->fresh()->company_id, 'Row company should not have been cleared');
    }

    public function test_clearing_company_is_allowed_for_uncompanied_non_superuser()
    {
        // Uncompanied non-superusers work in the null pseudo-company
        // namespace under strict mode. Bulk-clearing Company on rows
        // they own is a legitimate operation for them and the gate
        // steps aside.
        $this->settings->enableMultipleFullCompanySupport();
        $this->settings->disableFloaterMode();

        $actor = User::factory()->withoutCompany()->editAssets()->create();
        $target = Asset::factory()->create(['company_id' => null]);

        $this->actingAs($actor)
            ->post(route('hardware/bulksave'), [
                'ids' => [$target->id],
                'company_id' => 'clear',
                'bulk_actions' => 'edit',
            ]);

        $this->assertNull($target->fresh()->company_id);
    }

    public function test_editing_unrelated_field_still_works_for_companied_non_superuser()
    {
        // If the bulk edit doesn't touch Company at all, ValidatingTrait
        // sees the existing non-null company_id on each row and passes.
        // This is the "make sure we haven't broken ordinary bulk edits"
        // sanity guard.
        //
        // Defensive Setting cache flush. Some upstream tests in the full
        // MySQL sweep can leave the memoized Setting instance in a state
        // where full_multiple_companies_support looks enabled at the
        // moment auth loads but disabled by the time the fmcs_company
        // validator reads it, or vice versa. The Support helper's update()
        // clears the cache too, but only after both writes have landed.
        // Clearing here first pins the pre-state so the two writes below
        // are the only source of truth for this test's fmcs_company check.
        Setting::$_cache = null;
        $this->settings->enableMultipleFullCompanySupport();
        $this->settings->disableFloaterMode();

        $company = Company::factory()->create();
        $actor = $company->users()->save(User::factory()->editAssets()->create());
        $target = Asset::factory()->create([
            'company_id' => $company->id,
            'notes' => 'before',
        ]);

        $response = $this->actingAs($actor)
            ->post(route('hardware/bulksave'), [
                'ids' => [$target->id],
                'notes' => 'after',
                'bulk_actions' => 'edit',
            ]);

        // Fail loudly if the controller redirected with per-row errors
        // (the notes assertion below is a downstream symptom that hides
        // the actual validation failure). BulkAssetsController flashes
        // `bulk_asset_errors` (not `error`) when $asset->update() returns
        // false because ValidatingTrait rejected the save. Pulling those
        // errors into the assertion message turns any recurrence of the
        // MySQL CI full-suite flake into a concrete diagnostic.
        $bulkErrors = session('bulk_asset_errors');
        $this->assertNull(
            $bulkErrors,
            'Bulk asset edit produced per-row validation errors: '.json_encode($bulkErrors, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT),
        );

        $this->assertEquals('after', $target->fresh()->notes);
        $this->assertEquals($company->id, $target->fresh()->company_id);
    }
}
