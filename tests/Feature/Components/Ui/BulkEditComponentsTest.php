<?php

use App\Models\Asset;
use App\Models\Category;
use App\Models\Company;
use App\Models\Component;
use App\Models\Location;
use App\Models\Manufacturer;
use App\Models\Supplier;
use App\Models\User;

test('requires update permission to save', function () {
    $component = Component::factory()->create();

    $this->actingAs(User::factory()->viewComponents()->create())
        ->post(route('components.bulkedit.store'), [
            'ids' => [$component->id],
            'archived' => '1',
        ])
        ->assertForbidden();

    expect((bool) $component->fresh()->archived)->toBeFalse();
});

test('dispatcher shows edit view for edit action', function () {
    $component = Component::factory()->create();

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('components.bulkedit.show'), [
            'ids' => [$component->id],
            'bulk_actions' => 'edit',
        ])
        ->assertOk()
        ->assertViewIs('components.bulk-edit');
});

test('dispatcher shows delete view for delete action', function () {
    $component = Component::factory()->create();

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('components.bulkedit.show'), [
            'ids' => [$component->id],
            'bulk_actions' => 'delete',
        ])
        ->assertOk()
        ->assertViewIs('components.bulk-delete');
});

test('dispatcher rejects empty selection', function () {
    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('components.bulkedit.show'), ['bulk_actions' => 'edit'])
        ->assertRedirect(route('components.index'))
        ->assertSessionHas('error');
});

test('update writes only provided fields', function () {
    // Blank form inputs mean "keep as-is". Mirror a form POST where
    // only location changes to confirm every other column is left
    // alone across the selected rows.
    $originalCategory = Category::factory()->create();
    $originalLocation = Location::factory()->create();
    $newLocation = Location::factory()->create();

    $one = Component::factory()->create([
        'category_id' => $originalCategory->id,
        'location_id' => $originalLocation->id,
        'min_amt' => 5,
    ]);
    $two = Component::factory()->create([
        'category_id' => $originalCategory->id,
        'location_id' => $originalLocation->id,
        'min_amt' => 5,
    ]);

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('components.bulkedit.store'), [
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
        ->assertRedirect(route('components.index'))
        ->assertSessionHas('success');

    expect($one->fresh()->location_id)->toBe($newLocation->id);
    expect($two->fresh()->location_id)->toBe($newLocation->id);
    expect($one->fresh()->category_id)->toBe($originalCategory->id);
    expect((int) $one->fresh()->min_amt)->toBe(5);
});

test('update can set archived across selection', function () {
    $one = Component::factory()->create();
    $two = Component::factory()->create();

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('components.bulkedit.store'), [
            'ids' => [$one->id, $two->id],
            'archived' => '1',
        ])
        ->assertRedirect(route('components.index'));

    expect((bool) $one->fresh()->archived)->toBeTrue();
    expect((bool) $two->fresh()->archived)->toBeTrue();
});

test('update flashes error when no rows updated due to validation', function () {
    // Regression test: archive-ing components that all have active
    // checkouts should surface a model-validation error, not a
    // "0 components were updated" success flash.
    $one = Component::factory()->create(['qty' => 5]);
    $two = Component::factory()->create(['qty' => 5]);
    $assetOne = Asset::factory()->create();
    $assetTwo = Asset::factory()->create();
    $one->assets()->attach($assetOne->id, ['assigned_qty' => 1, 'created_at' => now(), 'created_by' => 1]);
    $two->assets()->attach($assetTwo->id, ['assigned_qty' => 1, 'created_at' => now(), 'created_by' => 1]);

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('components.bulkedit.store'), [
            'ids' => [$one->id, $two->id],
            'archived' => '1',
        ])
        ->assertRedirect(route('components.index'))
        ->assertSessionHas('error')
        ->assertSessionMissing('success')
        ->assertSessionHas('multi_error_messages');

    expect((bool) $one->fresh()->archived)->toBeFalse();
    expect((bool) $two->fresh()->archived)->toBeFalse();
});

test('update flashes warning on partial validation failure', function () {
    // Regression test: one component saves, one is rejected by
    // validation. Response should be warning + error details, not
    // plain success.
    $saveable = Component::factory()->create(['qty' => 5]);
    $blocked = Component::factory()->create(['qty' => 5]);
    $asset = Asset::factory()->create();
    $blocked->assets()->attach($asset->id, ['assigned_qty' => 1, 'created_at' => now(), 'created_by' => 1]);

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('components.bulkedit.store'), [
            'ids' => [$saveable->id, $blocked->id],
            'archived' => '1',
        ])
        ->assertRedirect(route('components.index'))
        ->assertSessionHas('warning')
        ->assertSessionMissing('success')
        ->assertSessionHas('multi_error_messages');

    expect((bool) $saveable->fresh()->archived)->toBeTrue();
    expect((bool) $blocked->fresh()->archived)->toBeFalse();
});

test('update rejects empty selection', function () {
    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('components.bulkedit.store'), ['archived' => '1'])
        ->assertRedirect(route('components.index'))
        ->assertSessionHas('error');
});

test('update warns when no fields posted', function () {
    $component = Component::factory()->create();

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('components.bulkedit.store'), [
            'ids' => [$component->id],
        ])
        ->assertRedirect(route('components.index'))
        ->assertSessionHas('warning');
});

test('update covers full classification field set', function () {
    // The field set exposed by BulkComponentsController::EDITABLE_FIELDS
    // is the contract the UI depends on. If someone narrows it (removes
    // a column from EDITABLE_FIELDS or the bulk-edit view), that column
    // silently stops writing across the selection. Pin all of them so
    // the regression surfaces here.
    $company = Company::factory()->create();
    $category = Category::factory()->create();
    $manufacturer = Manufacturer::factory()->create();
    $location = Location::factory()->create();
    $supplier = Supplier::factory()->create();

    $component = Component::factory()->create(['min_amt' => 1]);

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('components.bulkedit.store'), [
            'ids' => [$component->id],
            'company_id' => (string) $company->id,
            'category_id' => (string) $category->id,
            'manufacturer_id' => (string) $manufacturer->id,
            'location_id' => (string) $location->id,
            'default_supplier_id' => (string) $supplier->id,
            'min_amt' => '42',
            'requestable' => '1',
            'archived' => '0',
        ])
        ->assertRedirect(route('components.index'));

    $fresh = $component->fresh();
    expect($fresh->company_id)->toBe($company->id);
    expect($fresh->category_id)->toBe($category->id);
    expect($fresh->manufacturer_id)->toBe($manufacturer->id);
    expect($fresh->location_id)->toBe($location->id);
    expect($fresh->default_supplier_id)->toBe($supplier->id);
    expect((int) $fresh->min_amt)->toBe(42);
    expect((bool) $fresh->requestable)->toBeTrue();
    expect((bool) $fresh->archived)->toBeFalse();
});
