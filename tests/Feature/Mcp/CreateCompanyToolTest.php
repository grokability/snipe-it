<?php

use App\Mcp\Tools\CreateCompanyTool;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->createCompanies()->create());
});

function createCompanyTool(array $args = []): ResponseFactory
{
    return (new CreateCompanyTool)->handle(new Request($args));
}

test('creates company with required fields', function () {
    $content = createCompanyTool(['name' => 'Test Company LLC'])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertDatabaseHas('companies', ['name' => 'Test Company LLC']);
});

test('response includes id and name', function () {
    $content = createCompanyTool(['name' => 'Response Company Inc'])->getStructuredContent();

    expect($content)->toHaveKey('id');
    expect($content['name'])->toEqual('Response Company Inc');
});

test('creates with optional fields', function () {
    createCompanyTool([
        'name' => 'Full Company Corp',
        'phone' => '555-1234567',
        'fax' => '555-7654321',
        'email' => 'company@example.com',
    ]);

    $this->assertDatabaseHas('companies', [
        'name' => 'Full Company Corp',
        'phone' => '555-1234567',
        'fax' => '555-7654321',
        'email' => 'company@example.com',
    ]);
});

test('returns error when name missing', function () {
    expect(createCompanyTool([])->responses()->first()->isError())->toBeTrue();
});

test('returns error when user lacks permission', function () {
    $this->actingAs(User::factory()->create());

    expect(createCompanyTool(['name' => 'Blocked Company'])->responses()->first()->isError())->toBeTrue();
    $this->assertDatabaseMissing('companies', ['name' => 'Blocked Company']);
});
