<?php

use App\Models\Asset;
use App\Models\Component;
use App\Models\User;

test('requires delete permission', function () {
    $component = Component::factory()->create();

    $this->actingAs(User::factory()->viewComponents()->create())
        ->post(route('components.bulkdelete.store'), ['ids' => [$component->id]])
        ->assertForbidden();

    expect($component->fresh()->deleted_at)->toBeNull();
});

test('soft deletes selected components', function () {
    $one = Component::factory()->create();
    $two = Component::factory()->create();

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('components.bulkdelete.store'), ['ids' => [$one->id, $two->id]])
        ->assertRedirect(route('components.index'))
        ->assertSessionHas('success');

    expect($one->fresh()->deleted_at)->not->toBeNull();
    expect($two->fresh()->deleted_at)->not->toBeNull();
});

test('skips components with active checkouts', function () {
    // Pin so a future refactor can't silently delete checked-out
    // components. isDeletable() returns false while any pivot row
    // has a positive assigned_qty, so the handler should route
    // this row into the error bucket instead of calling delete().
    $clean = Component::factory()->create();

    $withCheckouts = Component::factory()->create(['qty' => 10]);
    $asset = Asset::factory()->create();
    $withCheckouts->assets()->attach($asset->id, [
        'assigned_qty' => 1,
        'created_at' => now(),
        'created_by' => 1,
    ]);

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('components.bulkdelete.store'), ['ids' => [$clean->id, $withCheckouts->id]])
        ->assertRedirect(route('components.index'))
        ->assertSessionHas('multi_error_messages');

    expect($clean->fresh()->deleted_at)->not->toBeNull();
    expect($withCheckouts->fresh()->deleted_at)->toBeNull();
});

test('unknown ids fall through to error bucket', function () {
    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('components.bulkdelete.store'), ['ids' => [999999]])
        ->assertRedirect(route('components.index'))
        ->assertSessionHas('multi_error_messages');
});
