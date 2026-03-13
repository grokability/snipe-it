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
    public function testRequiresPermission()
    {
        $reportTemplate = ReportTemplate::factory()->create();

        $this->actingAs(User::factory()->create())
            ->post(route('report-templates.destroy', $reportTemplate->id))
            ->assertStatus(302);

        $this->assertModelExists($reportTemplate);
    }

    public function testCannotDeleteAnotherUsersReportTemplate()
    {
        $reportTemplate = ReportTemplate::factory()->create();

        $this->actingAs(User::factory()->canViewReports()->create())
            ->delete(route('report-templates.destroy', $reportTemplate->id))
            ->assertStatus(302);

        $this->assertModelExists($reportTemplate);
    }

    public function testCanDeleteAReportTemplate()
    {
        $user = User::factory()->canViewReports()->create();
        $reportTemplate = ReportTemplate::factory()->for($user, 'creator')->create();

        $this->actingAs($user)
            ->delete(route('report-templates.destroy', $reportTemplate->id))
            ->assertRedirect(route('reports/custom'));

        $this->assertSoftDeleted($reportTemplate);
    }
}
