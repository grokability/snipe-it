<?php

use App\Models\Accessory;
use App\Models\AccessoryCheckout;
use App\Models\Category;
use App\Models\Company;
use App\Models\Location;
use App\Models\Manufacturer;
use App\Models\Supplier;
use App\Models\User;

test('requires update permission to save', function () {
    $accessory = Accessory::factory()->create();

    $this->actingAs(User::factory()->viewAccessories()->create())
        ->post(route('accessories.bulkedit.store'), [
            'ids' => [$accessory->id],
            'archived' => '1',
        ])
        ->assertForbidden();

    expect((bool) $accessory->fresh()->archived)->toBeFalse();
});

test('dispatcher shows edit view for edit action', function () {
    $accessory = Accessory::factory()->create();

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('accessories.bulkedit.show'), [
            'ids' => [$accessory->id],
            'bulk_actions' => 'edit',
        ])
        ->assertOk()
        ->assertViewIs('accessories.bulk-edit');
});

test('dispatcher shows delete view for delete action', function () {
    $accessory = Accessory::factory()->create();

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('accessories.bulkedit.show'), [
            'ids' => [$accessory->id],
            'bulk_actions' => 'delete',
        ])
        ->assertOk()
        ->assertViewIs('accessories.bulk-delete');
});

test('update writes only provided fields', function () {
    $originalCategory = Category::factory()->create();
    $originalLocation = Location::factory()->create();
    $newLocation = Location::factory()->create();

    $one = Accessory::factory()->create([
        'category_id' => $originalCategory->id,
        'location_id' => $originalLocation->id,
        'min_amt' => 5,
    ]);
    $two = Accessory::factory()->create([
        'category_id' => $originalCategory->id,
        'location_id' => $originalLocation->id,
        'min_amt' => 5,
    ]);

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('accessories.bulkedit.store'), [
            'ids' => [$one->id, $two->id],
            'company_id' => '',
            'category_id' => '',
            'manufacturer_id' => '',
            'location_id' => (string) $newLocation->id,
            'default_supplier_id' => '',
            'min_amt' => '',
            'requestable' => '',
            'archived' => '',
        ])
        ->assertRedirect(route('accessories.index'))
        ->assertSessionHas('success');

    expect($one->fresh()->location_id)->toBe($newLocation->id);
    expect($two->fresh()->location_id)->toBe($newLocation->id);
    expect($one->fresh()->category_id)->toBe($originalCategory->id);
    expect((int) $one->fresh()->min_amt)->toBe(5);
});

test('update can set archived across selection', function () {
    $one = Accessory::factory()->create();
    $two = Accessory::factory()->create();

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('accessories.bulkedit.store'), [
            'ids' => [$one->id, $two->id],
            'archived' => '1',
        ])
        ->assertRedirect(route('accessories.index'));

    expect((bool) $one->fresh()->archived)->toBeTrue();
    expect((bool) $two->fresh()->archived)->toBeTrue();
});

test('update flashes error when no rows updated due to validation', function () {
    // Regression test: archive-ing accessories that all have active
    // checkouts should surface a model-validation error, not a
    // "0 accessories were updated" success flash.
    $one = Accessory::factory()->create();
    $two = Accessory::factory()->create();
    AccessoryCheckout::factory()->for($one, 'accessory')->create();
    AccessoryCheckout::factory()->for($two, 'accessory')->create();

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('accessories.bulkedit.store'), [
            'ids' => [$one->id, $two->id],
            'archived' => '1',
        ])
        ->assertRedirect(route('accessories.index'))
        ->assertSessionHas('error')
        ->assertSessionMissing('success')
        ->assertSessionHas('multi_error_messages');

    expect((bool) $one->fresh()->archived)->toBeFalse();
    expect((bool) $two->fresh()->archived)->toBeFalse();
});

test('update flashes warning on partial validation failure', function () {
    // Regression test: one accessory saves, one is rejected by
    // validation. Response should be warning + error details, not
    // plain success.
    $saveable = Accessory::factory()->create();
    $blocked = Accessory::factory()->create();
    AccessoryCheckout::factory()->for($blocked, 'accessory')->create();

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('accessories.bulkedit.store'), [
            'ids' => [$saveable->id, $blocked->id],
            'archived' => '1',
        ])
        ->assertRedirect(route('accessories.index'))
        ->assertSessionHas('warning')
        ->assertSessionMissing('success')
        ->assertSessionHas('multi_error_messages');

    expect((bool) $saveable->fresh()->archived)->toBeTrue();
    expect((bool) $blocked->fresh()->archived)->toBeFalse();
});

test('update covers full classification field set', function () {
    $company = Company::factory()->create();
    $category = Category::factory()->create();
    $manufacturer = Manufacturer::factory()->create();
    $location = Location::factory()->create();
    $supplier = Supplier::factory()->create();

    $accessory = Accessory::factory()->create(['min_amt' => 1]);

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('accessories.bulkedit.store'), [
            'ids' => [$accessory->id],
            'company_id' => (string) $company->id,
            'category_id' => (string) $category->id,
            'manufacturer_id' => (string) $manufacturer->id,
            'location_id' => (string) $location->id,
            'default_supplier_id' => (string) $supplier->id,
            'min_amt' => '42',
            'requestable' => '1',
            'archived' => '0',
        ])
        ->assertRedirect(route('accessories.index'));

    $fresh = $accessory->fresh();
    expect($fresh->company_id)->toBe($company->id);
    expect($fresh->category_id)->toBe($category->id);
    expect($fresh->manufacturer_id)->toBe($manufacturer->id);
    expect($fresh->location_id)->toBe($location->id);
    expect($fresh->default_supplier_id)->toBe($supplier->id);
    expect((int) $fresh->min_amt)->toBe(42);
    expect((bool) $fresh->requestable)->toBeTrue();
    expect((bool) $fresh->archived)->toBeFalse();
});
