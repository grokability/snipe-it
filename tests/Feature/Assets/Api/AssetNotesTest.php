<?php

namespace Tests\Feature\Assets\Api;

use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\User;
use Tests\TestCase;

class AssetNotesTest extends TestCase
{
    public function testThatANonExistentAssetIdReturnsError()
    {   
        $this->actingAsForApi(User::factory()->editAssets()->create())
            ->postJson(route('api.notes.store', ['asset' => 123456789]))
            ->assertStatusMessageIs('error');
    }

    public function testRequiresPermissionToAddNoteToAssetAsset()
    {
        $asset = Asset::factory()->create();

        $this->actingAsForApi(User::factory()->create())
            ->postJson(route('api.notes.store', $asset), [
                'note' => 'test'
            ])
            ->assertForbidden();
    }

    public function testAssetNoteIsSaved()
    {
        $asset = Asset::factory()->create();

        $this->actingAsForApi(User::factory()->editAssets()->create())
            ->postJson(route('api.notes.store', $asset), [
                'note' => 'This is a test note.'
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

        $note = ActionLog::where('item_id', $asset->id)
            ->where('action_type', 'note added')
            ->first();

        $this->assertNotNull($note, 'The note was not saved in the database.');
        $this->assertEquals('This is a test note.', $note->note, 'The note content does not match.');
    }

    public function testAssetNotesAreRetrievable()
    {
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
                        ]
                    ]
                ],
            ]);
    }
}
