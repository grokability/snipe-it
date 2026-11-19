<?php

use App\Models\Component;
use App\Models\User;

test('show page loads for soft deleted component', function () {
    // Resource route uses ->withTrashed(['show']) so route-model
    // binding resolves soft-deleted components on the show path.
    // Without that, clicking a row from /components?status=Deleted
    // 404s before the controller runs.
    $component = Component::factory()->create();
    $component->delete();

    $this->actingAs(User::factory()->superuser()->create())
        ->get(route('components.show', $component))
        ->assertOk();
});

test('show page renders deleted warning banner', function () {
    $component = Component::factory()->create();
    $component->delete();

    $this->actingAs(User::factory()->superuser()->create())
        ->get(route('components.show', $component))
        ->assertOk()
        ->assertSeeText(trans('general.component_deleted_warning'));
});

test('live show page does not render deleted banner', function () {
    $component = Component::factory()->create();

    $this->actingAs(User::factory()->superuser()->create())
        ->get(route('components.show', $component))
        ->assertOk()
        ->assertDontSeeText(trans('general.component_deleted_warning'));
});

test('clone route resolves soft deleted component', function () {
    // Clone works on deleted parents (restore-and-tweak shortcut).
    // Needs ->withTrashed() on the clone route to let route-model
    // binding find the parent.
    $component = Component::factory()->create();
    $component->delete();

    $this->actingAs(User::factory()->superuser()->create())
        ->get(route('components.clone.create', $component->id))
        ->assertOk();
});

test('adjust quantity endpoint does not touch soft deleted', function () {
    // Belt-and-braces: the view disables the adjust-quantity
    // button on deleted rows, and the endpoint uses standard
    // route-model binding (no withTrashed), so a direct POST
    // to /components/{deleted-id}/adjust-quantity can't bump qty
    // on a deleted parent. Pin on the quantity staying put rather
    // than the specific HTTP status so a future validation /
    // route-model-binding refactor can shift the response code
    // without weakening the actual guard.
    $component = Component::factory()->create(['qty' => 5]);
    $component->delete();

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('components.adjust-quantity', ['component' => $component->id]), [
            'amount' => 1,
            'adjustment_type' => 'increase',
            'purchase_date' => now()->toDateString(),
        ]);

    expect((int) $component->fresh()->qty)->toBe(5);
});

test('checkout endpoint blocks soft deleted', function () {
    // The checkout controller does its own Component::find lookup
    // (not route-model binding) which auto-applies the soft-delete
    // scope and returns null. Pin so stock-depleting checkouts
    // can't fire on a deleted parent.
    $component = Component::factory()->create();
    $component->delete();

    $this->actingAs(User::factory()->superuser()->create())
        ->get(route('components.checkout.show', ['componentID' => $component->id]))
        ->assertRedirect(route('components.index'));
});
