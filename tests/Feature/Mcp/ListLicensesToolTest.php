<?php

use App\Mcp\Tools\ListLicensesTool;
use App\Models\Category;
use App\Models\License;
use App\Models\Manufacturer;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->viewLicenses()->create());
});

function listLicensesTool(array $args = []): ResponseFactory
{
    return (new ListLicensesTool)->handle(new Request($args));
}

test('returns total licenses and pagination metadata', function () {
    License::factory()->count(3)->create();

    $content = listLicensesTool()->getStructuredContent();

    expect($content)->toHaveKey('total');
    expect($content)->toHaveKey('licenses');
    expect($content)->toHaveKey('limit');
    expect($content)->toHaveKey('offset');
    expect($content['total'])->toBeGreaterThanOrEqual(3);
});

test('each license includes key fields', function () {
    License::factory()->create();

    $content = listLicensesTool()->getStructuredContent();

    $license = $content['licenses'][0];
    expect($license)->toHaveKey('id');
    expect($license)->toHaveKey('name');
    expect($license)->toHaveKey('seats');
    expect($license)->toHaveKey('free_seats');
    expect($license)->toHaveKey('category');
});

test('limit controls number of results returned', function () {
    License::factory()->count(10)->create();

    $content = listLicensesTool(['limit' => 3])->getStructuredContent();

    expect($content['licenses'])->toHaveCount(3);
    expect($content['total'])->toBeGreaterThan(3);
});

test('offset skips results for pagination', function () {
    License::factory()->count(10)->create();

    $page1 = listLicensesTool(['limit' => 4, 'offset' => 0])->getStructuredContent();
    $page2 = listLicensesTool(['limit' => 4, 'offset' => 4])->getStructuredContent();

    $ids1 = array_column($page1['licenses'], 'id');
    $ids2 = array_column($page2['licenses'], 'id');

    expect(array_intersect($ids1, $ids2))->toBeEmpty();
});

test('search matches on name', function () {
    $target = License::factory()->create(['name' => 'UniqueLicenseXYZ']);
    License::factory()->create(['name' => 'Something Else']);

    $content = listLicensesTool(['search' => 'UniqueLicenseXYZ'])->getStructuredContent();
    $ids = array_column($content['licenses'], 'id');

    expect($ids)->toContain($target->id);
});

test('filters by category id', function () {
    $category = Category::factory()->create(['category_type' => 'license']);
    $matching = License::factory()->create(['category_id' => $category->id]);
    $other = License::factory()->create();

    $content = listLicensesTool(['category_id' => $category->id])->getStructuredContent();
    $ids = array_column($content['licenses'], 'id');

    expect($ids)->toContain($matching->id);
    expect($ids)->not->toContain($other->id);
});

test('filters by manufacturer id', function () {
    $manufacturer = Manufacturer::factory()->create();
    $matching = License::factory()->create(['manufacturer_id' => $manufacturer->id]);
    $other = License::factory()->create(['manufacturer_id' => null]);

    $content = listLicensesTool(['manufacturer_id' => $manufacturer->id])->getStructuredContent();
    $ids = array_column($content['licenses'], 'id');

    expect($ids)->toContain($matching->id);
    expect($ids)->not->toContain($other->id);
});

test('returns error when user lacks permission', function () {
    $this->actingAs(User::factory()->create());

    expect(listLicensesTool()->responses()->first()->isError())->toBeTrue();
});

test('expiring within days filters to license window', function () {
    $soon = License::factory()->create(['name' => 'Soon', 'expiration_date' => now()->addDays(10)->toDateString()]);
    $later = License::factory()->create(['name' => 'Later', 'expiration_date' => now()->addDays(90)->toDateString()]);

    $content = listLicensesTool(['expiring_within_days' => 30])->getStructuredContent();
    $names = array_column($content['licenses'], 'name');

    expect($names)->toContain('Soon');
    expect($names)->not->toContain('Later');
});

test('filter payload forwards to text search', function () {
    $match = License::factory()->create(['name' => 'Office Enterprise 365']);
    License::factory()->create(['name' => 'Unrelated License']);

    $content = listLicensesTool(['filter' => ['name' => 'Office']])->getStructuredContent();
    $names = array_column($content['licenses'], 'name');

    expect($names)->toContain('Office Enterprise 365');
});

test('response includes lifecycle fields and notes', function () {
    $license = License::factory()->create(['name' => 'Marker License', 'seats' => 25]);

    $content = listLicensesTool(['limit' => 500])->getStructuredContent();
    $row = collect($content['licenses'])->firstWhere('name', 'Marker License');

    expect($row)->not->toBeNull();
    expect($row)->toHaveKey('seats');
    expect($row)->toHaveKey('free_seats');
    expect($row)->toHaveKey('expiration_date');
    expect($row)->toHaveKey('termination_date');
    expect($row)->toHaveKey('maintained');
    expect($row)->toHaveKey('reassignable');
    expect($row)->toHaveKey('notes');
});
