<?php

use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\User;
use Illuminate\Support\Facades\DB;

test('dry run reports orphans without deleting', function () {
    // Non-orphan: parent asset exists.
    $liveAsset = Asset::factory()->create();
    $liveLog = Actionlog::factory()->create([
        'item_type' => Asset::class,
        'item_id' => $liveAsset->id,
        'action_type' => 'checkout',
    ]);

    // Orphan: item_id points at an asset that doesn't exist.
    $orphanLog = Actionlog::factory()->create([
        'item_type' => Asset::class,
        'item_id' => 999999,
        'action_type' => 'checkout',
    ]);

    $this->artisan('snipeit:orphan-action-logs')->assertExitCode(0);

    // Nothing deleted in dry-run.
    $this->assertDatabaseHas('action_logs', ['id' => $liveLog->id]);
    $this->assertDatabaseHas('action_logs', ['id' => $orphanLog->id]);
});

test('delete flag removes orphaned rows and preserves live ones', function () {
    $liveAsset = Asset::factory()->create();
    $liveLog = Actionlog::factory()->create([
        'item_type' => Asset::class,
        'item_id' => $liveAsset->id,
        'action_type' => 'checkout',
    ]);

    $orphanLog = Actionlog::factory()->create([
        'item_type' => Asset::class,
        'item_id' => 999999,
        'action_type' => 'checkout',
    ]);

    $this->artisan('snipeit:orphan-action-logs', ['--delete' => true])
        ->expectsConfirmation('Proceed with deletion?', 'yes')
        ->assertExitCode(0);

    $this->assertDatabaseHas('action_logs', ['id' => $liveLog->id]);
    $this->assertDatabaseMissing('action_logs', ['id' => $orphanLog->id]);
});

test('polymorphic collision across item types does not flag valid rows', function () {
    // Two rows in different tables can share the same id (auto-increment
    // per table). An action_log with item_type=User and item_id=X must
    // not be flagged as orphan just because id=X also exists in the
    // assets table. Scan-only run — no orphans exist, so no prompt.
    $asset = Asset::factory()->create();
    $user = User::factory()->create();

    $assetLog = Actionlog::factory()->create([
        'item_type' => Asset::class,
        'item_id' => $asset->id,
        'action_type' => 'checkout',
    ]);
    $userLog = Actionlog::factory()->create([
        'item_type' => User::class,
        'item_id' => $user->id,
        'action_type' => 'update',
    ]);

    $this->artisan('snipeit:orphan-action-logs', ['--delete' => true])
        ->assertExitCode(0);

    $this->assertDatabaseHas('action_logs', ['id' => $assetLog->id]);
    $this->assertDatabaseHas('action_logs', ['id' => $userLog->id]);
});

test('confirmation declined does not delete', function () {
    Actionlog::factory()->create([
        'item_type' => Asset::class,
        'item_id' => 999999,
        'action_type' => 'checkout',
    ]);

    $orphanCountBefore = DB::table('action_logs')->count();

    $this->artisan('snipeit:orphan-action-logs', ['--delete' => true])
        ->expectsConfirmation('Proceed with deletion?', 'no')
        ->assertExitCode(0);

    expect(DB::table('action_logs')->count())->toEqual($orphanCountBefore);
});

test('unresolvable type reported but not deleted by default', function () {
    // A row whose item_type is a class that no longer exists. Reported
    // as unresolvable, preserved unless --include-unresolvable. Without
    // that flag and with no other orphans present, there's nothing to
    // delete, so no confirmation prompt fires.
    $ghostLog = Actionlog::factory()->create([
        'item_type' => 'App\\Models\\ThisClassDoesNotExist',
        'item_id' => 1,
        'action_type' => 'update',
    ]);

    $this->artisan('snipeit:orphan-action-logs', ['--delete' => true])
        ->assertExitCode(0);

    $this->assertDatabaseHas('action_logs', ['id' => $ghostLog->id]);
});

test('include unresolvable flag deletes ghost class rows', function () {
    $ghostLog = Actionlog::factory()->create([
        'item_type' => 'App\\Models\\ThisClassDoesNotExist',
        'item_id' => 1,
        'action_type' => 'update',
    ]);

    $this->artisan('snipeit:orphan-action-logs', [
        '--delete' => true,
        '--include-unresolvable' => true,
    ])
        ->expectsConfirmation('Proceed with deletion?', 'yes')
        ->assertExitCode(0);

    $this->assertDatabaseMissing('action_logs', ['id' => $ghostLog->id]);
});

test('orphans on target columns are also cleaned', function () {
    // The command must scan BOTH (item_type, item_id) AND
    // (target_type, target_id). A row that is valid on the item_* side
    // but orphaned on the target_* side should still be caught, since
    // User::userlog() and similar relations read target_id/target_type.
    // (item_type is NOT NULL in the schema, so we set it to a valid
    // parent to isolate the target-side orphan being the reason for
    // deletion.)
    $liveAsset = Asset::factory()->create();
    $orphanTargetLog = Actionlog::factory()->create([
        'item_type' => Asset::class,
        'item_id' => $liveAsset->id,
        'target_type' => User::class,
        'target_id' => 999999,
        'action_type' => 'update',
    ]);

    $this->artisan('snipeit:orphan-action-logs', ['--delete' => true])
        ->expectsConfirmation('Proceed with deletion?', 'yes')
        ->assertExitCode(0);

    $this->assertDatabaseMissing('action_logs', ['id' => $orphanTargetLog->id]);
});

test('clean table reports nothing', function () {
    $asset = Asset::factory()->create();
    Actionlog::factory()->create([
        'item_type' => Asset::class,
        'item_id' => $asset->id,
        'action_type' => 'checkout',
    ]);

    $this->artisan('snipeit:orphan-action-logs')
        ->expectsOutputToContain('Table is clean')
        ->assertExitCode(0);
});
