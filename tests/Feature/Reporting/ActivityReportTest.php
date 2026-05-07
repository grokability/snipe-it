<?php

namespace Tests\Feature\Reporting;

use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\Company;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class ActivityReportTest extends TestCase
{
    public function test_requires_permission_to_view_activity()
    {
        $this->actingAsForApi(User::factory()->create())
            ->getJson(route('api.activity.index'))
            ->assertForbidden();
    }

    public function test_can_view_activity_if_item_is_given_and_user_has_permissions()
    {
        $asset = Asset::factory()->create();
        $this->actingAsForApi(User::factory()->viewAssets()->create())
            ->getJson(route('api.activity.index',
                [
                    'item_type' => 'asset',
                    'item_id' => $asset->id,
                ]))
            ->assertOk()
            ->assertJsonStructure([
                'rows',
            ])
            ->assertJson(fn (AssertableJson $json) => $json->has('rows', 1)->etc());
    }

    public function test_can_view_activity_if_target_is_given_and_user_has_permissions()
    {

        $user = User::factory()->create();
        $user->update([
            'first_name' => 'Test Update',
        ]);
        $user->update([
            'first_name' => 'Test Update Again',
        ]);

        $this->actingAsForApi(User::factory()->viewUsers()->create())
            ->getJson(route('api.activity.index',
                [
                    'target_type' => 'user',
                    'target_id' => $user->id,
                ]))
            ->assertOk()
            ->assertJsonStructure([
                'rows',
            ])
            ->assertJson(fn (AssertableJson $json) => $json->has('rows', 2)->etc());
    }

    public function test_records_are_scoped_to_company_when_multiple_company_support_enabled()
    {
        // $this->markTestIncomplete('This test returns strange results. Need to figure out why.');
        $this->settings->enableMultipleFullCompanySupport();

        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();

        $superUser = User::factory()->superuser()->make();

        $userInCompanyA = User::factory()
            ->viewUsers()
            ->viewAssets()
            ->canViewReports()
            ->create(['company_id' => $companyA->id]);

        $userInCompanyB = User::factory()
            ->viewUsers()
            ->viewAssets()
            ->canViewReports()
            ->create(['company_id' => $companyB->id]);

        Asset::factory()->count(5)->create(['company_id' => $companyA->id]);
        Asset::factory()->count(4)->create(['company_id' => $companyB->id]);
        Asset::factory()->count(3)->create();

        Actionlog::factory()->userUpdated()->count(5)->create(['company_id' => $companyA->id]);
        Actionlog::factory()->userUpdated()->count(4)->create(['company_id' => $companyB->id]);
        Actionlog::factory()->userUpdated()->count(3)->create(['company_id' => $companyB->id]);

        // I don't love this, since it doesn't test that we're actually storing the company ID appropriately
        // but it's better than what we had
        $this->actingAsForApi($userInCompanyA)
            ->getJson(route('api.activity.index', [
                'action_type' => 'update',
            ]))
            ->assertOk()
            ->assertJsonStructure([
                'rows',
            ])
            ->assertJson(fn (AssertableJson $json) => $json->has('rows', 5)->etc());

        $this->actingAsForApi($userInCompanyB)
            ->getJson(
                route('api.activity.index', [
                    'action_type' => 'update',
                ]))
            ->assertOk()
            ->assertJsonStructure([
                'rows',
            ])
            ->assertJson(fn (AssertableJson $json) => $json->has('rows', 7)->etc());

    }
}
