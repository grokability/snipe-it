<?php

namespace Tests\Feature\ReportTemplates;

use App\Models\ReportTemplate;
use App\Models\User;
use PHPUnit\Framework\Attributes\Group;
use Tests\Concerns\TestsPermissionsRequirement;
use Tests\TestCase;

#[Group('custom-reporting')]
class ShowReportTemplateTest extends TestCase implements TestsPermissionsRequirement
{
    public function test_requires_permission()
    {
        $this->actingAs(User::factory()->create())
            ->get(route('report-templates.show', ReportTemplate::factory()->create()))
            ->assertRedirectToRoute('reports/custom');
    }

    public function test_can_load_a_saved_report_template()
    {
        $user = User::factory()->canViewReports()->create();
        $reportTemplate = ReportTemplate::factory()->make(['name' => 'My Awesome Template']);
        $user->reportTemplates()->save($reportTemplate);

        $this->actingAs($user)
            ->get(route('report-templates.show', $reportTemplate))
            ->assertOk()
            ->assertViewHas(['template' => function (ReportTemplate $templatePassedToView) use ($reportTemplate) {
                return $templatePassedToView->is($reportTemplate);
            }]);
    }

    public function test_cannot_load_another_users_saved_report_template_if_not_shared()
    {
        $reportTemplate = ReportTemplate::factory()->create();

        $this->actingAs(User::factory()->canViewReports()->create())
            ->get(route('report-templates.show', $reportTemplate))
            ->assertRedirectToRoute('reports/custom')
            ->assertSessionHas('error', trans('general.generic_model_not_found', ['model' => 'report template']));
    }

    public function test_can_load_another_users_saved_report_template_if_shared()
    {
        $user = User::factory()->canViewReports()->create();
        $reportTemplate = ReportTemplate::factory()->shared()->make(['name' => 'My Awesome Template']);
        $user->reportTemplates()->save($reportTemplate);

        $this->actingAs(User::factory()->canViewReports()->create())
            ->get(route('report-templates.show', $reportTemplate))
            ->assertOk()
            ->assertViewHas([
                'template' => function (ReportTemplate $templatePassedToView) use ($reportTemplate) {
                    return $templatePassedToView->is($reportTemplate);
                },
            ]);
    }
}
