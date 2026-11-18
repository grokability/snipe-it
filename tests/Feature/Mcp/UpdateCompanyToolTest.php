<?php

use App\Models\Company;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->editCompanies()->create());
});

function handle(array $args = []): ResponseFactory
{
    return handle(new Request($args));
}

test('updates company by id', function () {
    $company = Company::factory()->create(['name' => 'Original Corp']);

    $content = handle([
        'id' => $company->id,
        'new_name' => 'Updated Corp',
    ])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertDatabaseHas('companies', ['id' => $company->id, 'name' => 'Updated Corp']);
});

test('updates company by name', function () {
    $company = Company::factory()->create(['name' => 'Find By Name Corp']);

    $content = handle([
        'name' => 'Find By Name Corp',
        'new_name' => 'Renamed Corp',
    ])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertDatabaseHas('companies', ['id' => $company->id, 'name' => 'Renamed Corp']);
});

test('renames via new name', function () {
    $company = Company::factory()->create(['name' => 'Before Rename']);

    $content = handle([
        'id' => $company->id,
        'new_name' => 'After Rename',
    ])->getStructuredContent();

    expect($content['name'])->toEqual('After Rename');
    $this->assertDatabaseHas('companies', ['id' => $company->id, 'name' => 'After Rename']);
});

test('returns error when not found', function () {
    expect(handle(['id' => 999999, 'new_name' => 'Ghost Corp'])->responses()->first()->isError())->toBeTrue();
});

test('returns error when no identifier provided', function () {
    expect(handle(['new_name' => 'X'])->responses()->first()->isError())->toBeTrue();
});

test('returns error when user lacks permission', function () {
    $company = Company::factory()->create(['name' => 'Unchanged Corp']);
    $this->actingAs(User::factory()->create());

    expect(handle([
        'id' => $company->id,
        'new_name' => 'Should Not Change',
    ])->responses()->first()->isError())->toBeTrue();

    $this->assertDatabaseHas('companies', ['id' => $company->id, 'name' => 'Unchanged Corp']);
});
