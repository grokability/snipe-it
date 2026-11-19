<?php

use App\Models\Company;
use App\Models\Consumable;
use App\Models\User;

test('single restore requires delete permission', function () {
    $consumable = Consumable::factory()->create();
    $consumable->delete();

    $this->actingAs(User::factory()->viewConsumables()->create())
        ->post(route('consumables.restore', ['consumableId' => $consumable->id]))
        ->assertForbidden();

    expect($consumable->fresh()->deleted_at)->not->toBeNull();
});

test('single restore brings consumable back', function () {
    $consumable = Consumable::factory()->create();
    $consumable->delete();

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('consumables.restore', ['consumableId' => $consumable->id]))
        ->assertRedirect(route('consumables.show', $consumable));

    expect($consumable->fresh()->deleted_at)->toBeNull();
});

test('single restore rejects already live consumable', function () {
    $consumable = Consumable::factory()->create();

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('consumables.restore', ['consumableId' => $consumable->id]))
        ->assertRedirect()
        ->assertSessionHas('error');

    expect($consumable->fresh()->deleted_at)->toBeNull();
});

test('bulk restore brings selected consumables back', function () {
    $one = Consumable::factory()->create();
    $two = Consumable::factory()->create();
    $one->delete();
    $two->delete();

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('consumables.bulkedit.show'), [
            'ids' => [$one->id, $two->id],
            'bulk_actions' => 'restore',
        ])
        ->assertRedirect(route('consumables.index', ['status' => 'Deleted']))
        ->assertSessionHas('success');

    expect($one->fresh()->deleted_at)->toBeNull();
    expect($two->fresh()->deleted_at)->toBeNull();
});

test('bulk restore blocks cross company under fmcs', function () {
    $this->settings->enableMultipleFullCompanySupport();

    [$companyA, $companyB] = Company::factory()->count(2)->create();
    $actor = $companyA->users()->save(User::factory()->editConsumables()->deleteConsumables()->viewConsumables()->make());
    $foreign = Consumable::factory()->for($companyB)->create();
    $foreign->delete();

    $this->actingAs($actor)
        ->post(route('consumables.bulkedit.show'), [
            'ids' => [$foreign->id],
            'bulk_actions' => 'restore',
        ])
        ->assertRedirect();

    expect($foreign->fresh()->deleted_at)->not->toBeNull();
});
