<?php

use App\Models\ReportTemplate;
use App\Models\User;

pest()->group('custom-reporting');

test('requires permission', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('report-templates.edit', ReportTemplate::factory()->create()))
        ->assertRedirectToRoute('reports/custom');
});

test('cannot load edit page for another users report template', function () {
    $user = User::factory()->canViewReports()->create();
    $reportTemplate = ReportTemplate::factory()->create();

    $this->actingAs($user)
        ->get(route('report-templates.edit', $reportTemplate))
        ->assertRedirectToRoute('reports/custom');
});

test('cannot load edit page for another users shared report template', function () {
    $user = User::factory()->canViewReports()->create();
    $reportTemplate = ReportTemplate::factory()->shared()->create();

    $this->actingAs($user)
        ->get(route('report-templates.edit', $reportTemplate))
        ->assertRedirectToRoute('report-templates.show', $reportTemplate->id);
});

test('edit route redirects to show which renders for owner', function () {
    $user = User::factory()->canViewReports()->create();
    $reportTemplate = ReportTemplate::factory()->for($user, 'creator')->create();

    $this->actingAs($user)
        ->get(route('report-templates.edit', $reportTemplate))
        ->assertRedirectToRoute('report-templates.show', $reportTemplate->id);

    $this->actingAs($user)
        ->get(route('report-templates.show', $reportTemplate))
        ->assertOk();
});
