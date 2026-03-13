<?php

namespace Tests\Feature\Notes;

use App\Models\Asset;
use App\Models\User;
use Tests\TestCase;

class CreateNotesTest extends TestCase
{
    public function testRequiresPermission()
    {
        $this->actingAs(User::factory()->create())
            ->post(route('notes.store'))
            ->assertForbidden();
    }

    public function testValidation()
    {
        $asset = Asset::factory()->create();

        $this->actingAs(User::factory()->editAssets()->create())
            ->post(route('notes.store'), [
                'id' => $asset->id,
                // should be more...
            ])
            ->assertSessionHas('errors');
    }

    public function testAssetMustExist()
    {
        $this->actingAs(User::factory()->editAssets()->create())
            ->post(route('notes.store'), [
                'id' => 999_999,
                'type' => 'asset',
                'note' => 'my note',
            ])
            ->assertStatus(302);
    }

    public function testCanCreateNoteForAsset()
    {
        $actor = User::factory()->editAssets()->create();

        $asset = Asset::factory()->create();

        $this->actingAs($actor)
            ->withHeader('User-Agent', 'Custom User Agent For Test')
            ->post(route('notes.store'), [
                '_token' => '_token-to-simulate-request-from-gui',
                'id' => $asset->id,
                'type' => 'asset',
                'note' => 'my special note',
            ])
            ->assertRedirect(route('hardware.show', $asset->id) . '#history')
            ->assertSessionHas('success', trans('general.note_added'));

        $this->assertDatabaseHas('action_logs', [
            'created_by' => $actor->id,
            'action_type' => 'note added',
            'target_id' => null,
            'target_type' => null,
            'note' => 'my special note',
            'item_type' => Asset::class,
            'item_id' => $asset->id,
            'action_source' => 'gui',
            'user_agent' => 'Custom User Agent For Test',
        ]);
    }
}
