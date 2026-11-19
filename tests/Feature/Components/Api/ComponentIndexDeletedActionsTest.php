<?php

use App\Models\Component;
use App\Models\User;

function rowFor(Component $component): array
{
    $response = $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(route('api.components.index', ['status' => 'Deleted']))
        ->assertOk();

    $row = collect($response->json('rows'))->firstWhere('id', $component->id);
    expect($row)->not->toBeNull("deleted component {$component->id} should appear in Deleted index");

    return $row;
}

test('deleted row blocks update and delete and request', function () {
    $component = Component::factory()->create();
    $component->delete();

    $row = rowFor($component);
    expect($row['available_actions']['update'])->toBeFalse();
    expect($row['available_actions']['delete'])->toBeFalse();
    expect($row['available_actions']['request'])->toBeFalse();
});

test('deleted row keeps adjust quantity true so formatter renders disabled', function () {
    // adjust_quantity stays policy-true even for deleted rows so
    // the generic formatter renders a visible-but-disabled plus-
    // minus button (driven off row.deleted_at). Flipping this to
    // false would hide the button entirely, which is NOT what we
    // want on the Deleted index.
    $component = Component::factory()->create();
    $component->delete();

    expect(rowFor($component)['available_actions']['adjust_quantity'])->toBeTrue();
});

test('deleted row keeps checkout true so formatter renders disabled', function () {
    // Same rationale as adjust_quantity: keep the flag policy-
    // true, let the InOut formatter inspect row.deleted_at and
    // render a disabled checkout stand-in.
    $component = Component::factory()->create();
    $component->delete();

    expect(rowFor($component)['available_actions']['checkout'])->toBeTrue();
});

test('deleted row keeps clone available', function () {
    // Cloning a deleted component is explicitly allowed as a
    // restore-and-tweak shortcut: the clone produces a brand-new
    // row and leaves the deleted parent untouched.
    $component = Component::factory()->create();
    $component->delete();

    expect(rowFor($component)['available_actions']['clone'])->toBeTrue();
});

test('deleted row exposes bulk selectable restore', function () {
    // The Deleted index's bulk-actions dropdown offers Restore
    // instead of Edit/Delete. The dropdown's auto-uncheck guard
    // (applyActionDependentRowStyling) reads bulk_selectable.restore
    // to decide whether a row can participate in the restore batch.
    $component = Component::factory()->create();
    $component->delete();

    $bulk = rowFor($component)['available_actions']['bulk_selectable'];
    expect($bulk['restore'])->toBeTrue();
    expect($bulk['edit'])->toBeFalse();
    expect($bulk['delete'])->toBeFalse();
});

test('live row does not expose restore bulk selectable', function () {
    $component = Component::factory()->create();

    $this->settings->set(['show_archived_in_list' => 1]);

    $response = $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(route('api.components.index'))
        ->assertOk();

    $row = collect($response->json('rows'))->firstWhere('id', $component->id);
    expect($row)->not->toBeNull();
    expect($row['available_actions']['bulk_selectable']['restore'])->toBeFalse();
});
