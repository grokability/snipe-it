<?php

namespace Tests\Feature\ReportTemplates;

use App\Models\ReportTemplate;
use App\Models\User;
use PHPUnit\Framework\Attributes\Group;
use Tests\Concerns\TestsPermissionsRequirement;
use Tests\TestCase;

#[Group('custom-reporting')]
class DeleteReportTemplateTest extends TestCase implements TestsPermissionsRequirement
{
    public function test_requires_permission()
    {
        $reportTemplate = ReportTemplate::factory()->create();

        $this->actingAs(User::factory()->create())
            ->post(route('report-templates.destroy', $reportTemplate->id))
            ->assertRedirect(route('reports/custom'));

        $this->assertNotSoftDeleted($reportTemplate);
    }

    public function test_cannot_delete_another_users_report_template()
    {
        $reportTemplate = ReportTemplate::factory()->create();

        $this->actingAs(User::factory()->canViewReports()->create())
            ->delete(route('report-templates.destroy', $reportTemplate->id))
            ->assertRedirect(route('reports/custom'))
            ->assertSessionHas('error', trans('general.generic_model_not_found', ['model' => 'report template']));

        $this->assertNotSoftDeleted($reportTemplate);
    }

    public function test_cannot_delete_another_users_shared_report_template()
    {
        $reportTemplate = ReportTemplate::factory()->shared()->create();

        $this->actingAs(User::factory()->canViewReports()->create())
            ->delete(route('report-templates.destroy', $reportTemplate->id))
            ->assertRedirect(route('report-templates.show', $reportTemplate->id))
            ->assertSessionHas('error', trans('general.generic_model_not_found', ['model' => 'report template']));

        $this->assertNotSoftDeleted($reportTemplate);
    }

    public function test_can_delete_a_report_template()
    {
        $user = User::factory()->canViewReports()->create();
        $reportTemplate = ReportTemplate::factory()->for($user, 'creator')->create();

        $this->actingAs($user)
            ->delete(route('report-templates.destroy', $reportTemplate->id))
            ->assertRedirect(route('reports/custom'));

        $this->assertSoftDeleted($reportTemplate);
    }
}
