<?php

use App\Models\ReportTemplate;
use App\Models\User;

test('requires permission', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('report-templates.show', ReportTemplate::factory()->create()))
        ->assertRedirectToRoute('reports/custom');
});

test('can load a saved report template', function () {
    $user = User::factory()->canViewReports()->create();
    $reportTemplate = ReportTemplate::factory()->make(['name' => 'My Awesome Template']);
    $user->reportTemplates()->save($reportTemplate);

    $this->actingAs($user)
        ->get(route('report-templates.show', $reportTemplate))
        ->assertOk()
        ->assertViewHas(['template' => function (ReportTemplate $templatePassedToView) use ($reportTemplate) {
            return $templatePassedToView->is($reportTemplate);
        }]);
});

test('cannot load another users saved report template if not shared', function () {
    $reportTemplate = ReportTemplate::factory()->create();

    $this->actingAs(User::factory()->canViewReports()->create())
        ->get(route('report-templates.show', $reportTemplate))
        ->assertRedirectToRoute('reports/custom')
        ->assertSessionHas('error', trans('general.generic_model_not_found', ['model' => 'report template']));
});

test('can load another users saved report template if shared', function () {
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
});
