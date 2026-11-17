<?php

namespace Tests\Feature\FileUploads;

use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Regression coverage for Christopher Finks (christopherfi-dev) Issue 7:
 * both UploadedFilesController and Api\UploadedFilesController called
 * Storage::delete() without checking the return value, then created an
 * "upload deleted" action log entry and returned success unconditionally.
 * HasUploads::uploads excludes rows whose filename matches an "upload
 * deleted" log, so a silently failed physical delete produced: file still
 * on disk, action log states it's gone, admin sees a success response, and
 * the file is invisible through the ordinary UI listing.
 *
 * The fix checks Storage::delete's return and refuses to create the
 * deletion log (or return success) when the physical delete failed.
 */
class UploadDeleteStorageFailureTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake();
    }

    private function seedAssetWithUpload(User $actor): array
    {
        $asset = Asset::factory()->create();

        // Upload a real file so a corresponding "uploaded" action log
        // exists for the destroy path to find.
        $this->actingAs($actor)
            ->post(route('ui.files.store', ['object_type' => 'assets', 'id' => $asset->id]), [
                'file' => [UploadedFile::fake()->create('test.pdf', 10)],
            ])
            ->assertRedirect();

        $log = Actionlog::where('item_type', Asset::class)
            ->where('item_id', $asset->id)
            ->where('action_type', 'uploaded')
            ->latest()
            ->first();

        return [$asset, $log];
    }

    private function mockStorageToFailDelete(): void
    {
        // Storage::exists returns true (file is present), Storage::delete
        // returns false (silent-fail). All other calls pass through.
        Storage::fake();
        $default = Storage::disk();

        Storage::shouldReceive('exists')->andReturn(true);
        Storage::shouldReceive('delete')->andReturn(false);
        Storage::shouldReceive('disk')->andReturn($default);
        Storage::shouldReceive('makeDirectory')->andReturnUsing(fn (...$a) => $default->makeDirectory(...$a));
    }

    public function test_web_delete_does_not_log_deletion_when_physical_delete_fails(): void
    {
        $user = User::factory()->superuser()->create();
        [$asset, $log] = $this->seedAssetWithUpload($user);

        $this->mockStorageToFailDelete();

        $response = $this->actingAs($user)
            ->delete(route('ui.files.destroy', ['object_type' => 'assets', 'id' => $asset->id, 'file_id' => $log->id]));

        $response->assertSessionHas('error');

        $this->assertDatabaseMissing('action_logs', [
            'item_type' => Asset::class,
            'item_id' => $asset->id,
            'action_type' => 'upload deleted',
            'filename' => $log->filename,
        ]);
    }

    public function test_api_delete_does_not_log_deletion_when_physical_delete_fails(): void
    {
        $user = User::factory()->superuser()->create();
        [$asset, $log] = $this->seedAssetWithUpload($user);

        $this->mockStorageToFailDelete();

        $response = $this->actingAsForApi($user)
            ->deleteJson(route('api.files.destroy', ['object_type' => 'assets', 'id' => $asset->id, 'file_id' => $log->id]));

        $response->assertStatus(500);

        $this->assertDatabaseMissing('action_logs', [
            'item_type' => Asset::class,
            'item_id' => $asset->id,
            'action_type' => 'upload deleted',
            'filename' => $log->filename,
        ]);
    }
}
