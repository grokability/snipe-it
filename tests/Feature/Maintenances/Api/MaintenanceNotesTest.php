<?php

use App\Models\Actionlog;
use App\Models\Maintenance;
use App\Models\User;

test('index requires permission', function () {
    $maintenance = Maintenance::factory()->create();

    $this->actingAsForApi(User::factory()->create())
        ->getJson(route('api.maintenances.notes.index', $maintenance))
        ->assertForbidden();
});

test('index returns notes for maintenance', function () {
    $actor = User::factory()->superuser()->create();
    $maintenance = Maintenance::factory()->create();

    $log = new Actionlog;
    $log->item_type = Maintenance::class;
    $log->item_id = $maintenance->id;
    $log->note = 'Test note content';
    $log->created_by = $actor->id;
    $log->logaction('note added');

    $response = $this->actingAsForApi($actor)
        ->getJson(route('api.maintenances.notes.index', $maintenance))
        ->assertOk()
        ->assertStatusMessageIs('success');

    $notes = $response->json('payload.notes');
    expect($notes)->toHaveCount(1);
    expect($notes[0]['note'])->toEqual('Test note content');
});

test('index does not return other action types', function () {
    $actor = User::factory()->superuser()->create();
    $maintenance = Maintenance::factory()->create();

    $this->actingAsForApi($actor)
        ->getJson(route('api.maintenances.notes.index', $maintenance))
        ->assertOk();

    // The create actionlog from factory should not appear (it's 'create' not 'note added')
    $response = $this->actingAsForApi($actor)
        ->getJson(route('api.maintenances.notes.index', $maintenance))
        ->assertOk();

    $notes = $response->json('payload.notes');
    expect($notes)->toBeEmpty();
});

test('store requires permission', function () {
    $maintenance = Maintenance::factory()->create();

    $this->actingAsForApi(User::factory()->create())
        ->postJson(route('api.maintenances.notes.store', $maintenance), ['note' => 'Test'])
        ->assertForbidden();
});

test('store validates note is required', function () {
    $actor = User::factory()->superuser()->create();
    $maintenance = Maintenance::factory()->create();

    $this->actingAsForApi($actor)
        ->postJson(route('api.maintenances.notes.store', $maintenance), ['note' => ''])
        ->assertStatus(422);
});

test('store creates note actionlog', function () {
    $actor = User::factory()->superuser()->create();
    $maintenance = Maintenance::factory()->create();

    $this->actingAsForApi($actor)
        ->postJson(route('api.maintenances.notes.store', $maintenance), ['note' => 'Important note'])
        ->assertOk()
        ->assertStatusMessageIs('success');

    $this->assertDatabaseHas('action_logs', [
        'item_type' => Maintenance::class,
        'item_id' => $maintenance->id,
        'action_type' => 'note added',
        'note' => 'Important note',
        'created_by' => $actor->id,
    ]);
});

test('store returns note in response', function () {
    $actor = User::factory()->superuser()->create();
    $maintenance = Maintenance::factory()->create();

    $response = $this->actingAsForApi($actor)
        ->postJson(route('api.maintenances.notes.store', $maintenance), ['note' => 'My note'])
        ->assertOk();

    expect($response->json('payload.note'))->toEqual('My note');
    expect($response->json('payload.item_id'))->toEqual($maintenance->id);
});
