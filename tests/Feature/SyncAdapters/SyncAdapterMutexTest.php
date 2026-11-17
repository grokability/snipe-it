<?php

namespace Tests\Feature\SyncAdapters;

use App\Models\SyncAdapterConfig;
use App\Models\SyncAdapterInstance;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Tests\TestCase;


class SyncAdapterMutexTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->flushAllInstanceLocks();
    }

    protected function tearDown(): void
    {
        $this->flushAllInstanceLocks();
        parent::tearDown();
    }

    private function flushAllInstanceLocks(): void
    {
        foreach (SyncAdapterInstance::query()->pluck('slug') as $slug) {
            Cache::store('file')->forget('sync-adapter:'.$slug.':running');
        }
    }

    private function configuredFleet(): SyncAdapterInstance
    {
        $instance = SyncAdapterInstance::where('slug', 'fleet')->firstOrFail();
        $instance->active = true;
        $instance->save();
        SyncAdapterConfig::put($instance->id, 'url', 'https://example.com/fleet');
        SyncAdapterConfig::put($instance->id, 'token', Crypt::encrypt('fake-token'));

        return $instance->fresh();
    }

    public function test_ui_pull_is_rejected_when_the_instance_lock_is_held(): void
    {
        $fleet = $this->configuredFleet();
        Cache::store('file')->put($fleet->lockKey(), true, now()->addMinutes(30));

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('settings.adapters.sync', $fleet))
            ->assertSessionHas('error', trans('general.operation_already_running', [
                'operation' => trans('admin/settings/sync_adapters.sync_adapter_run'),
            ]));
    }

    public function test_locks_are_scoped_per_instance_so_a_second_adapter_can_still_run(): void
    {
        // Lock fleet, trigger jamf. Jamf should proceed past the lock
        // gate (it may fail later for other config reasons, but the
        // mutex itself should NOT reject it).
        $fleet = $this->configuredFleet();
        $jamf = SyncAdapterInstance::where('slug', 'jamf')->firstOrFail();
        Cache::store('file')->put($fleet->lockKey(), true, now()->addMinutes(30));

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('settings.adapters.sync', $jamf))
            ->assertSessionMissing('error', trans('general.operation_already_running', [
                'operation' => trans('admin/settings/sync_adapters.sync_adapter_run'),
            ]));

        // Fleet lock still held, Jamf lock was not permanently set
        // (released by the finally after whatever Jamf's path did).
        $this->assertTrue(Cache::store('file')->has($fleet->lockKey()));
        $this->assertFalse(Cache::store('file')->has($jamf->lockKey()));
    }

    public function test_cli_pull_skips_with_success_exit_when_the_instance_lock_is_held(): void
    {
        $fleet = $this->configuredFleet();
        Cache::store('file')->put($fleet->lockKey(), true, now()->addMinutes(30));

        $this->artisan('snipeit:pull-inventory', ['adapter' => 'fleet'])
            ->assertExitCode(0);
    }
}
