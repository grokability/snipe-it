<?php

namespace Tests\Feature\ReportTemplates;

use App\Models\ReportTemplate;
use App\Models\User;
use PHPUnit\Framework\Attributes\Group;
use Tests\Concerns\TestsPermissionsRequirement;
use Tests\TestCase;

#[Group('custom-reporting')]
class StoreReportTemplateTest extends TestCase implements TestsPermissionsRequirement
{
    public function testRequiresPermission()
    {
        $this->actingAs(User::factory()->create())
            ->post(route('report-templates.store'))
            ->assertForbidden();
    }

    public function testSavingReportTemplateRequiresValidFields()
    {
        $this->actingAs(User::factory()->canViewReports()->create())
            ->post(route('report-templates.store'), [
                'name' => '',
            ])
            ->assertSessionHasErrors('name');
    }

    public function testRedirectingAfterValidationErrorRestoresInputs()
    {
        $this->actingAs(User::factory()->canViewReports()->create())
            // start on the custom report page
            ->from(route('reports/custom'))
            ->followingRedirects()
            ->post(route('report-templates.store'), [
                'name' => '',
                // set some values to ensure they are still present
                // when returning to the custom report page.
                'by_company_id' => [2, 3]
            ])->assertViewHas(['template' => function (ReportTemplate $reportTemplate) {
                return data_get($reportTemplate, 'options.by_company_id') === [2, 3];
            }]);
    }

    public function testCanSaveAReportTemplate()
    {
        $user = User::factory()->canViewReports()->create();

        $this->actingAs($user)
            ->post(route('report-templates.store'), [
                'name' => 'My Awesome Template',
                'company' => '1',
                'by_company_id' => ['1', '2'],
            ])
            ->assertRedirect();

        $template = $user->reportTemplates->first(function ($report) {
            return $report->name === 'My Awesome Template';
        });

        $this->assertNotNull($template);
        $this->assertEquals('1', $template->options['company']);
        $this->assertEquals(['1', '2'], $template->options['by_company_id']);
    }
}
