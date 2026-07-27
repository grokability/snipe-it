<?php

namespace Tests\Feature\Assets\Ui;

use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\Import;
use App\Models\User;
use Tests\Support\Importing\AssetHistoryImportFileBuilder;
use Tests\Support\Importing\CleansUpImportFiles;
use Tests\TestCase;

class ImportAssetHistoryTest extends TestCase
{
    use CleansUpImportFiles;

    public function test_legacy_get_history_route_redirects_to_importer(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/hardware/history')
            ->assertRedirect(route('imports.index'));
    }

    public function test_legacy_post_history_endpoint_is_gone(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->post('/hardware/history')
            ->assertStatus(405);
    }

    public function test_process_endpoint_blocked_in_demo_mode(): void
    {
        // Uploads were already blocked at Api\ImportController::store,
        // but the process endpoint would still let a demo user mutate the
        // DB via any seeded / leftover Import row. The lock_passwords
        // gate here closes that loophole.
        config(['app.lock_passwords' => true]);

        $actor = User::factory()->canImport()->superuser()->create();
        $import = Import::factory()->assetHistory()->create(['created_by' => $actor->id]);

        $this->actingAsForApi($actor);
        $this->postJson(
            route('api.imports.importFile', ['import' => $import->id]),
            ['import-type' => 'assetHistory', 'import' => $import->id],
        )->assertStatus(422);
    }

    public function test_asset_history_import_requires_import_permission(): void
    {
        $actor = User::factory()->create();
        $import = Import::factory()->assetHistory()->create(['created_by' => $actor->id]);

        $this->actingAsForApi($actor);
        $this->postJson(
            route('api.imports.importFile', ['import' => $import->id]),
            ['import-type' => 'assetHistory', 'import' => $import->id],
        )->assertForbidden();
    }

    public function test_asset_history_import_creates_actionlogs_and_assigns_user(): void
    {
        $actor = User::factory()->canImport()->create();
        $target = User::factory()->create(['username' => 'target.user']);
        $asset = Asset::factory()->create([
            'asset_tag' => 'AHIST-1',
            'assigned_to' => null,
            'assigned_type' => null,
        ]);

        $checkoutDate = now()->subDay()->format('Y-m-d H:i:s');
        $checkinDate = now()->addDays(30)->format('Y-m-d H:i:s');

        $file = AssetHistoryImportFileBuilder::new([
            'assetTag' => $asset->asset_tag,
            'name' => $target->username,
            'email' => '',
            'checkoutDate' => $checkoutDate,
            'checkinDate' => $checkinDate,
        ]);

        $import = Import::factory()->assetHistory()->create([
            'created_by' => $actor->id,
            'file_path' => $file->saveToImportsDirectory(),
        ]);

        $this->actingAsForApi($actor);
        $this->postJson(
            route('api.imports.importFile', ['import' => $import->id]),
            [
                'import-type' => 'assetHistory',
                'import' => $import->id,
                'match_username' => true,
            ],
        )->assertOk();

        $asset->refresh();
        $this->assertEquals($target->id, $asset->assigned_to);
        $this->assertEquals(User::class, $asset->assigned_type);

        $this->assertDatabaseHas('action_logs', [
            'item_id' => $asset->id,
            'item_type' => Asset::class,
            'target_id' => $target->id,
            'target_type' => User::class,
            'action_type' => 'checkout',
        ]);

        $this->assertDatabaseHas('action_logs', [
            'item_id' => $asset->id,
            'item_type' => Asset::class,
            'target_id' => null,
            'action_type' => 'checkin',
        ]);
    }

