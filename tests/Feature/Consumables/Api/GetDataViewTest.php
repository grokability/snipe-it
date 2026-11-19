<?php

use App\Models\Consumable;
use App\Models\ConsumableAssignment;
use App\Models\User;

test('endpoint returns outstanding rows', function () {
    $consumable = Consumable::factory()->checkedOutToUser()->create();
    $assignment = ConsumableAssignment::where('consumable_id', $consumable->id)->firstOrFail();

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(route('api.consumables.show.users', $consumable->id))
        ->assertOk()
        ->assertJson([
            'total' => 1,
            'rows' => [
                [
                    'id' => $assignment->id,
                    'consumable_id' => $consumable->id,
                ],
            ],
        ]);
});

test('row marks consume action available when outstanding', function () {
    $consumable = Consumable::factory()->checkedOutToUser()->create();

    $response = $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(route('api.consumables.show.users', $consumable->id))
        ->assertOk();

    expect($response->json('rows.0.available_actions.consume'))->toBeTrue();
    expect($response->json('rows.0.available_actions.bulk_selectable.consume'))->toBeTrue();
});

test('endpoint hides consumed rows', function () {
    // Mark-consumed is a terminal state. Once a pivot row has a
    // consumed_at stamp, it stops surfacing on the Assigned tab
    // (which lists "currently distributed" rows only). The
    // consume audit log on the History tab preserves the record.
    $consumable = Consumable::factory()->checkedOutToUser()->create();
    $assignment = ConsumableAssignment::where('consumable_id', $consumable->id)->firstOrFail();
    $assignment->consumed_at = now();
    $assignment->save();

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(route('api.consumables.show.users', $consumable->id))
        ->assertOk()
        ->assertJson([
            'total' => 0,
            'rows' => [],
        ]);
});

test('endpoint returns mix of outstanding and hides consumed', function () {
    $consumable = Consumable::factory()->checkedOutToUser()->checkedOutToUser()->create();
    $assignments = ConsumableAssignment::where('consumable_id', $consumable->id)->get();
    $outstanding = $assignments->first();
    $consumed = $assignments->last();
    $consumed->consumed_at = now();
    $consumed->save();

    $response = $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(route('api.consumables.show.users', $consumable->id))
        ->assertOk();

    expect($response->json('total'))->toBe(1);
    expect($response->json('rows.0.id'))->toBe($outstanding->id);
});

test('row hides consume action when caller lacks checkout permission', function () {
    // Regression pin: Gate::allows('checkout', $consumable) is
    // computed once at the top of getDataView and threaded through
    // every row, so a view-only caller sees consume=false on all
    // outstanding rows.
    $consumable = Consumable::factory()->checkedOutToUser()->create();

    $response = $this->actingAsForApi(User::factory()->viewConsumables()->create())
        ->getJson(route('api.consumables.show.users', $consumable->id))
        ->assertOk();

    expect($response->json('rows.0.available_actions.consume'))->toBeFalse();
});
