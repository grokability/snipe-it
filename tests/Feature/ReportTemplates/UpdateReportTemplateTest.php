<?php

use App\Models\ReportTemplate;
use App\Models\User;

test('requires permission', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('report-templates.update', ReportTemplate::factory()->create()))
        ->assertStatus(302);
});

test('cannot update another users report template', function () {
    $reportTemplate = ReportTemplate::factory()->create(['name' => 'Original']);

    $this->actingAs(User::factory()->canViewReports()->create())
        ->post(route('report-templates.update', $reportTemplate), [
            'name' => 'Changed',
            'options' => $reportTemplate->options,
        ])
        ->assertStatus(302);

    expect($reportTemplate->fresh()->name)->toEqual('Original');
});

test('cannot update another users shared report template', function () {
    $reportTemplate = ReportTemplate::factory()->shared()->create(['name' => 'Original']);

    $this->actingAs(User::factory()->canViewReports()->create())
        ->post(route('report-templates.update', $reportTemplate), [
            'name' => 'Changed',
            'options' => $reportTemplate->options,
        ])
        ->assertStatus(302)
        ->assertSessionHas('error', trans('general.report_not_editable'));

    expect($reportTemplate->fresh()->name)->toEqual('Original');
});

test('updating report template requires valid fields', function () {
    $user = User::factory()->canViewReports()->create();

    $reportTemplate = ReportTemplate::factory()->for($user, 'creator')->create();

    $this->actingAs($user)
        ->post(route('report-templates.update', $reportTemplate), [
            'category' => 1,
        ])
        ->assertSessionHasErrors([
            'name' => 'The name field is required.',
        ]);
});

test('can update a report template', function () {
    $user = User::factory()->canViewReports()->create();

    $reportTemplate = ReportTemplate::factory()->notShared()->for($user, 'creator')->create([
        'name' => 'Original Name',
        'options' => [
            'category' => 1,
            'by_category_id' => 2,
            'company' => 1,
            'by_company_id' => [1, 2],
        ],
    ]);

    $this->actingAs($user)
        ->post(route('report-templates.update', $reportTemplate), [
            'name' => 'Updated Name',
            'company' => 1,
            'by_company_id' => [3],
        ])
        ->assertRedirectToRoute('report-templates.show', $reportTemplate->id);

    $reportTemplate->refresh();
    expect($reportTemplate->is_shared)->toEqual(0);
    expect($reportTemplate->name)->toEqual('Updated Name');
    expect($reportTemplate->checkmarkValue('category'))->toEqual(0);
    expect($reportTemplate->selectValues('by_category_id'))->toEqual([]);
    expect($reportTemplate->checkmarkValue('company'))->toEqual(1);
    expect($reportTemplate->selectValues('by_company_id'))->toEqual([3]);
});

test('can update a report template with the same name', function () {
    $user = User::factory()->canViewReports()->create();

    $reportTemplate = ReportTemplate::factory()->notShared()->for($user, 'creator')->create([
        'name' => 'Original Name',
        'options' => [
            'category' => 1,
            'by_category_id' => 2,
            'company' => 1,
            'by_company_id' => [1, 2],
        ],
    ]);

    $this->actingAs($user)
        ->post(route('report-templates.update', $reportTemplate), [
            'name' => 'Original Name',
            'company' => 1,
            'by_company_id' => [3],
        ])
        ->assertSessionDoesntHaveErrors();
});

test('can share a report template', function () {
    $user = User::factory()->canViewReports()->create();

    $reportTemplate = ReportTemplate::factory()->notShared()->for($user, 'creator')->create([
        'name' => 'Original Name',
        'options' => [
            'category' => 1,
            'by_category_id' => 2,
            'company' => 1,
        ],
    ]);

    $this->actingAs($user)
        ->post(route('report-templates.update', $reportTemplate), [
            'name' => 'Original Name',
            'options' => [
                'category' => 1,
                'by_category_id' => 2,
                'company' => 1,
            ],
            'is_shared' => 1,
        ])
        ->assertRedirectToRoute('report-templates.show', $reportTemplate->id);

    $reportTemplate->refresh();
    expect($reportTemplate->is_shared)->toEqual(1);
    expect($reportTemplate->name)->toEqual('Original Name');
});
