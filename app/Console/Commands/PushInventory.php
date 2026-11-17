<?php

namespace App\Console\Commands;

use App\Models\AssetExternalSource;
use App\Models\SyncAdapterInstance;
use App\SyncAdapters\PushableSyncAdapter;
use App\Traits\LocksLongRunningOperation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

class PushInventory extends Command
{
    use LocksLongRunningOperation;

    protected $signature = 'snipeit:push-inventory
        {adapter? : The adapter instance slug. Omit to push every enabled instance that supports push.}
        {--dry-run : Build each payload and log it to the sync-adapters log without calling the vendor API. Overrides the per-instance push_dry_run config for this one run.}
        {--limit= : Stop after processing this many assets per adapter. Useful for sanity-checking a dry run on a large fleet without waiting for the whole push.}';

    protected $description = 'Push Snipe-IT-authoritative field values to configured sync-adapter instances that support pushing.';

    private const TABLE_HEADERS = ['Adapter', 'Status', 'Pushed', 'Errors', 'Elapsed'];

    public function handle(): int
    {
        $slug = $this->argument('adapter');

        if ($slug !== null) {
            $instance = SyncAdapterInstance::query()->where('slug', $slug)->first();
            if ($instance === null) {
                $this->error("Unknown adapter instance \"{$slug}\".");

                return self::FAILURE;
            }

            $rows = [];
            $exit = $this->runInstance($instance, $rows);
            $this->table(self::TABLE_HEADERS, $rows);

            return $exit;
        }

        $instances = SyncAdapterInstance::query()->orderBy('slug')->get();
        if ($instances->isEmpty()) {
            $this->info('No sync-adapter instances configured.');

            return self::SUCCESS;
        }

        $anyFailed = false;
        $ranCount = 0;
        $totalStartedAt = microtime(true);
        $rows = [];

        foreach ($instances as $instance) {
            $adapter = $instance->adapter();
            if (!$adapter instanceof PushableSyncAdapter) {
                continue;
            }
            if (! $adapter->isEnabled() || ! $adapter->canPush()) {
                $rows[] = [$instance->slug, 'Skipped', '-', '-', '-'];

                continue;
            }

            $ranCount++;
            if ($this->runInstance($instance, $rows) === self::FAILURE) {
                $anyFailed = true;
            }
        }

        $this->table(self::TABLE_HEADERS, $rows);

        $totalElapsed = self::formatElapsed($totalStartedAt);
        $this->info("Ran push on {$ranCount} push-enabled instance(s) in {$totalElapsed}.");

        return $anyFailed ? self::FAILURE : self::SUCCESS;
    }

