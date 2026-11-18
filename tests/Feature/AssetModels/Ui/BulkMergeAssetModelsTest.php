<?php

use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\User;

test('shows confirmation when at least two models are selected', function () {
    $modelA = AssetModel::factory()->create();
    $modelB = AssetModel::factory()->create();

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('models.bulkedit.index'), [
            'ids' => [$modelA->id, $modelB->id],
            'bulk_actions' => 'merge',
        ])
        ->assertStatus(200)
        ->assertSee($modelA->name)
        ->assertSee($modelB->name);
});

test('redirects to index when fewer than two models selected', function () {
    // Merge requires at least two models. Selecting one is not
    // enough. Redirect with a flash instead of rendering the
    // confirmation view.
    $modelA = AssetModel::factory()->create();

    $this->actingAs(User::factory()->superuser()->create())
        ->from(route('models.index'))
        ->post(route('models.bulkedit.index'), [
            'ids' => [$modelA->id],
            'bulk_actions' => 'merge',
        ])
        ->assertRedirect(route('models.index'))
        ->assertSessionHas('error');
});

test('merges assets from source models into target', function () {
    $target = AssetModel::factory()->create();
    $sourceA = AssetModel::factory()->create();
    $sourceB = AssetModel::factory()->create();

    $assetOnA = Asset::factory()->for($sourceA, 'model')->create();
    $assetOnB1 = Asset::factory()->for($sourceB, 'model')->create();
    $assetOnB2 = Asset::factory()->for($sourceB, 'model')->create();

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('models.merge.save'), [
            'merge_into_id' => $target->id,
            'ids_to_merge' => [$target->id, $sourceA->id, $sourceB->id],
        ])
        ->assertRedirect(route('models.index'))
        ->assertSessionHas('success');

    // Every source model's assets now point at the target.
    expect($assetOnA->refresh()->model_id)->toBe($target->id);
    expect($assetOnB1->refresh()->model_id)->toBe($target->id);
    expect($assetOnB2->refresh()->model_id)->toBe($target->id);

    // Source models are soft-deleted, target survives.
    expect(AssetModel::find($sourceA->id))->toBeNull();
    expect(AssetModel::find($sourceB->id))->toBeNull();
    expect(AssetModel::find($target->id))->not->toBeNull();
});

test('merge target is not deleted even when listed in ids to merge', function () {
    // ids_to_merge carries the full selection including the target
    // (that's how the confirmation form posts). The controller must
    // exclude the target from the sources list before deleting so
    // it doesn't wipe the model we're merging into.
    $target = AssetModel::factory()->create();
    $source = AssetModel::factory()->create();

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('models.merge.save'), [
            'merge_into_id' => $target->id,
            'ids_to_merge' => [$target->id, $source->id],
        ])
        ->assertRedirect(route('models.index'));

    expect(AssetModel::find($target->id))->not->toBeNull();
    expect(AssetModel::find($source->id))->toBeNull();
});

test('redirects with error when target not picked', function () {
    $source = AssetModel::factory()->create();

    $this->actingAs(User::factory()->superuser()->create())
        ->from(route('models.index'))
        ->post(route('models.merge.save'), [
            'merge_into_id' => null,
            'ids_to_merge' => [$source->id],
        ])
        ->assertRedirect(route('models.index'))
        ->assertSessionHas('error');

    // Source is untouched.
    expect(AssetModel::find($source->id))->not->toBeNull();
});

test('merge logs actionlog entries on both source and target', function () {
    // Same shape as the user-merge log: one action_log entry per
    // source pointing at target, plus one entry per pair pointing
    // at target->source. So merging N sources into one target
    // yields 2N action_log rows, all with action_type 'merged'.
    $target = AssetModel::factory()->create();
    $sourceA = AssetModel::factory()->create();
    $sourceB = AssetModel::factory()->create();

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('models.merge.save'), [
            'merge_into_id' => $target->id,
            'ids_to_merge' => [$target->id, $sourceA->id, $sourceB->id],
        ]);

    // Entry on each source model.
    $this->assertDatabaseHas('action_logs', [
        'action_type' => 'merged',
        'item_id' => $sourceA->id,
        'item_type' => AssetModel::class,
        'target_id' => $target->id,
        'target_type' => AssetModel::class,
    ]);
    $this->assertDatabaseHas('action_logs', [
        'action_type' => 'merged',
        'item_id' => $sourceB->id,
        'item_type' => AssetModel::class,
        'target_id' => $target->id,
        'target_type' => AssetModel::class,
    ]);

    // Entry on the target pointing at each source.
    $this->assertDatabaseHas('action_logs', [
        'action_type' => 'merged',
        'item_id' => $target->id,
        'item_type' => AssetModel::class,
        'target_id' => $sourceA->id,
        'target_type' => AssetModel::class,
    ]);
    $this->assertDatabaseHas('action_logs', [
        'action_type' => 'merged',
        'item_id' => $target->id,
        'item_type' => AssetModel::class,
        'target_id' => $sourceB->id,
        'target_type' => AssetModel::class,
    ]);
});

test('non superusers without delete permission are blocked', function () {
    $target = AssetModel::factory()->create();
    $source = AssetModel::factory()->create();

    // A user with no permissions at all shouldn't reach the merge
    // controller. The delete permission on AssetModel is what
    // gates it (matches the user-merge shape).
    $this->actingAs(User::factory()->create())
        ->post(route('models.merge.save'), [
            'merge_into_id' => $target->id,
            'ids_to_merge' => [$target->id, $source->id],
        ])
        ->assertForbidden();

    expect(AssetModel::find($source->id))->not->toBeNull();
});
