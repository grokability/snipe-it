<?php

use App\Mcp\Tools\DeleteCompanyTool;
use App\Models\Company;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->deleteCompanies()->create());
});

function deleteCompanyTool(array $args = []): ResponseFactory
{
    return (new DeleteCompanyTool)->handle(new Request($args));
}

test('deletes company by id', function () {
    $company = Company::factory()->create();

    $content = deleteCompanyTool(['id' => $company->id])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertSoftDeleted('companies', ['id' => $company->id]);
});

test('deletes company by name', function () {
    $company = Company::factory()->create(['name' => 'Delete By Name Corp']);

    $content = deleteCompanyTool(['name' => 'Delete By Name Corp'])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertSoftDeleted('companies', ['id' => $company->id]);
});

test('response includes name', function () {
    $company = Company::factory()->create(['name' => 'Named Company']);

    $content = deleteCompanyTool(['id' => $company->id])->getStructuredContent();

    expect($content['name'])->toEqual('Named Company');
});

test('returns error when not found', function () {
    expect(deleteCompanyTool(['id' => 999999])->responses()->first()->isError())->toBeTrue();
});

test('returns error when user lacks permission', function () {
    $company = Company::factory()->create();
    $this->actingAs(User::factory()->create());

    expect(deleteCompanyTool(['id' => $company->id])->responses()->first()->isError())->toBeTrue();
    $this->assertNotSoftDeleted('companies', ['id' => $company->id]);
});
