<?php

use App\Helpers\Helper;
use App\Models\Asset;
use App\Models\Company;
use App\Models\Location;
use App\Models\User;

function mismatchedIds(array $mismatched): array
{
    return array_column($mismatched, 1); // column 1 is the item ID
}

function assetAt(Location $location, array $attrs = []): Asset
{
    // Pin both location_id and rtd_location_id to prevent AssetFactory from
    // generating a random rtd_location with no company that would cause false mismatches.
    return Asset::factory()->create(array_merge([
        'location_id' => $location->id,
        'rtd_location_id' => $location->id,
    ], $attrs));
}

test('item at location with same company is not flagged', function () {
    $company = Company::factory()->create();
    $location = Location::factory()->for($company)->create();
    $asset = assetAt($location, ['company_id' => $company->id]);

    $this->settings->enableMultipleFullCompanySupport();

    $result = Helper::test_locations_fmcs(true);

    expect(mismatchedIds($result))->not->toContain($asset->id);
});

test('item at location with different company is flagged', function () {
    [$companyA, $companyB] = Company::factory()->count(2)->create();
    $location = Location::factory()->for($companyA)->create();
    $asset = assetAt($location, ['company_id' => $companyB->id]);

    $this->settings->enableMultipleFullCompanySupport();

    $result = Helper::test_locations_fmcs(true);

    expect(mismatchedIds($result))->toContain($asset->id);
});

test('null company item at company location is flagged in strict mode', function () {
    $company = Company::factory()->create();
    $location = Location::factory()->for($company)->create();
    $asset = assetAt($location, ['company_id' => null]);

    $this->settings->enableMultipleFullCompanySupport();

    $result = Helper::test_locations_fmcs(true);

    expect(mismatchedIds($result))->toContain($asset->id);
});

test('null company item at company location is not flagged in floater mode', function () {
    $company = Company::factory()->create();
    $location = Location::factory()->for($company)->create();
    $asset = assetAt($location, ['company_id' => null]);

    $this->settings->enableFloaterMode();

    $result = Helper::test_locations_fmcs(true);

    expect(mismatchedIds($result))->not->toContain($asset->id);
});

test('company item at null company location is flagged in strict mode', function () {
    $company = Company::factory()->create();
    $location = Location::factory()->create(['company_id' => null]);
    $asset = assetAt($location, ['company_id' => $company->id]);

    $this->settings->enableMultipleFullCompanySupport();

    $result = Helper::test_locations_fmcs(true);

    expect(mismatchedIds($result))->toContain($asset->id);
});

test('company item at null company location is not flagged in floater mode', function () {
    $company = Company::factory()->create();
    $location = Location::factory()->create(['company_id' => null]);
    $asset = assetAt($location, ['company_id' => $company->id]);

    $this->settings->enableFloaterMode();

    $result = Helper::test_locations_fmcs(true);

    expect(mismatchedIds($result))->not->toContain($asset->id);
});

test('null company item at null company location is never flagged', function () {
    $location = Location::factory()->create(['company_id' => null]);
    $asset = assetAt($location, ['company_id' => null]);

    $this->settings->enableMultipleFullCompanySupport();

    $result = Helper::test_locations_fmcs(true);

    expect(mismatchedIds($result))->not->toContain($asset->id);
});

test('user at location with matching company is not flagged', function () {
    $company = Company::factory()->create();
    $location = Location::factory()->for($company)->create();
    $user = User::factory()->create(['location_id' => $location->id]);
    $user->companies()->sync([$company->id]);

    $this->settings->enableMultipleFullCompanySupport();

    $result = Helper::test_locations_fmcs(true);

    expect(mismatchedIds($result))->not->toContain($user->id);
});

test('user at location with different company is flagged', function () {
    [$companyA, $companyB] = Company::factory()->count(2)->create();
    $location = Location::factory()->for($companyA)->create();
    $user = User::factory()->create(['location_id' => $location->id]);
    $user->companies()->sync([$companyB->id]);

    $this->settings->enableMultipleFullCompanySupport();

    $result = Helper::test_locations_fmcs(true);

    expect(mismatchedIds($result))->toContain($user->id);
});

test('null company user at company location is flagged in strict mode', function () {
    $company = Company::factory()->create();
    $location = Location::factory()->for($company)->create();
    $user = User::factory()->withoutCompany()->create(['location_id' => $location->id]);
    $user->companies()->sync([]);

    $this->settings->enableMultipleFullCompanySupport();

    $result = Helper::test_locations_fmcs(true);

    expect(mismatchedIds($result))->toContain($user->id);
});

test('null company user at company location is not flagged in floater mode', function () {
    $company = Company::factory()->create();
    $location = Location::factory()->for($company)->create();
    $user = User::factory()->withoutCompany()->create(['location_id' => $location->id]);
    $user->companies()->sync([]);

    $this->settings->enableFloaterMode();

    $result = Helper::test_locations_fmcs(true);

    expect(mismatchedIds($result))->not->toContain($user->id);
});

test('location id option scopes check to single location', function () {
    [$companyA, $companyB] = Company::factory()->count(2)->create();
    $locationA = Location::factory()->for($companyA)->create();
    $locationB = Location::factory()->for($companyB)->create();
    $assetA = assetAt($locationA, ['company_id' => $companyB->id]); // mismatch at A
    $assetB = assetAt($locationB, ['company_id' => $companyA->id]); // mismatch at B

    $this->settings->enableMultipleFullCompanySupport();

    $result = Helper::test_locations_fmcs(true, $locationA->id);

    expect(mismatchedIds($result))->toContain($assetA->id);
    expect(mismatchedIds($result))->not->toContain($assetB->id);
});
