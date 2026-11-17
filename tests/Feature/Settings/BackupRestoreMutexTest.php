<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;


class BackupRestoreMutexTest extends TestCase
{
    private const LOCK_KEY = 'backup-restore:running';

    protected function setUp(): void
    {
        parent::setUp();
        Cache::store('file')->forget(self::LOCK_KEY);
    }

    protected function tearDown(): void
    {
        Cache::store('file')->forget(self::LOCK_KEY);
        parent::tearDown();
    }

    public function test_backup_is_rejected_when_the_lock_is_already_held(): void
    {
        Artisan::spy();
        Cache::store('file')->put(self::LOCK_KEY, true, now()->addMinutes(30));

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('settings.backups.create'))
            ->assertSessionHas('error', trans('general.operation_already_running', ['operation' => trans('general.backup_or_restore')]));

        Artisan::shouldNotHaveReceived('call', function ($command) {
            return $command === 'snipeit:backup';
        });
    }

    public function test_restore_is_rejected_when_the_lock_is_already_held(): void
    {
        Artisan::spy();
        Cache::store('file')->put(self::LOCK_KEY, true, now()->addMinutes(30));

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('settings.backups.restore', 'latest-manual-backup-2026-01-01-00-00-00.zip'))
            ->assertSessionHas('error', trans('general.operation_already_running', ['operation' => trans('general.backup_or_restore')]));

        Artisan::shouldNotHaveReceived('call', function ($command) {
            return in_array($command, ['snipeit:restore', 'db:wipe'], true);
        });
    }

    public function test_backup_releases_the_lock_after_the_artisan_call_returns(): void
    {
        Artisan::spy();

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('settings.backups.create'));

        $this->assertFalse(
            Cache::store('file')->has(self::LOCK_KEY),
            'postBackups must release the shared backup+restore lock after Artisan returns.'
        );
    }

    public function test_backups_index_exposes_the_running_state_to_the_view(): void
    {
        Cache::store('file')->put(self::LOCK_KEY, true, now()->addMinutes(30));

        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('settings.backups.index'))
            ->assertOk()
            ->assertViewHas('backupRestoreRunning', true)
            ->assertSee(trans('general.operation_already_running', ['operation' => trans('general.backup_or_restore')]));
    }

    public function test_backups_index_reports_running_false_when_the_lock_is_absent(): void
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('settings.backups.index'))
            ->assertOk()
            ->assertViewHas('backupRestoreRunning', false);
    }
}
