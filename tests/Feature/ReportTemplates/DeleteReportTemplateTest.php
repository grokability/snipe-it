<?php

use App\Models\ReportTemplate;
use App\Models\User;

test('requires permission', function () {
    $reportTemplate = ReportTemplate::factory()->create();

    $this->actingAs(User::factory()->create())
        ->post(route('report-templates.destroy', $reportTemplate->id))
        ->assertRedirect(route('reports/custom'));

    $this->assertNotSoftDeleted($reportTemplate);
});

test('cannot delete another users report template', function () {
    $reportTemplate = ReportTemplate::factory()->create();

    $this->actingAs(User::factory()->canViewReports()->create())
        ->delete(route('report-templates.destroy', $reportTemplate->id))
        ->assertRedirect(route('reports/custom'))
        ->assertSessionHas('error', trans('general.generic_model_not_found', ['model' => 'report template']));

    $this->assertNotSoftDeleted($reportTemplate);
});

test('cannot delete another users shared report template', function () {
    $reportTemplate = ReportTemplate::factory()->shared()->create();

    $this->actingAs(User::factory()->canViewReports()->create())
        ->delete(route('report-templates.destroy', $reportTemplate->id))
        ->assertRedirect(route('report-templates.show', $reportTemplate->id))
        ->assertSessionHas('error', trans('general.generic_model_not_found', ['model' => 'report template']));

    $this->assertNotSoftDeleted($reportTemplate);
});

test('can delete a report template', function () {
    $user = User::factory()->canViewReports()->create();
    $reportTemplate = ReportTemplate::factory()->for($user, 'creator')->create();

    $this->actingAs($user)
        ->delete(route('report-templates.destroy', $reportTemplate->id))
        ->assertRedirect(route('reports/custom'));

    $this->assertSoftDeleted($reportTemplate);
});
