<?php

namespace App\Console\Commands;

use App\Models\AssetExternalSource;
use App\Models\SyncAdapterInstance;
use App\SyncAdapters\PushableAdapter;
use Illuminate\Console\Command;
use Throwable;


class PushInventory extends Command
{
    protected $signature = 'snipeit:push-inventory {adapter? : The adapter instance slug. Omit to push every enabled instance that supports push.}';

    protected $description = 'Push Snipe-IT-authoritative field values to configured sync-adapter instances that support pushing.';

    public function handle(): int
    {
        $slug = $this->argument('adapter');

        if ($slug !== null) {
            $instance = SyncAdapterInstance::query()->where('slug', $slug)->first();
            if ($instance === null) {
                $this->error(sprintf('Unknown adapter instance "%s".', $slug));

                return self::FAILURE;
            }

            return $this->runInstance($instance);
        }

        $instances = SyncAdapterInstance::query()->orderBy('slug')->get();
        if ($instances->isEmpty()) {
            $this->info('No sync-adapter instances configured.');

            return self::SUCCESS;
        }

        $anyFailed = false;
        $ranCount = 0;

        foreach ($instances as $instance) {
            $adapter = $instance->adapter();
            if (!$adapter instanceof PushableAdapter) {
                continue;
            }
            if (!$adapter->isEnabled() || !$adapter->canPush()) {
                $this->line(sprintf('Skipping %s (not active, not configured, or push not supported).', $instance->slug));

                continue;
            }

            $ranCount++;
            if ($this->runInstance($instance) === self::FAILURE) {
                $anyFailed = true;
            }
        }

        $this->info(sprintf('Ran push on %d push-enabled instance(s).', $ranCount));

        return $anyFailed ? self::FAILURE : self::SUCCESS;
    }

    /**
     * Run push for one instance. Mirrors the postAdapterPush controller
     * shape: iterate asset_external_sources for this instance in chunks
     * so a large fleet doesn't load everything into memory, invoke
     * $adapter->push() per asset, accumulate counts. Per-asset errors
     * get logged and counted. A single bad asset doesn't abort the run.
     */
    private function runInstance(SyncAdapterInstance $instance): int
    {
        $slug = $instance->slug;

        $adapter = $instance->adapter();
        if (!$adapter instanceof PushableAdapter) {
            $this->error(sprintf('Adapter "%s" does not support push.', $slug));

            return self::FAILURE;
        }
        if (!$adapter->isEnabled()) {
            $this->error(sprintf('Adapter "%s" is not active or is missing configuration.', $slug));

            return self::FAILURE;
        }
        if (!$adapter->canPush()) {
            $this->error(sprintf('Adapter "%s" does not currently support push (vendor gate).', $slug));

            return self::FAILURE;
        }

        set_time_limit(0);

        $pushed = 0;
        $errors = 0;

        try {
            AssetExternalSource::query()
                ->where('source', $slug)
                ->with('asset')
                ->chunkById(200, function ($rows) use ($adapter, &$pushed, &$errors) {
                    foreach ($rows as $row) {
                        $asset = $row->asset;
                        if ($asset === null) {
                            continue;
                        }

                        try {
                            $adapter->push($asset);
                            $pushed++;
                        } catch (Throwable $e) {
                            $errors++;
                            $this->warn(sprintf(
                                'push: asset %d failed: %s',
                                $asset->id,
                                $e->getMessage(),
                            ));
                        }
                    }
                });
        } catch (Throwable $e) {
            $this->error(sprintf('%s push aborted: %s', $slug, $e->getMessage()));

            return self::FAILURE;
        }

        $this->info(sprintf('%s: pushed %d asset(s), %d error(s).', $slug, $pushed, $errors));

        return $errors > 0 ? self::FAILURE : self::SUCCESS;
    }
}
