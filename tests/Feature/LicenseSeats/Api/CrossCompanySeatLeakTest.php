<?php

namespace Tests\Feature\LicenseSeats\Api;

use App\Http\Transformers\LicenseSeatsTransformer;
use App\Models\Asset;
use App\Models\Company;
use App\Models\License;
use App\Models\LicenseSeat;
use App\Models\User;
use Tests\TestCase;

/**
 * Regression coverage for GHSA-vj5w-6wvp-ppxv.
 *
 * LicenseSeat is a CompanyableChild of both asset and license and
 * Company::scopeCompanyableChildren() ORs the two parents, so a seat
 * whose license belongs to Company B but whose assigned asset belongs
 * to Company A used to leak through to a Company A caller on both
 * GET /api/v1/hardware/{asset}/licenses and
 * GET /api/v1/licenses/{license}/seats/{seat}.
 *
 * Three fixes together close the surface and this file covers all
 * three:
 *   1. AssetsController::licenses() now filters each seat through
 *      License::find(), dropping any whose owning tenant the caller
 *      cannot see.
 *   2. LicenseSeatsController::show() short-circuits with the standard
 *      not-found error when License::find() returns null, so the
 *      cross-tenant path returns the same error the sibling index path
 *      returns.
 *   3. LicenseSeatsTransformer null-safes every $seat->license access
 *      so a scoped-out license cannot 500 the response regardless of
 *      caller path.
 */
class CrossCompanySeatLeakTest extends TestCase
{
    protected Company $companyA;

    protected Company $companyB;

    protected function setUp(): void
    {
        parent::setUp();
        [$this->companyA, $this->companyB] = Company::factory()->count(2)->create();
    }

    /**
     * Mirrors HttpCrossCompanyLeakTest's helper: non-superuser caller
     * pinned to Company A via the company_user pivot, no superuser
     * bypass, with the requested view permissions.
     */
    private function callerScopedToCompanyA(array $viewPermissionKeys): User
    {
        $permissions = [];
        foreach ($viewPermissionKeys as $key) {
            $permissions[$key.'.view'] = '1';
        }

        return $this->companyA->users()->save(
            User::factory()->make(['permissions' => json_encode($permissions)])
        );
    }

    /**
     * The trigger shape the advisory describes: a seat whose owning
     * license is in a different tenant than the asset it is checked
     * out to. Admin edits that produce this state include editing the
     * license's company after checkout and enabling FMCS post-hoc.
     *
     * @return array{asset: Asset, license: License, seat: LicenseSeat}
     */
    private function companyBLicenseCheckedOutToCompanyAAsset(string $notes): array
    {
        $asset = Asset::factory()->for($this->companyA)->create();

        // License is created in Company A so the single auto-seeded
        // seat row lines up with Company A rules at checkout time,
        // then we move the license to Company B to produce the mixed
        // state the advisory describes. The seat row stays put.
        $license = License::factory()->create([
            'company_id' => $this->companyA->id,
            'seats' => 1,
        ]);

        // withoutGlobalScopes so fixture building does not depend on
        // an authed user context. CompanyableScope on LicenseSeat would
        // otherwise hide the row during setup once FMCS is on.
        $seat = LicenseSeat::withoutGlobalScopes()->where('license_id', $license->id)->first();
        $seat->update(['asset_id' => $asset->id, 'notes' => $notes]);

        $license->update(['company_id' => $this->companyB->id]);

        return ['asset' => $asset, 'license' => $license->fresh(), 'seat' => LicenseSeat::withoutGlobalScopes()->find($seat->id)];
    }

    public function test_asset_licenses_hides_cross_company_seat_under_strict_fmcs(): void
    {
        $this->settings->enableMultipleFullCompanySupport();
        $this->settings->disableFloaterMode();

        $leaky = $this->companyBLicenseCheckedOutToCompanyAAsset('STRICT-REACH-NOTE');
        $caller = $this->callerScopedToCompanyA(['assets', 'licenses']);

        $response = $this->actingAsForApi($caller)
            ->getJson(route('api.assets.licenselist', $leaky['asset']))
            ->assertOk();

        $this->assertSame(0, $response->json('total'), 'Company B seat leaked into asset-licenses response under strict FMCS.');
        $this->assertStringNotContainsString('STRICT-REACH-NOTE', $response->getContent(), 'Company B seat notes leaked into asset-licenses response under strict FMCS.');
    }

