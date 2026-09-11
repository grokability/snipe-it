<?php

namespace App\Console\Commands;

use App\Models\SyncAdapterInstance;
use App\SyncAdapters\SyncHostFromAdapter;
use Illuminate\Console\Command;
use Throwable;

class PullInventory extends Command
{
    protected $signature = 'snipeit:pull-inventory {adapter? : The adapter instance slug. Omit to pull every enabled instance.}';

    protected $description = 'Pull host inventory from configured sync-adapter instances and upsert as Snipe-IT assets.';

    public function handle(): int
    {
        $slug = $this->argument('adapter');

        if ($slug !== null) {
            $instance = SyncAdapterInstance::query()->where('slug', $slug)->first();

            if ($instance === null) {
                $available = SyncAdapterInstance::query()->pluck('slug')->implode(', ');
                $this->error(sprintf(
                    'Unknown adapter instance "%s". Available: %s.',
                    $slug,
                    $available !== '' ? $available : '(none configured)',
                ));

                return self::FAILURE;
            }

            return $this->runInstance($instance);
        }

        // No slug argument means "run every enabled instance". Used by
        // the scheduler entry so the whole install syncs off one cron
        // line without having to enumerate instances at boot time
        // (they're user-created and change at runtime).
        $instances = SyncAdapterInstance::query()->orderBy('slug')->get();

        if ($instances->isEmpty()) {
            $this->info('No sync-adapter instances configured.');

            return self::SUCCESS;
        }

        $anyFailed = false;
        $ranCount = 0;

        foreach ($instances as $instance) {
            $adapter = $instance->adapter();
            if ($adapter === null || ! $adapter->isEnabled()) {
                $this->line(sprintf('Skipping %s (not active or not configured).', $instance->slug));

                continue;
            }

            $ranCount++;
            if ($this->runInstance($instance) === self::FAILURE) {
                $anyFailed = true;
            }
        }

        $this->info(sprintf('Ran %d enabled instance(s).', $ranCount));

        return $anyFailed ? self::FAILURE : self::SUCCESS;
    }

    /**
     * Run a single instance. Extracted so the two entry paths (slug
     * arg and iterate-all) share the same body. Returns the shell
     * exit code (SUCCESS or FAILURE) rather than throwing so the
     * iterate-all path can aggregate across many instances.
     */
    private function runInstance(SyncAdapterInstance $instance): int
    {
        $slug = $instance->slug;

        $adapter = $instance->adapter();
        if ($adapter === null) {
            $this->error(sprintf(
                'Instance "%s" references adapter_type "%s" which is not registered.',
                $slug,
                $instance->adapter_type,
            ));

            return self::FAILURE;
        }

        if (! $adapter->isEnabled()) {
            $this->error(sprintf(
                'Adapter "%s" is not active or is missing configuration. Set it up under Settings -> Sync Adapters.',
                $slug,
            ));

            return self::FAILURE;
        }

        // CLI PHP usually has no time cap, but being explicit is
        // harmless and matches the interactive controller.
        set_time_limit(0);

        $seen = 0;
        $errors = 0;

        try {
            foreach ($adapter->pull() as $record) {
                try {
                    SyncHostFromAdapter::run($record);
                    $seen++;
                } catch (Throwable $e) {
                    // A single bad record shouldn't take down the run.
                    // Log the offending host and keep going.
                    $errors++;
                    $this->warn(sprintf(
                        'Failed to sync host %s: %s',
                        $record->sourceId,
                        $e->getMessage(),
                    ));
                }
            }
        } catch (Throwable $e) {
            $abortSummary = sprintf('Sync aborted: %s', $e->getMessage());
            $instance->last_synced_at = now();
            $instance->last_sync_result = $abortSummary;
            $instance->save();

            $this->error(sprintf('%s %s', $slug, $abortSummary));

            return self::FAILURE;
        }

        // Same lang key as the UI so both paths render the same phrasing
        // on the settings page.
        $result = trans('admin/settings/sync_adapters.sync_complete', [
            'count' => $seen,
            'errors' => $errors,
        ]);
        $instance->last_synced_at = now();
        $instance->last_sync_result = $result;
        $instance->save();

        $this->info(sprintf('%s: %s', $slug, $result));

        return $errors > 0 ? self::FAILURE : self::SUCCESS;
    }
}
