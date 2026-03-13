<?php

namespace Tests\Feature\Reporting;

use App\Models\Asset;
use App\Models\Company;
use App\Models\User;
use App\Models\Actionlog;
use Database\Factories\ActionlogFactory;
use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Testing\TestResponse;
use League\Csv\Reader;
use PHPUnit\Framework\Assert;
use Tests\TestCase;

class ActivityReportTest extends TestCase
{
    public function testRequiresPermissionToViewActivity()
    {
        $this->actingAsForApi(User::factory()->create())
            ->getJson(route('api.activity.index'))
            ->assertForbidden();
    }

    public function testRecordsAreScopedToCompanyWhenMultipleCompanySupportEnabled()
    {
        $this->markTestIncomplete('This test returns strange results. Need to figure out why.');
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
        $response = $this->actingAsForApi($userInCompanyA)
            ->getJson(route('api.activity.index'))
            ->assertOk()
            ->assertJsonStructure([
                'rows',
            ])
            ->assertJson(fn(AssertableJson $json) => $json->has('rows', 5)->etc());


        $this->actingAsForApi($userInCompanyB)
            ->getJson(
                route('api.activity.index'))
            ->assertOk()
            ->assertJsonStructure([
                'rows',
            ])
            ->assertJson(fn(AssertableJson $json) => $json->has('rows', 7)->etc());





    }

}
