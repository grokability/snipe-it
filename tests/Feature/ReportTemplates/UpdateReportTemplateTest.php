<?php

namespace Tests\Feature\ReportTemplates;

use App\Models\ReportTemplate;
use App\Models\User;
use PHPUnit\Framework\Attributes\Group;
use Tests\Concerns\TestsPermissionsRequirement;
use Tests\TestCase;

#[Group('custom-reporting')]
class UpdateReportTemplateTest extends TestCase implements TestsPermissionsRequirement
{
    public function testRequiresPermission()
    {
        $this->actingAs(User::factory()->create())
            ->post(route('report-templates.update', ReportTemplate::factory()->create()))
            ->assertStatus(302);
    }

    public function testCannotUpdateAnotherUsersReportTemplate()
    {
        $this->actingAs(User::factory()->canViewReports()->create())
            ->post(route('report-templates.update', ReportTemplate::factory()->create()))
            ->assertStatus(302);
    }

    public function testUpdatingReportTemplateRequiresValidFields()
    {
        $user = User::factory()->canViewReports()->create();

        $reportTemplate = ReportTemplate::factory()->for($user, 'creator')->create();

        $this->actingAs($user)
            ->post(route('report-templates.update', $reportTemplate), [
                //
            ])
            ->assertSessionHasErrors([
                'name' => 'The name field is required.',
            ]);
    }

    public function testCanUpdateAReportTemplate()
    {
        $user = User::factory()->canViewReports()->create();

        $reportTemplate = ReportTemplate::factory()->for($user, 'creator')->create([
            'name' => 'Original Name',
            'options' => [
                'id' => 1,
                'category' => 1,
                'by_category_id' => 2,
                'company' => 1,
                'by_company_id' => [1, 2],
            ],
        ]);

        $this->actingAs($user)
            ->post(route('report-templates.update', $reportTemplate), [
                'name' => 'Updated Name',
                'id' => 1,
                'company' => 1,
                'by_company_id' => [3],
            ]);

        $reportTemplate->refresh();
        $this->assertEquals('Updated Name', $reportTemplate->name);
        $this->assertEquals(1, $reportTemplate->checkmarkValue('id'));
        $this->assertEquals(0, $reportTemplate->checkmarkValue('category'));
        $this->assertEquals([], $reportTemplate->selectValues('by_category_id'));
        $this->assertEquals(1, $reportTemplate->checkmarkValue('company'));
        $this->assertEquals([3], $reportTemplate->selectValues('by_company_id'));
    }
}
