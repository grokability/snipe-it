<?php

use App\Models\Consumable;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->checkoutConsumables()->create());
});

function handle(array $args = []): ResponseFactory
{
    return handle(new Request($args));
}

test('checks out consumable to user', function () {
    $consumable = Consumable::factory()->create(['qty' => 5]);
    $user = User::factory()->create();

    $content = handle([
        'id' => $consumable->id,
        'assigned_to' => $user->id,
    ])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertDatabaseHas('consumables_users', [
        'consumable_id' => $consumable->id,
        'assigned_to' => $user->id,
    ]);
});

test('returns error when no units remaining', function () {
    $consumable = Consumable::factory()->withoutItemsRemaining()->create();
    $user = User::factory()->create();

    $response = handle([
        'id' => $consumable->id,
        'assigned_to' => $user->id,
    ]);

    expect($response->responses()->first()->isError())->toBeTrue();
});

test('returns error when consumable not found', function () {
    $user = User::factory()->create();

    expect(handle([
        'id' => 999999,
        'assigned_to' => $user->id,
    ])->responses()->first()->isError())->toBeTrue();
});

test('returns error when user not found', function () {
    $consumable = Consumable::factory()->create(['qty' => 5]);

    expect(handle([
        'id' => $consumable->id,
        'assigned_to' => 999999,
    ])->responses()->first()->isError())->toBeTrue();
});

test('returns error when user lacks permission', function () {
    $this->actingAs(User::factory()->create());
    $consumable = Consumable::factory()->create(['qty' => 5]);
    $user = User::factory()->create();

    expect(handle([
        'id' => $consumable->id,
        'assigned_to' => $user->id,
    ])->responses()->first()->isError())->toBeTrue();
});

test('refuses to check out archived consumable', function () {
    $consumable = Consumable::factory()->archived()->create(['qty' => 5]);
    $user = User::factory()->create();

    $response = handle([
        'id' => $consumable->id,
        'assigned_to' => $user->id,
    ]);

    expect($response->responses()->first()->isError())->toBeTrue();
    $this->assertDatabaseMissing('consumables_users', [
        'consumable_id' => $consumable->id,
        'assigned_to' => $user->id,
    ]);
});
