<?php

use App\Mcp\Tools\ShowLicenseTool;
use App\Models\License;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->viewLicenses()->create());
});

function showLicenseTool(array $args): ResponseFactory
{
    return (new ShowLicenseTool)->handle(new Request($args));
}

test('returns license by id', function () {
    $license = License::factory()->create(['name' => 'Test License', 'seats' => 5]);

    $content = showLicenseTool(['id' => $license->id])->getStructuredContent();

    expect($content['id'])->toEqual($license->id);
    expect($content['name'])->toEqual('Test License');
    expect($content['seats'])->toEqual(5);
});

test('returns license by name', function () {
    $license = License::factory()->create(['name' => 'FindByName License']);

    $content = showLicenseTool(['name' => 'FindByName License'])->getStructuredContent();

    expect($content['id'])->toEqual($license->id);
    expect($content['name'])->toEqual('FindByName License');
});

test('response includes seat counts', function () {
    $license = License::factory()->create(['seats' => 3, 'reassignable' => true]);
    $user = User::factory()->create();

    $seat = $license->freeSeat();
    $seat->assigned_to = $user->id;
    $seat->save();

    $content = showLicenseTool(['id' => $license->id])->getStructuredContent();

    expect($content['seats'])->toEqual(3);
    expect($content['assigned_seats'])->toEqual(1);
    expect($content['free_seats'])->toEqual(2);
});

test('response includes key fields', function () {
    // License::serial is masked (--------) unless the acting user
    // has the licenses.keys gate. The default viewLicenses() helper
    // used in setUp doesn't include it, so this test adds it.
    $this->actingAs(User::factory()->viewLicenses()->viewKeysLicenses()->create());

    $license = License::factory()->create([
        'name' => 'Full Details License',
        'serial' => 'SN-12345',
        'license_name' => 'Acme Corp',
        'license_email' => 'legal@acme.com',
    ]);

    $content = showLicenseTool(['id' => $license->id])->getStructuredContent();

    expect($content)->toHaveKey('serial');
    expect($content)->toHaveKey('license_name');
    expect($content)->toHaveKey('license_email');
    expect($content)->toHaveKey('maintained');
    expect($content)->toHaveKey('reassignable');
    expect($content)->toHaveKey('category');
    expect($content)->toHaveKey('category_id');
    expect($content['serial'])->toEqual('SN-12345');
    expect($content['license_name'])->toEqual('Acme Corp');
});

test('returns error when no identifier provided', function () {
    expect(showLicenseTool([])->responses()->first()->isError())->toBeTrue();
});

test('returns error when not found', function () {
    expect(showLicenseTool(['id' => 999999])->responses()->first()->isError())->toBeTrue();
});

test('returns error when user lacks permission', function () {
    $license = License::factory()->create();
    $this->actingAs(User::factory()->create());

    expect(showLicenseTool(['id' => $license->id])->responses()->first()->isError())->toBeTrue();
});
