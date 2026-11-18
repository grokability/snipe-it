<?php

use App\Models\Company;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->viewCompanies()->create());
});

function handle(array $args = []): ResponseFactory
{
    return handle(new Request($args));
}

test('returns company by id', function () {
    $company = Company::factory()->create(['name' => 'Lookup By ID Corp']);

    $content = handle(['id' => $company->id])->getStructuredContent();

    expect($content['id'])->toEqual($company->id);
    expect($content['name'])->toEqual('Lookup By ID Corp');
});

test('returns company by name', function () {
    $company = Company::factory()->create(['name' => 'Lookup By Name Corp']);

    $content = handle(['name' => 'Lookup By Name Corp'])->getStructuredContent();

    expect($content['id'])->toEqual($company->id);
    expect($content['name'])->toEqual('Lookup By Name Corp');
});

test('returns error when no identifier provided', function () {
    expect(handle()->responses()->first()->isError())->toBeTrue();
});

test('returns error when not found', function () {
    expect(handle(['id' => 999999])->responses()->first()->isError())->toBeTrue();
});

test('returns error when user lacks permission', function () {
    $company = Company::factory()->create();
    $this->actingAs(User::factory()->create());

    expect(handle(['id' => $company->id])->responses()->first()->isError())->toBeTrue();
});
