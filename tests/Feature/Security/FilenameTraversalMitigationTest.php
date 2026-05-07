<?php

namespace Tests\Feature\Security;

use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FilenameTraversalMitigationTest extends TestCase
{
    public function test_settings_backup_download_rejects_nested_filename_input(): void
    {
        config(['app.lock_passwords' => false]);

        $this->actingAs(User::factory()->superuser()->create())
            ->get('/admin/backups/download/..')
            ->assertRedirect(route('settings.backups.index'))
            ->assertSessionHas('error', trans('admin/settings/message.backup.file_not_found'));
    }

    public function test_settings_backup_delete_rejects_nested_filename_input(): void
    {
        config(['app.lock_passwords' => false]);
        config(['app.allow_backup_delete' => 'true']);

        $this->actingAs(User::factory()->superuser()->create())
            ->delete('/admin/backups/delete/..')
            ->assertRedirect(route('settings.backups.index'))
            ->assertSessionHas('error', trans('admin/settings/message.backup.file_not_found'));
    }

    public function test_settings_backup_restore_rejects_nested_filename_input(): void
    {
        config(['app.lock_passwords' => false]);

        $this->actingAs(User::factory()->superuser()->create())
            ->post('/admin/backups/restore/..')
            ->assertRedirect(route('settings.backups.index'))
            ->assertSessionHas('error', trans('admin/settings/message.backup.file_not_found'));
    }

    public function test_storage_proxy_blocks_path_traversal_segments(): void
    {
        $this->withoutMiddleware();

        Storage::disk('public')->put('proxy-safe/example.txt', 'ok');

        $this->get('/storage-proxy/..%2Fproxy-safe%2Fexample.txt')
            ->assertNotFound();
    }

    public function test_storage_proxy_serves_valid_public_path(): void
    {
        $this->withoutMiddleware();

        Storage::disk('public')->put('proxy-safe/example-valid.txt', 'ok');

        $response = $this->get('/storage-proxy/proxy-safe/example-valid.txt')
            ->assertOk();

        $this->assertStringContainsString('ok', $response->streamedContent());
    }
}