    /**
     * Run push for one instance. Mirrors the postAdapterPush controller
     * shape: iterate asset_external_sources for this instance in chunks
     * so a large fleet doesn't load everything into memory, invoke
     * $adapter->push() per asset, accumulate counts. Per-asset errors
     * get logged and counted. A single bad asset doesn't abort the run.
     * Appends one row to $rows describing the outcome for the final
     * table render.
     */
    private function runInstance(SyncAdapterInstance $instance, array &$rows): int
    {
        $slug = $instance->slug;
        $startedAt = microtime(true);

        $adapter = $instance->adapter();
        if (!$adapter instanceof PushableSyncAdapter) {
            $this->error("Adapter \"{$slug}\" does not support push.");
            $rows[] = [$slug, 'Not pushable', '-', '-', self::formatElapsed($startedAt)];

            return self::FAILURE;
        }
        if (! $adapter->isEnabled()) {
            $this->error("Adapter \"{$slug}\" is not active or is missing configuration.");
            $rows[] = [$slug, 'Not active', '-', '-', self::formatElapsed($startedAt)];

            return self::FAILURE;
        }
        if (! $adapter->canPush()) {
            $this->error("Adapter \"{$slug}\" does not currently support push (vendor gate).");
            $rows[] = [$slug, 'Push gated', '-', '-', self::formatElapsed($startedAt)];

            return self::FAILURE;
        }

        // Per-instance mutex. Pull and Push on the same adapter share
        // one key so a scheduled push and a UI pull on the same
        // instance can't collide. Fleet pushing doesn't block Jamf
        // pushing because the key is scoped to the instance slug.
        // SUCCESS on contention so a cron tick during a UI run isn't
        // a scheduler failure.
        if (! $this->acquireOperationLock($instance->lockKey())) {
            $message = "{$slug} push skipped: another pull or push is already running for this instance.";
            Log::channel('sync-adapters')->info($message);
            $this->warn($message);
            $rows[] = [$slug, 'Already running', '-', '-', self::formatElapsed($startedAt)];

            return self::SUCCESS;
        }

        try {

            // --dry-run on the CLI overrides the persisted push_dry_run
            // config so admins can preview a push without saving the
            // setting through the web UI first.
            if ($this->option('dry-run') === true) {
                $adapter->withPushDryRunOverride(true);
                $this->warn("Push dry-run is on for {$slug}: payloads will be logged, no vendor API calls will be made.");
            }

            // --limit caps how many assets this adapter pushes before we
            // break out. Null = no limit. Non-positive values treated as
            // no-limit so operators don't foot-gun themselves with
            // --limit=0 and get a silent zero-asset run.
            $limitInput = $this->option('limit');
            $limit = ($limitInput !== null && (int) $limitInput > 0) ? (int) $limitInput : null;
            if ($limit !== null) {
                $this->warn("Push limit is {$limit} for {$slug}: run stops after that many assets.");
            }

            set_time_limit(0);

            $pushed = 0;
            $errors = 0;

            try {
                AssetExternalSource::query()
                    ->where('source', $slug)
                    ->with('asset')
                    ->chunkById(200, function ($queryRows) use ($adapter, $slug, $limit, &$pushed, &$errors) {
                        foreach ($queryRows as $row) {
                            if ($limit !== null && ($pushed + $errors) >= $limit) {
                                // Returning false from a chunkById callback
                                // tells Eloquent to stop paging after this
                                // chunk, which is what we want for an early
                                // break on --limit.
                                Log::channel('sync-adapters')->info("{$slug} push: --limit {$limit} reached, stopping early");

                                return false;
                            }

                            $asset = $row->asset;
                            if ($asset === null) {
                                continue;
                            }

                            try {
                                $adapter->push($asset);
                                $pushed++;
                            } catch (Throwable $e) {
                                $errors++;
                                $message = $e->getMessage();
                                $this->warn("push: asset {$asset->id} failed: {$message}");
                            }
                        }
                    });
            } catch (Throwable $e) {
                $elapsed = self::formatElapsed($startedAt);
                $message = $e->getMessage();
                $this->error("{$slug} push aborted: {$message} ({$elapsed})");
                $rows[] = [$slug, 'Aborted', $pushed, $errors, $elapsed];

                return self::FAILURE;
            }

            $status = $errors > 0 ? 'Errors' : 'OK';
            $rows[] = [$slug, $status, $pushed, $errors, self::formatElapsed($startedAt)];

            return $errors > 0 ? self::FAILURE : self::SUCCESS;
        } finally {
            $this->releaseOperationLock($instance->lockKey());
        }
    }

    /**
     * Format an elapsed duration for console output. Seconds with one
     * decimal below a minute, minutes-and-seconds above. Sized for
     * scan-at-a-glance output on multi-instance runs where one slow
     * adapter is easier to spot when the units don't drift into the
     * hundreds of seconds.
     */
    private static function formatElapsed(float $startedAt): string
    {
        $elapsed = microtime(true) - $startedAt;
        if ($elapsed < 60) {
            return number_format($elapsed, 1).'s';
        }

        $minutes = (int) floor($elapsed / 60);
        $seconds = number_format($elapsed - ($minutes * 60), 1);

        return $minutes.'m '.$seconds.'s';
    }
}
