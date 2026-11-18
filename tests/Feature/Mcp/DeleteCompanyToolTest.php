<?php

use App\Models\Company;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->deleteCompanies()->create());
});

function handle(array $args = []): ResponseFactory
{
    return handle(new Request($args));
}

test('deletes company by id', function () {
    $company = Company::factory()->create();

    $content = handle(['id' => $company->id])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertSoftDeleted('companies', ['id' => $company->id]);
});

test('deletes company by name', function () {
    $company = Company::factory()->create(['name' => 'Delete By Name Corp']);

    $content = handle(['name' => 'Delete By Name Corp'])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertSoftDeleted('companies', ['id' => $company->id]);
});

test('response includes name', function () {
    $company = Company::factory()->create(['name' => 'Named Company']);

    $content = handle(['id' => $company->id])->getStructuredContent();

    expect($content['name'])->toEqual('Named Company');
});

test('returns error when not found', function () {
    expect(handle(['id' => 999999])->responses()->first()->isError())->toBeTrue();
});

test('returns error when user lacks permission', function () {
    $company = Company::factory()->create();
    $this->actingAs(User::factory()->create());

    expect(handle(['id' => $company->id])->responses()->first()->isError())->toBeTrue();
    $this->assertNotSoftDeleted('companies', ['id' => $company->id]);
});
