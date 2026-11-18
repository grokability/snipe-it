<?php

use App\Events\CheckoutableCheckedIn;
use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;

test('permission needed to delete asset', function () {
    $this->actingAs(User::factory()->create())
        ->delete(route('hardware.destroy', Asset::factory()->create()))
        ->assertForbidden();
});

test('can delete asset', function () {
    $asset = Asset::factory()->create();

    $this->actingAs(User::factory()->deleteAssets()->create())
        ->delete(route('hardware.destroy', $asset))
        ->assertRedirectToRoute('hardware.index')
        ->assertSessionHas('success');

    $this->assertSoftDeleted($asset);
});

test('action log entry made when asset deleted', function () {
    $actor = User::factory()->deleteAssets()->create();

    $asset = Asset::factory()->create();

    $this->actingAs($actor)->delete(route('hardware.destroy', $asset));

    $this->assertDatabaseHas('action_logs', [
        'created_by' => $actor->id,
        'action_type' => 'delete',
        'target_id' => null,
        'target_type' => null,
        'item_type' => Asset::class,
        'item_id' => $asset->id,
    ]);
});

test('action logs action date is populated when asset deleted', function () {
    $actor = User::factory()->deleteAssets()->create();

    $asset = Asset::factory()->create();

    $this->actingAs($actor)->delete(route('hardware.destroy', $asset));

    // Loggable sets action_date via PHP's date('Y-m-d H:i:s') rather than
    // Carbon::now(), so freezeTime()/setTestNow can't pin it in sync with
    // Eloquent's Carbon-driven created_at or with $asset->updated_at.
    // Assert what the test name actually promises: the delete row exists
    // for this actor/asset AND its action_date column is populated.
    $log = Actionlog::query()
        ->where('created_by', $actor->id)
        ->where('action_type', 'delete')
        ->where('item_type', Asset::class)
        ->where('item_id', $asset->id)
        ->whereNull('target_id')
        ->whereNull('target_type')
        ->first();

    expect($log)->not->toBeNull('Expected a delete action log for the actor and asset.');
    expect($log->action_date)->not->toBeNull('action_date should be populated on the delete action log.');
});

test('asset is checked in when deleted', function () {
    Event::fake();

    $assignedUser = User::factory()->create();
    $asset = Asset::factory()->assignedToUser($assignedUser)->create();

    expect($assignedUser->assets->contains($asset))->toBeTrue();

    $this->actingAs(User::factory()->deleteAssets()->create())
        ->delete(route('hardware.destroy', $asset));

    expect($assignedUser->fresh()->assets->contains($asset))->toBeFalse('Asset still assigned to user after deletion');

    $asset->refresh();
    expect($asset->assigned_to)->toBeNull();
    expect($asset->assigned_type)->toBeNull();

    Event::assertDispatched(CheckoutableCheckedIn::class);
});

test('image is preserved when asset soft deleted', function () {
    // Soft-deleting an asset preserves its image on disk so a
    // restored asset still has one. The image is only removed for
    // good by `snipeit:purge` when the row is force-deleted.
    // Coverage for that permanent-removal path lives in
    // `tests/Feature/Console/Commands/PurgeTest.php`.
    Storage::fake('public');

    $asset = Asset::factory()->create(['image' => 'image.jpg']);

    Storage::disk('public')->put('assets/image.jpg', 'content');

    Storage::disk('public')->assertExists('assets/image.jpg');

    $this->actingAs(User::factory()->deleteAssets()->create())
        ->delete(route('hardware.destroy', $asset));

    Storage::disk('public')->assertExists('assets/image.jpg');
});