    public function test_asset_licenses_hides_cross_company_seat_under_floater_fmcs(): void
    {
        $this->settings->enableMultipleFullCompanySupport();
        $this->settings->enableFloaterMode();

        $leaky = $this->companyBLicenseCheckedOutToCompanyAAsset('FLOATER-REACH-NOTE');
        $caller = $this->callerScopedToCompanyA(['assets', 'licenses']);

        $response = $this->actingAsForApi($caller)
            ->getJson(route('api.assets.licenselist', $leaky['asset']))
            ->assertOk();

        $this->assertSame(0, $response->json('total'), 'Company B seat leaked into asset-licenses response under floater FMCS.');
        $this->assertStringNotContainsString('FLOATER-REACH-NOTE', $response->getContent(), 'Company B seat notes leaked into asset-licenses response under floater FMCS.');
    }

    public function test_asset_licenses_still_shows_same_company_seats_for_caller(): void
    {
        // Positive control: a Company A caller must still see a seat
        // from a Company A license checked out to a Company A asset,
        // so the filter cannot regress into a blanket drop.
        $this->settings->enableMultipleFullCompanySupport();

        $asset = Asset::factory()->for($this->companyA)->create();
        $license = License::factory()->create([
            'company_id' => $this->companyA->id,
            'seats' => 1,
        ]);
        $seat = LicenseSeat::withoutGlobalScopes()->where('license_id', $license->id)->first();
        $seat->update(['asset_id' => $asset->id, 'notes' => 'SAME-TENANT-NOTE']);

        $caller = $this->callerScopedToCompanyA(['assets', 'licenses']);

        $response = $this->actingAsForApi($caller)
            ->getJson(route('api.assets.licenselist', $asset))
            ->assertOk();

        $this->assertSame(1, $response->json('total'), 'Same-tenant seat was incorrectly filtered out of asset-licenses response.');
    }

    public function test_license_seats_show_returns_not_found_for_cross_company_license_under_strict_fmcs(): void
    {
        $this->settings->enableMultipleFullCompanySupport();
        $this->settings->disableFloaterMode();

        $leaky = $this->companyBLicenseCheckedOutToCompanyAAsset('STRICT-SHOW-NOTE');
        $caller = $this->callerScopedToCompanyA(['licenses']);

        $response = $this->actingAsForApi($caller)
            ->getJson(route('api.licenses.seats.show', [$leaky['license']->id, $leaky['seat']->id]));

        $response->assertOk();
        $this->assertSame('error', $response->json('status'), 'Cross-company seat show returned a success shape instead of the not-found error.');
        $this->assertStringNotContainsString('STRICT-SHOW-NOTE', $response->getContent(), 'Company B seat notes leaked into seat-show response under strict FMCS.');
    }

    public function test_license_seats_show_returns_not_found_for_cross_company_license_under_floater_fmcs(): void
    {
        $this->settings->enableMultipleFullCompanySupport();
        $this->settings->enableFloaterMode();

        $leaky = $this->companyBLicenseCheckedOutToCompanyAAsset('FLOATER-SHOW-NOTE');
        $caller = $this->callerScopedToCompanyA(['licenses']);

        $response = $this->actingAsForApi($caller)
            ->getJson(route('api.licenses.seats.show', [$leaky['license']->id, $leaky['seat']->id]));

        $response->assertOk();
        $this->assertSame('error', $response->json('status'), 'Cross-company seat show returned a success shape instead of the not-found error.');
        $this->assertStringNotContainsString('FLOATER-SHOW-NOTE', $response->getContent(), 'Company B seat notes leaked into seat-show response under floater FMCS.');
    }

    public function test_transformer_null_safe_when_license_relation_is_null(): void
    {
        // Defense-in-depth: if a caller somehow reaches the transformer
        // with a seat whose owning license is not visible (CompanyableScope
        // resolves $seat->license to null), the transformer must produce
        // a well-formed array and not dereference null.
        $license = License::factory()->create(['seats' => 1]);
        $seat = LicenseSeat::withoutGlobalScopes()->where('license_id', $license->id)->first();

        // Point the seat at a license id that definitely resolves to
        // null on a fresh load. Raw update bypasses any model-level
        // relation refresh.
        LicenseSeat::withoutGlobalScopes()->where('id', $seat->id)->update(['license_id' => 0]);
        $reloaded = LicenseSeat::withoutGlobalScopes()->find($seat->id);

        $this->assertNull($reloaded->license, 'Precondition: seat with license_id 0 must resolve to a null license relation.');

        $output = (new LicenseSeatsTransformer)->transformLicenseSeat($reloaded);

        $this->assertSame((int) $reloaded->id, $output['id']);
        $this->assertNull($output['license_id'], 'license_id must be null when the owning license is not visible.');
        $this->assertFalse($output['reassignable']);
        $this->assertTrue($output['disabled']);
        $this->assertFalse($output['available_actions']['checkout']);
        $this->assertFalse($output['available_actions']['checkin']);
    }
}
