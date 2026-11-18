<?php

use App\Models\MaintenanceType;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->viewAssets()->create());
});

function handle(array $args = []): ResponseFactory
{
    return handle(new Request($args));
}

test('returns total and pagination metadata', function () {
    MaintenanceType::factory()->count(3)->create();

    $content = handle()->getStructuredContent();

    expect($content)->toHaveKey('total');
    expect($content)->toHaveKey('offset');
    expect($content)->toHaveKey('limit');
    expect($content)->toHaveKey('maintenance_types');
    expect($content['total'])->toBeGreaterThanOrEqual(3);
});

test('each type includes id name and tag color', function () {
    MaintenanceType::factory()->create(['name' => 'UniqueTypeForTest', 'tag_color' => '#ff0000']);

    $content = handle(['limit' => 500])->getStructuredContent();
    $row = collect($content['maintenance_types'])->firstWhere('name', 'UniqueTypeForTest');

    expect($row)->not->toBeNull();
    expect($row)->toHaveKey('id');
    expect($row['name'])->toBe('UniqueTypeForTest');
    expect($row['tag_color'])->toBe('#ff0000');
});

test('search narrows results', function () {
    MaintenanceType::factory()->create(['name' => 'Preventive Maintenance']);
    MaintenanceType::factory()->create(['name' => 'Something Unrelated']);

    $content = handle(['search' => 'Preventive'])->getStructuredContent();
    $names = array_column($content['maintenance_types'], 'name');

    expect($names)->toContain('Preventive Maintenance');
    expect($names)->not->toContain('Something Unrelated');
});

test('results ordered alphabetically by name', function () {
    MaintenanceType::factory()->create(['name' => 'Zebra']);
    MaintenanceType::factory()->create(['name' => 'Alpha']);
    MaintenanceType::factory()->create(['name' => 'Mango']);

    $content = handle(['limit' => 500])->getStructuredContent();
    $names = array_column($content['maintenance_types'], 'name');

    $alphaPos = array_search('Alpha', $names);
    $mangoPos = array_search('Mango', $names);
    $zebraPos = array_search('Zebra', $names);
    expect($alphaPos)->toBeLessThan($mangoPos);
    expect($mangoPos)->toBeLessThan($zebraPos);
});

test('returns error when user lacks permission', function () {
    $this->actingAs(User::factory()->create());

    expect(handle()->responses()->first()->isError())->toBeTrue();
});
