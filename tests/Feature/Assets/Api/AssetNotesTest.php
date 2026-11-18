<?php

use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\User;

test('that a non existent asset id returns error', function () {
    $this->actingAsForApi(User::factory()->editAssets()->create())
        ->postJson(route('api.notes.store', ['asset' => 123456789]))
        ->assertStatusMessageIs('error');
});

test('requires permission to add note to asset asset', function () {
    $asset = Asset::factory()->create();

    $this->actingAsForApi(User::factory()->create())
        ->postJson(route('api.notes.store', $asset), [
            'note' => 'test',
        ])
        ->assertForbidden();
});

test('asset note is saved', function () {
    $asset = Asset::factory()->create();

    $this->actingAsForApi(User::factory()->editAssets()->create())
        ->postJson(route('api.notes.store', $asset), [
            'note' => 'This is a test note.',
        ])
        ->assertStatusMessageIs('success')
        ->assertJson([
            'messages' => trans('general.note_added'),
            'payload' => [
                'note' => 'This is a test note.',
                'item_id' => e($asset->id),
            ],
        ])
        ->assertStatus(200);

    $note = Actionlog::where('item_id', $asset->id)
        ->where('action_type', 'note added')
        ->first();

    expect($note)->not->toBeNull('The note was not saved in the database.');
    expect($note->note)->toEqual('This is a test note.', 'The note content does not match.');
});

test('asset notes are retrievable', function () {
    $asset = Asset::factory()->create();

    $user = User::factory()->viewAssets()->create();

    $assetNote = Actionlog::factory()
        ->assetNote($user)
        ->create([
            'item_id' => $asset->id,
            'note' => 'This is a test note.',
        ]);

    $this->actingAsForApi($user)
        ->getJson(route('api.notes.index', $asset))
        ->assertOk()
        ->assertJson([
            'messages' => null,
            'payload' => [
                'notes' => [
                    [
                        'note' => 'This is a test note.',
                        'created_by' => $assetNote->created_by,
                        'username' => $user->username,
                        'item_id' => $assetNote->item_id,
                        'item_type' => Asset::class,
                        'action_type' => 'note added',
                    ],
                ],
            ],
        ]);
});