    public function test_asset_history_import_does_not_reassign_when_checkin_is_past(): void
    {
        $actor = User::factory()->canImport()->create();
        $target = User::factory()->create(['username' => 'past.user']);
        $asset = Asset::factory()->create([
            'asset_tag' => 'AHIST-2',
            'assigned_to' => null,
            'assigned_type' => null,
        ]);

        $file = AssetHistoryImportFileBuilder::new([
            'assetTag' => $asset->asset_tag,
            'name' => $target->username,
            'email' => '',
            'checkoutDate' => now()->subDays(30)->format('Y-m-d H:i:s'),
            'checkinDate' => now()->subDays(15)->format('Y-m-d H:i:s'),
        ]);

        $import = Import::factory()->assetHistory()->create([
            'created_by' => $actor->id,
            'file_path' => $file->saveToImportsDirectory(),
        ]);

        $this->actingAsForApi($actor);
        $this->postJson(
            route('api.imports.importFile', ['import' => $import->id]),
            [
                'import-type' => 'assetHistory',
                'import' => $import->id,
                'match_username' => true,
            ],
        )->assertOk();

        $asset->refresh();
        $this->assertNull($asset->assigned_to);
        $this->assertNull($asset->assigned_type);

        // Historical checkout + checkin actionlogs both got written even
        // though the asset ends up in a checked-in state.
        $this->assertDatabaseHas('action_logs', [
            'item_id' => $asset->id,
            'action_type' => 'checkout',
        ]);
        $this->assertDatabaseHas('action_logs', [
            'item_id' => $asset->id,
            'action_type' => 'checkin',
        ]);
    }

    public function test_asset_history_import_skips_unknown_asset_tag(): void
    {
        $actor = User::factory()->canImport()->create();
        User::factory()->create(['username' => 'someone']);

        $file = AssetHistoryImportFileBuilder::new([
            'assetTag' => 'DOES-NOT-EXIST',
            'name' => 'someone',
            'email' => '',
            'checkoutDate' => now()->format('Y-m-d H:i:s'),
            'checkinDate' => '',
        ]);

        $import = Import::factory()->assetHistory()->create([
            'created_by' => $actor->id,
            'file_path' => $file->saveToImportsDirectory(),
        ]);

        $this->actingAsForApi($actor);
        // Skipped rows now surface as import-errors so the wizard can
        // display the reason (Asset does not exist) instead of hiding it
        // in laravel.log. The 500 status is the shared API contract for
        // an errors-non-empty return.
        $response = $this->postJson(
            route('api.imports.importFile', ['import' => $import->id]),
            ['import-type' => 'assetHistory', 'import' => $import->id, 'match_username' => true],
        );
        $response->assertStatus(500);
        $this->assertEquals('import-errors', $response->json('status'));
        $this->assertArrayHasKey('Asset DOES-NOT-EXIST', $response->json('messages'));

        $this->assertSame(0, Actionlog::where('note', 'like', '%history importer%')->count());
    }

    public function test_asset_history_import_skips_row_when_user_not_matched(): void
    {
        $actor = User::factory()->canImport()->create();
        $asset = Asset::factory()->create([
            'asset_tag' => 'AHIST-3',
            'assigned_to' => null,
            'assigned_type' => null,
        ]);

        $file = AssetHistoryImportFileBuilder::new([
            'assetTag' => $asset->asset_tag,
            'name' => 'no.such.user',
            'email' => '',
            'checkoutDate' => now()->format('Y-m-d H:i:s'),
            'checkinDate' => '',
        ]);

        $import = Import::factory()->assetHistory()->create([
            'created_by' => $actor->id,
            'file_path' => $file->saveToImportsDirectory(),
        ]);

        $this->actingAsForApi($actor);
        $response = $this->postJson(
            route('api.imports.importFile', ['import' => $import->id]),
            ['import-type' => 'assetHistory', 'import' => $import->id, 'match_username' => true],
        );
        $response->assertStatus(500);
        $this->assertEquals('import-errors', $response->json('status'));
        $this->assertArrayHasKey('Asset '.$asset->asset_tag, $response->json('messages'));

        $asset->refresh();
        $this->assertNull($asset->assigned_to);
        $this->assertDatabaseMissing('action_logs', [
            'item_id' => $asset->id,
            'action_type' => 'checkout',
        ]);
    }
}
