<?php

use App\Mcp\Tools\ShowCompanyTool;
use App\Models\Company;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->viewCompanies()->create());
});

function showCompanyTool(array $args = []): ResponseFactory
{
    return (new ShowCompanyTool)->handle(new Request($args));
}

test('returns company by id', function () {
    $company = Company::factory()->create(['name' => 'Lookup By ID Corp']);

    $content = showCompanyTool(['id' => $company->id])->getStructuredContent();

    expect($content['id'])->toEqual($company->id);
    expect($content['name'])->toEqual('Lookup By ID Corp');
});

test('returns company by name', function () {
    $company = Company::factory()->create(['name' => 'Lookup By Name Corp']);

    $content = showCompanyTool(['name' => 'Lookup By Name Corp'])->getStructuredContent();

    expect($content['id'])->toEqual($company->id);
    expect($content['name'])->toEqual('Lookup By Name Corp');
});

test('returns error when no identifier provided', function () {
    expect(showCompanyTool()->responses()->first()->isError())->toBeTrue();
});

test('returns error when not found', function () {
    expect(showCompanyTool(['id' => 999999])->responses()->first()->isError())->toBeTrue();
});

test('returns error when user lacks permission', function () {
    $company = Company::factory()->create();
    $this->actingAs(User::factory()->create());

    expect(showCompanyTool(['id' => $company->id])->responses()->first()->isError())->toBeTrue();
});
