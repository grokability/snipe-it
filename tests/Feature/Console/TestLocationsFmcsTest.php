<?php

namespace Tests\Feature\Console;

use App\Helpers\Helper;
use App\Models\Asset;
use App\Models\Company;
use App\Models\Location;
use App\Models\User;
use Tests\TestCase;

class TestLocationsFmcsTest extends TestCase
{
    private function mismatchedIds(array $mismatched): array
    {
        return array_column($mismatched, 1); // column 1 is the item ID
    }

    private function assetAt(Location $location, array $attrs = []): Asset
    {
        // Pin both location_id and rtd_location_id to prevent AssetFactory from
        // generating a random rtd_location with no company that would cause false mismatches.
        return Asset::factory()->create(array_merge([
            'location_id' => $location->id,
            'rtd_location_id' => $location->id,
        ], $attrs));
    }

    public function test_item_at_location_with_same_company_is_not_flagged()
    {
        $company = Company::factory()->create();
        $location = Location::factory()->for($company)->create();
        $asset = $this->assetAt($location, ['company_id' => $company->id]);

        $this->settings->enableMultipleFullCompanySupport();

        $result = Helper::test_locations_fmcs(true);

        $this->assertNotContains($asset->id, $this->mismatchedIds($result));
    }

    public function test_item_at_location_with_different_company_is_flagged()
    {
        [$companyA, $companyB] = Company::factory()->count(2)->create();
        $location = Location::factory()->for($companyA)->create();
        $asset = $this->assetAt($location, ['company_id' => $companyB->id]);

        $this->settings->enableMultipleFullCompanySupport();

        $result = Helper::test_locations_fmcs(true);

        $this->assertContains($asset->id, $this->mismatchedIds($result));
    }

    public function test_null_company_item_at_company_location_is_flagged_in_strict_mode()
    {
        $company = Company::factory()->create();
        $location = Location::factory()->for($company)->create();
        $asset = $this->assetAt($location, ['company_id' => null]);

        $this->settings->enableMultipleFullCompanySupport();

        $result = Helper::test_locations_fmcs(true);

        $this->assertContains($asset->id, $this->mismatchedIds($result));
    }

    public function test_null_company_item_at_company_location_is_not_flagged_in_floater_mode()
    {
        $company = Company::factory()->create();
        $location = Location::factory()->for($company)->create();
        $asset = $this->assetAt($location, ['company_id' => null]);

        $this->settings->enableFloaterMode();

        $result = Helper::test_locations_fmcs(true);

        $this->assertNotContains($asset->id, $this->mismatchedIds($result));
    }

    public function test_company_item_at_null_company_location_is_flagged_in_strict_mode()
    {
        $company = Company::factory()->create();
        $location = Location::factory()->create(['company_id' => null]);
        $asset = $this->assetAt($location, ['company_id' => $company->id]);

        $this->settings->enableMultipleFullCompanySupport();

        $result = Helper::test_locations_fmcs(true);

        $this->assertContains($asset->id, $this->mismatchedIds($result));
    }

    public function test_company_item_at_null_company_location_is_not_flagged_in_floater_mode()
    {
        $company = Company::factory()->create();
        $location = Location::factory()->create(['company_id' => null]);
        $asset = $this->assetAt($location, ['company_id' => $company->id]);

        $this->settings->enableFloaterMode();

        $result = Helper::test_locations_fmcs(true);

        $this->assertNotContains($asset->id, $this->mismatchedIds($result));
    }

    public function test_null_company_item_at_null_company_location_is_never_flagged()
    {
        $location = Location::factory()->create(['company_id' => null]);
        $asset = $this->assetAt($location, ['company_id' => null]);

        $this->settings->enableMultipleFullCompanySupport();

        $result = Helper::test_locations_fmcs(true);

        $this->assertNotContains($asset->id, $this->mismatchedIds($result));
    }

    public function test_user_at_location_with_matching_company_is_not_flagged()
    {
        $company = Company::factory()->create();
        $location = Location::factory()->for($company)->create();
        $user = User::factory()->create(['location_id' => $location->id]);
        $user->companies()->sync([$company->id]);

        $this->settings->enableMultipleFullCompanySupport();

        $result = Helper::test_locations_fmcs(true);

        $this->assertNotContains($user->id, $this->mismatchedIds($result));
    }

    public function test_user_at_location_with_different_company_is_flagged()
    {
        [$companyA, $companyB] = Company::factory()->count(2)->create();
        $location = Location::factory()->for($companyA)->create();
        $user = User::factory()->create(['location_id' => $location->id]);
        $user->companies()->sync([$companyB->id]);

        $this->settings->enableMultipleFullCompanySupport();

        $result = Helper::test_locations_fmcs(true);

        $this->assertContains($user->id, $this->mismatchedIds($result));
    }

    public function test_null_company_user_at_company_location_is_flagged_in_strict_mode()
    {
        $company = Company::factory()->create();
        $location = Location::factory()->for($company)->create();
        $user = User::factory()->create(['company_id' => null, 'location_id' => $location->id]);
        $user->companies()->sync([]);

        $this->settings->enableMultipleFullCompanySupport();

        $result = Helper::test_locations_fmcs(true);

        $this->assertContains($user->id, $this->mismatchedIds($result));
    }

    public function test_null_company_user_at_company_location_is_not_flagged_in_floater_mode()
    {
        $company = Company::factory()->create();
        $location = Location::factory()->for($company)->create();
        $user = User::factory()->create(['company_id' => null, 'location_id' => $location->id]);
        $user->companies()->sync([]);

        $this->settings->enableFloaterMode();

        $result = Helper::test_locations_fmcs(true);

        $this->assertNotContains($user->id, $this->mismatchedIds($result));
    }

    public function test_location_id_option_scopes_check_to_single_location()
    {
        [$companyA, $companyB] = Company::factory()->count(2)->create();
        $locationA = Location::factory()->for($companyA)->create();
        $locationB = Location::factory()->for($companyB)->create();
        $assetA = $this->assetAt($locationA, ['company_id' => $companyB->id]); // mismatch at A
        $assetB = $this->assetAt($locationB, ['company_id' => $companyA->id]); // mismatch at B

        $this->settings->enableMultipleFullCompanySupport();

        $result = Helper::test_locations_fmcs(true, $locationA->id);

        $this->assertContains($assetA->id, $this->mismatchedIds($result));
        $this->assertNotContains($assetB->id, $this->mismatchedIds($result));
    }
}
