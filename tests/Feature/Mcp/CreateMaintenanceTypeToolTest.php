<?php

use App\Mcp\Tools\CreateMaintenanceTypeTool;
use App\Models\MaintenanceType;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->editAssets()->create());
});

function createMaintenanceTypeTool(array $args = []): ResponseFactory
{
    return (new CreateMaintenanceTypeTool)->handle(new Request($args));
}

test('creates maintenance type with required name', function () {
    createMaintenanceTypeTool(['name' => 'Preventive Maintenance']);

    $this->assertDatabaseHas('maintenance_types', [
        'name' => 'Preventive Maintenance',
    ]);
});

test('creates maintenance type with tag color', function () {
    createMaintenanceTypeTool([
        'name' => 'Urgent Repair',
        'tag_color' => '#ff0000',
    ]);

    $this->assertDatabaseHas('maintenance_types', [
        'name' => 'Urgent Repair',
        'tag_color' => '#ff0000',
    ]);
});

test('response includes id name and tag color', function () {
    $content = createMaintenanceTypeTool([
        'name' => 'Response Check',
        'tag_color' => '#00ff00',
    ])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    expect($content)->toHaveKey('id');
    expect($content['name'])->toBe('Response Check');
    expect($content['tag_color'])->toBe('#00ff00');
});

test('rejects duplicate name', function () {
    MaintenanceType::factory()->create(['name' => 'Already Exists']);

    $response = createMaintenanceTypeTool(['name' => 'Already Exists']);

    expect($response->responses()->first()->isError())->toBeTrue();
});

test('returns error when name missing', function () {
    expect(createMaintenanceTypeTool([])->responses()->first()->isError())->toBeTrue();
});

test('returns error when user lacks permission', function () {
    $this->actingAs(User::factory()->create());

    expect(createMaintenanceTypeTool(['name' => 'Unauthorized Type'])->responses()->first()->isError())->toBeTrue();
});
