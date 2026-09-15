<?php

namespace Tests\Feature\Users\Api;

use App\Models\Company;
use App\Models\License;
use App\Models\LicenseSeat;
use App\Models\User;
use Tests\TestCase;

class UserLicensesAuthorizationTest extends TestCase
{
    public function test_licenses_requires_view_users_permission(): void
    {
        $target = User::factory()->create();

        $this->actingAsForApi(User::factory()->viewLicenses()->create())
            ->getJson(route('api.users.licenselist', $target))
            ->assertForbidden();
    }

    public function test_licenses_requires_view_licenses_permission(): void
    {
        $target = User::factory()->create();

        $this->actingAsForApi(User::factory()->viewUsers()->create())
            ->getJson(route('api.users.licenselist', $target))
            ->assertForbidden();
    }

    public function test_licenses_blocks_cross_company_user_target_under_fmcs(): void
    {
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $admin = User::factory()->viewUsers()->viewLicenses()->forCompany($companyA)->create();
        $target = User::factory()->forCompany($companyB)->create();
        $license = License::factory()->create(['company_id' => $companyB->id, 'name' => 'ProbeCrossCompanyLicense']);
        LicenseSeat::factory()->for($license)->create(['assigned_to' => $target->id]);

        $response = $this->actingAsForApi($admin)
            ->getJson(route('api.users.licenselist', $target));

        // Either 403 (authorize on $user throws) or a JSON error response.
        // Either way, no license row for a cross-company seat may appear.
        $body = $response->getContent();
        $this->assertStringNotContainsString($license->name, $body);
        $this->assertStringNotContainsString('"total":1', $body);
    }

    public function test_licenses_returns_seats_for_same_company_target_under_fmcs(): void
    {
        $this->settings->enableMultipleFullCompanySupport();

        $company = Company::factory()->create();
        $admin = User::factory()->viewUsers()->viewLicenses()->forCompany($company)->create();
        $target = User::factory()->forCompany($company)->create();
        $license = License::factory()->create(['company_id' => $company->id]);
        LicenseSeat::factory()->for($license)->create(['assigned_to' => $target->id]);

        $this->actingAsForApi($admin)
            ->getJson(route('api.users.licenselist', $target))
            ->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonPath('rows.0.name', e($license->name));
    }
}
