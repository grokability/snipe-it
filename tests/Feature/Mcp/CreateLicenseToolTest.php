<?php

use App\Mcp\Tools\CreateLicenseTool;
use App\Models\Category;
use App\Models\LicenseSeat;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->createLicenses()->create());
});

function createLicenseTool(array $args): ResponseFactory
{
    return (new CreateLicenseTool)->handle(new Request($args));
}

test('creates license with required fields', function () {
    $category = Category::factory()->create(['category_type' => 'license']);

    $content = createLicenseTool([
        'name' => 'Test License',
        'seats' => 5,
        'category_id' => $category->id,
    ])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertDatabaseHas('licenses', ['name' => 'Test License', 'seats' => 5]);
});

test('response includes id name seats and category', function () {
    $category = Category::factory()->create(['category_type' => 'license']);

    $content = createLicenseTool([
        'name' => 'Response License',
        'seats' => 10,
        'category_id' => $category->id,
    ])->getStructuredContent();

    expect($content)->toHaveKey('id');
    expect($content['name'])->toEqual('Response License');
    expect($content['seats'])->toEqual(10);
    expect($content['category_id'])->toEqual($category->id);
});

test('creates license seats automatically', function () {
    $category = Category::factory()->create(['category_type' => 'license']);

    $content = createLicenseTool([
        'name' => 'Seat Auto License',
        'seats' => 3,
        'category_id' => $category->id,
    ])->getStructuredContent();

    $licenseId = $content['id'];
    expect(LicenseSeat::where('license_id', $licenseId)->count())->toEqual(3);
});

test('creates license with optional fields', function () {
    $category = Category::factory()->create(['category_type' => 'license']);

    createLicenseTool([
        'name' => 'Full License',
        'seats' => 2,
        'category_id' => $category->id,
        'serial' => 'SN-FULL-001',
        'license_name' => 'Acme Corp',
        'license_email' => 'admin@acme.com',
        'purchase_date' => '2024-01-15',
        'purchase_cost' => 299.99,
        'expiration_date' => '2025-01-15',
        'maintained' => true,
        'reassignable' => false,
        'notes' => 'Important license',
    ]);

    $this->assertDatabaseHas('licenses', [
        'name' => 'Full License',
        'serial' => 'SN-FULL-001',
        'license_name' => 'Acme Corp',
        'license_email' => 'admin@acme.com',
    ]);
});

test('returns error when name missing', function () {
    $category = Category::factory()->create(['category_type' => 'license']);

    expect(createLicenseTool([
        'seats' => 5,
        'category_id' => $category->id,
    ])->responses()->first()->isError())->toBeTrue();
});

test('returns error when seats missing', function () {
    $category = Category::factory()->create(['category_type' => 'license']);

    expect(createLicenseTool([
        'name' => 'No Seats',
        'category_id' => $category->id,
    ])->responses()->first()->isError())->toBeTrue();
});

test('returns error when category missing', function () {
    expect(createLicenseTool([
        'name' => 'No Category',
        'seats' => 5,
    ])->responses()->first()->isError())->toBeTrue();
});

test('returns error when category does not exist', function () {
    expect(createLicenseTool([
        'name' => 'Bad Category',
        'seats' => 5,
        'category_id' => 999999,
    ])->responses()->first()->isError())->toBeTrue();
});

test('returns error when user lacks permission', function () {
    $this->actingAs(User::factory()->create());
    $category = Category::factory()->create(['category_type' => 'license']);

    expect(createLicenseTool([
        'name' => 'Blocked License',
        'seats' => 5,
        'category_id' => $category->id,
    ])->responses()->first()->isError())->toBeTrue();

    $this->assertDatabaseMissing('licenses', ['name' => 'Blocked License']);
});
