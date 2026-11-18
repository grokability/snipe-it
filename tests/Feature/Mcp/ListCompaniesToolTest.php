<?php

use App\Mcp\Tools\ListCompaniesTool;
use App\Models\Company;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->viewCompanies()->create());
});

function listCompaniesTool(array $args = []): ResponseFactory
{
    return (new ListCompaniesTool)->handle(new Request($args));
}

test('returns total and pagination metadata', function () {
    Company::factory()->count(3)->create();

    $content = listCompaniesTool()->getStructuredContent();

    expect($content['total'])->toBeGreaterThanOrEqual(3);
    expect($content)->toHaveKey('companies');
    expect($content)->toHaveKey('limit');
    expect($content)->toHaveKey('offset');
});

test('each company includes key fields', function () {
    Company::factory()->create();

    $content = listCompaniesTool()->getStructuredContent();

    expect($content['companies'])->not->toBeEmpty();
    $company = $content['companies'][0];
    expect($company)->toHaveKey('id');
    expect($company)->toHaveKey('name');
});

test('limit controls results', function () {
    Company::factory()->count(10)->create();

    $content = listCompaniesTool(['limit' => 3])->getStructuredContent();

    expect($content['companies'])->toHaveCount(3);
    expect($content['total'])->toBeGreaterThan(3);
});

test('returns error when user lacks permission', function () {
    $this->actingAs(User::factory()->create());

    expect(listCompaniesTool()->responses()->first()->isError())->toBeTrue();
});
