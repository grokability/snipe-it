<?php

namespace Tests\Feature\ReportTemplates;

use App\Models\ReportTemplate;
use App\Models\User;
use PHPUnit\Framework\Attributes\Group;
use Tests\Concerns\TestsPermissionsRequirement;
use Tests\TestCase;

#[Group('custom-reporting')]
class EditReportTemplateTest extends TestCase implements TestsPermissionsRequirement
{
    public function test_requires_permission()
    {
        $this->actingAs(User::factory()->create())
            ->get(route('report-templates.edit', ReportTemplate::factory()->create()))
            ->assertRedirectToRoute('reports/custom');
    }

    public function test_cannot_load_edit_page_for_another_users_report_template()
    {
        $user = User::factory()->canViewReports()->create();
        $reportTemplate = ReportTemplate::factory()->create();

        $this->actingAs($user)
            ->get(route('report-templates.edit', $reportTemplate))
            ->assertRedirectToRoute('reports/custom');
    }

    public function test_cannot_load_edit_page_for_another_users_shared_report_template()
    {
        $user = User::factory()->canViewReports()->create();
        $reportTemplate = ReportTemplate::factory()->shared()->create();

        $this->actingAs($user)
            ->get(route('report-templates.edit', $reportTemplate))
            ->assertRedirectToRoute('report-templates.show', $reportTemplate->id);
    }

    public function test_can_load_edit_report_template_page()
    {
        $user = User::factory()->canViewReports()->create();
        $reportTemplate = ReportTemplate::factory()->for($user, 'creator')->create();

        $this->actingAs($user)
            ->get(route('report-templates.edit', $reportTemplate))
            ->assertOk();
    }
}
