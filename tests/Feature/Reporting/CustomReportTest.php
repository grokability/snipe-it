<?php

namespace Tests\Feature\Reporting;

use App\Models\Asset;
use App\Models\Company;
use App\Models\ReportTemplate;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Testing\TestResponse;
use League\Csv\Reader;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\Group;
use Tests\Concerns\TestsPermissionsRequirement;
use Tests\TestCase;

#[Group('custom-reporting')]
class CustomReportTest extends TestCase implements TestsPermissionsRequirement
{
    protected function setUp(): void
    {
        parent::setUp();

        TestResponse::macro(
            'assertSeeTextInStreamedResponse',
            function (string $needle) {
                Assert::assertTrue(
                    collect(Reader::createFromString($this->streamedContent())->getRecords())
                        ->pluck(0)
                        ->contains($needle)
                );

                return $this;
            }
        );

        TestResponse::macro(
            'assertDontSeeTextInStreamedResponse',
            function (string $needle) {
                Assert::assertFalse(
                    collect(Reader::createFromString($this->streamedContent())->getRecords())
                        ->pluck(0)
                        ->contains($needle)
                );

                return $this;
            }
        );
    }

    public function testRequiresPermission()
    {
        $this->actingAs(User::factory()->create())
            ->get(route('reports/custom'))
            ->assertForbidden();
    }

    public function testCanLoadCustomReportPage()
    {
        $this->actingAs(User::factory()->canViewReports()->create())
            ->get(route('reports/custom'))
            ->assertOk()
            ->assertViewHas([
                'template' => function (ReportTemplate $template) {
                    // the view should have an empty report by default
                    return $template->exists() === false;
                }
            ]);
    }

    public function testSavedTemplatesOnPageAreScopedToTheUser()
    {
        // Given there is a saved template for one user
        ReportTemplate::factory()->create(['name' => 'Report A']);

        // When loading reports/custom while acting as another user that also has a saved template
        $user = User::factory()->canViewReports()
            ->has(ReportTemplate::factory(['name' => 'Report B']))
            ->create();

        // The user should not see the other user's template (in view as 'report_templates')
        $this->actingAs($user)
            ->get(route('reports/custom'))
            ->assertViewHas([
                'report_templates' => function (Collection $reports) {
                    return $reports->pluck('name')->doesntContain('Report A');
                }
            ]);
    }


    public function testCustomAssetReport()
    {
        Asset::factory()->create(['name' => 'Asset A']);
        Asset::factory()->create(['name' => 'Asset B']);

        $this->actingAs(User::factory()->canViewReports()->create())
            ->post('reports/custom', [
                'asset_name' => '1',
                'asset_tag' => '1',
                'serial' => '1',
            ])->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=utf-8')
            ->assertSeeTextInStreamedResponse('Asset A')
            ->assertSeeTextInStreamedResponse('Asset B');
    }

    public function testCustomAssetReportAdheresToCompanyScoping()
    {
        [$companyA, $companyB] = Company::factory()->count(2)->create();

        Asset::factory()->for($companyA)->create(['name' => 'Asset A']);
        Asset::factory()->for($companyB)->create(['name' => 'Asset B']);

        $superUser = $companyA->users()->save(User::factory()->superuser()->make());
        $userInCompanyA = $companyA->users()->save(User::factory()->canViewReports()->make());
        $userInCompanyB = $companyB->users()->save(User::factory()->canViewReports()->make());

        $this->settings->disableMultipleFullCompanySupport();

        $this->actingAs($superUser)
            ->post('reports/custom', ['asset_name' => '1', 'asset_tag' => '1', 'serial' => '1'])
            ->assertSeeTextInStreamedResponse('Asset A')
            ->assertSeeTextInStreamedResponse('Asset B');

        $this->actingAs($userInCompanyA)
            ->post('reports/custom', ['asset_name' => '1', 'asset_tag' => '1', 'serial' => '1'])
            ->assertSeeTextInStreamedResponse('Asset A')
            ->assertSeeTextInStreamedResponse('Asset B');

        $this->actingAs($userInCompanyB)
            ->post('reports/custom', ['asset_name' => '1', 'asset_tag' => '1', 'serial' => '1'])
            ->assertSeeTextInStreamedResponse('Asset A')
            ->assertSeeTextInStreamedResponse('Asset B');

        $this->settings->enableMultipleFullCompanySupport();

        $this->actingAs($superUser)
            ->post('reports/custom', ['asset_name' => '1', 'asset_tag' => '1', 'serial' => '1'])
            ->assertSeeTextInStreamedResponse('Asset A')
            ->assertSeeTextInStreamedResponse('Asset B');

        $this->actingAs($userInCompanyA)
            ->post('reports/custom', ['asset_name' => '1', 'asset_tag' => '1', 'serial' => '1'])
            ->assertSeeTextInStreamedResponse('Asset A')
            ->assertDontSeeTextInStreamedResponse('Asset B');

        $this->actingAs($userInCompanyB)
            ->post('reports/custom', ['asset_name' => '1', 'asset_tag' => '1', 'serial' => '1'])
            ->assertDontSeeTextInStreamedResponse('Asset A')
            ->assertSeeTextInStreamedResponse('Asset B');
    }

    public function testCanLimitAssetsByLastCheckIn()
    {
        Asset::factory()->create(['name' => 'Asset A', 'last_checkin' => '2023-08-01']);
        Asset::factory()->create(['name' => 'Asset B', 'last_checkin' => '2023-08-02']);
        Asset::factory()->create(['name' => 'Asset C', 'last_checkin' => '2023-08-03']);
        Asset::factory()->create(['name' => 'Asset D', 'last_checkin' => '2023-08-04']);
        Asset::factory()->create(['name' => 'Asset E', 'last_checkin' => '2023-08-05']);

        $this->actingAs(User::factory()->canViewReports()->create())
            ->post('reports/custom', [
                'asset_name' => '1',
                'asset_tag' => '1',
                'serial' => '1',
                'checkin_date' => '1',
                'checkin_date_start' => '2023-08-02',
                'checkin_date_end' => '2023-08-04',
            ])->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=utf-8')
            ->assertDontSeeTextInStreamedResponse('Asset A')
            ->assertSeeTextInStreamedResponse('Asset B')
            ->assertSeeTextInStreamedResponse('Asset C')
            ->assertSeeTextInStreamedResponse('Asset D')
            ->assertDontSeeTextInStreamedResponse('Asset E');
    }
}
