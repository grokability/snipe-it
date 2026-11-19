<?php

use App\Models\Category;
use App\Models\Company;
use App\Models\Consumable;
use App\Models\Location;
use App\Models\Manufacturer;
use App\Models\Supplier;
use App\Models\User;

test('requires update permission to save', function () {
    $consumable = Consumable::factory()->create();

    $this->actingAs(User::factory()->viewConsumables()->create())
        ->post(route('consumables.bulkedit.store'), [
            'ids' => [$consumable->id],
            'archived' => '1',
        ])
        ->assertForbidden();

    expect((bool) $consumable->fresh()->archived)->toBeFalse();
});

test('dispatcher shows edit view for edit action', function () {
    $consumable = Consumable::factory()->create();

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('consumables.bulkedit.show'), [
            'ids' => [$consumable->id],
            'bulk_actions' => 'edit',
        ])
        ->assertOk()
        ->assertViewIs('consumables.bulk-edit');
});

test('dispatcher shows delete view for delete action', function () {
    $consumable = Consumable::factory()->create();

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('consumables.bulkedit.show'), [
            'ids' => [$consumable->id],
            'bulk_actions' => 'delete',
        ])
        ->assertOk()
        ->assertViewIs('consumables.bulk-delete');
});

test('update writes only provided fields', function () {
    $originalCategory = Category::factory()->consumablePaperCategory()->create();
    $originalLocation = Location::factory()->create();
    $newLocation = Location::factory()->create();

    $one = Consumable::factory()->create([
        'category_id' => $originalCategory->id,
        'location_id' => $originalLocation->id,
        'min_amt' => 5,
    ]);
    $two = Consumable::factory()->create([
        'category_id' => $originalCategory->id,
        'location_id' => $originalLocation->id,
        'min_amt' => 5,
    ]);

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('consumables.bulkedit.store'), [
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
        ->assertRedirect(route('consumables.index'))
        ->assertSessionHas('success');

    expect($one->fresh()->location_id)->toBe($newLocation->id);
    expect($two->fresh()->location_id)->toBe($newLocation->id);
    expect($one->fresh()->category_id)->toBe($originalCategory->id);
    expect((int) $one->fresh()->min_amt)->toBe(5);
});

test('update can set archived across selection', function () {
    $one = Consumable::factory()->create();
    $two = Consumable::factory()->create();

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('consumables.bulkedit.store'), [
            'ids' => [$one->id, $two->id],
            'archived' => '1',
        ])
        ->assertRedirect(route('consumables.index'));

    expect((bool) $one->fresh()->archived)->toBeTrue();
    expect((bool) $two->fresh()->archived)->toBeTrue();
});

test('update flashes error when no rows updated due to validation', function () {
    // Regression test: archive-ing consumables that all have
    // outstanding distributions should surface a model-validation
    // error, not a "0 consumables were updated" success flash.
    $one = Consumable::factory()->checkedOutToUser()->create();
    $two = Consumable::factory()->checkedOutToUser()->create();

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('consumables.bulkedit.store'), [
            'ids' => [$one->id, $two->id],
            'archived' => '1',
        ])
        ->assertRedirect(route('consumables.index'))
        ->assertSessionHas('error')
        ->assertSessionMissing('success')
        ->assertSessionHas('multi_error_messages');

    expect((bool) $one->fresh()->archived)->toBeFalse();
    expect((bool) $two->fresh()->archived)->toBeFalse();
});

test('update flashes warning on partial validation failure', function () {
    // One row saves, one is rejected by the outstanding-check.
    // Response should be warning + error details, not plain success.
    $saveable = Consumable::factory()->create();
    $blocked = Consumable::factory()->checkedOutToUser()->create();

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('consumables.bulkedit.store'), [
            'ids' => [$saveable->id, $blocked->id],
            'archived' => '1',
        ])
        ->assertRedirect(route('consumables.index'))
        ->assertSessionHas('warning')
        ->assertSessionMissing('success')
        ->assertSessionHas('multi_error_messages');

    expect((bool) $saveable->fresh()->archived)->toBeTrue();
    expect((bool) $blocked->fresh()->archived)->toBeFalse();
});

test('update covers full classification field set', function () {
    $company = Company::factory()->create();
    $category = Category::factory()->consumablePaperCategory()->create();
    $manufacturer = Manufacturer::factory()->create();
    $location = Location::factory()->create();
    $supplier = Supplier::factory()->create();

    $consumable = Consumable::factory()->create(['min_amt' => 1]);

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('consumables.bulkedit.store'), [
            'ids' => [$consumable->id],
            'company_id' => (string) $company->id,
            'category_id' => (string) $category->id,
            'manufacturer_id' => (string) $manufacturer->id,
            'location_id' => (string) $location->id,
            'default_supplier_id' => (string) $supplier->id,
            'min_amt' => '42',
            'requestable' => '1',
            'archived' => '0',
        ])
        ->assertRedirect(route('consumables.index'));

    $fresh = $consumable->fresh();
    expect($fresh->company_id)->toBe($company->id);
    expect($fresh->category_id)->toBe($category->id);
    expect($fresh->manufacturer_id)->toBe($manufacturer->id);
    expect($fresh->location_id)->toBe($location->id);
    expect($fresh->default_supplier_id)->toBe($supplier->id);
    expect((int) $fresh->min_amt)->toBe(42);
    expect((bool) $fresh->requestable)->toBeTrue();
    expect((bool) $fresh->archived)->toBeFalse();
});
