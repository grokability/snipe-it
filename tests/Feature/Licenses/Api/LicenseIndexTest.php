<?php

namespace Tests\Feature\Licenses\Api;

use App\Models\Company;
use App\Models\License;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class LicenseIndexTest extends TestCase
{
    public function test_licenses_index_adheres_to_company_scoping()
    {
        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $licenseA = License::factory()->for($companyA)->create();
        $licenseB = License::factory()->for($companyB)->create();

        $superUser = $companyA->users()->save(User::factory()->superuser()->make());
        $userInCompanyA = $companyA->users()->save(User::factory()->viewLicenses()->make());
        $userInCompanyB = $companyB->users()->save(User::factory()->viewLicenses()->make());

        $this->settings->disableMultipleFullCompanySupport();

        $this->actingAsForApi($superUser)
            ->getJson(route('api.licenses.index'))
            ->assertResponseContainsInRows($licenseA)
            ->assertResponseContainsInRows($licenseB);

        $this->actingAsForApi($userInCompanyA)
            ->getJson(route('api.licenses.index'))
            ->assertResponseContainsInRows($licenseA)
            ->assertResponseContainsInRows($licenseB);

        $this->actingAsForApi($userInCompanyB)
            ->getJson(route('api.licenses.index'))
            ->assertResponseContainsInRows($licenseA)
            ->assertResponseContainsInRows($licenseB);

        $this->settings->enableMultipleFullCompanySupport();

        $this->actingAsForApi($superUser)
            ->getJson(route('api.licenses.index'))
            ->assertResponseContainsInRows($licenseA)
            ->assertResponseContainsInRows($licenseB);

        $this->actingAsForApi($userInCompanyA)
            ->getJson(route('api.licenses.index'))
            ->assertResponseContainsInRows($licenseA)
            ->assertResponseDoesNotContainInRows($licenseB);

        $this->actingAsForApi($userInCompanyB)
            ->getJson(route('api.licenses.index'))
            ->assertResponseDoesNotContainInRows($licenseA)
            ->assertResponseContainsInRows($licenseB);
    }

    public function test_returns_result_via_filter()
    {

        License::factory()->create(['name' => 'MY AWESOME LICENSE NAME 1']);
        License::factory()->count(2)->create(['name' => 'MY AWESOME LICENSE NAME 2']);
        License::factory()->count(2)->create(['name' => 'MY AWESOME LICENSE NAME 3']);
        License::factory()->count(2)->create(['name' => 'MY TERRIBLE LICENSE NAME']);

        $this->actingAsForApi(User::factory()->viewLicenses()->create())
            ->getJson(route('api.licenses.index', [
                'filter' => '{"name":"AWESOME LICENSE NAME"}',
            ]))
            ->assertOk()
            ->assertJsonStructure([
                'total',
                'rows',
            ])
            ->assertJson(fn (AssertableJson $json) => $json->has('rows', 5)->etc());
    }

    public function test_returns_result_via_filter_for_manufacturer()
    {

        License::factory()->count(5)->office()->create();
        License::factory()->count(3)->indesign()->create();
        License::factory()->count(3)->acrobat()->create();

        $this->actingAsForApi(User::factory()->viewLicenses()->create())
            ->getJson(route('api.licenses.index', [
                'filter' => '{"manufacturer":"adobe"}',
            ]))
            ->assertOk()
            ->assertJsonStructure([
                'total',
                'rows',
            ])
            ->assertJson(fn (AssertableJson $json) => $json->has('rows', 6)->etc());

        $this->actingAsForApi(User::factory()->viewLicenses()->create())
            ->getJson(route('api.licenses.index', [
                'filter' => '{"manufacturer":"blah"}',
            ]))
            ->assertOk()
            ->assertJsonStructure([
                'total',
                'rows',
            ])
            ->assertJson(fn (AssertableJson $json) => $json->has('rows', 0)->etc());

        $this->actingAsForApi(User::factory()->viewLicenses()->create())
            ->getJson(route('api.licenses.index', [
                'filter' => '{"manufacturer":"microsoft"}',
            ]))
            ->assertOk()
            ->assertJsonStructure([
                'total',
                'rows',
            ])
            ->assertJson(fn (AssertableJson $json) => $json->has('rows', 5)->etc());

        $this->actingAsForApi(User::factory()->viewLicenses()->create())
            ->getJson(route('api.licenses.index', [
                'search' => 'adobe',
            ]))
            ->assertOk()
            ->assertJsonStructure([
                'total',
                'rows',
            ])
            ->assertJson(fn (AssertableJson $json) => $json->has('rows', 6)->etc());
    }
}
