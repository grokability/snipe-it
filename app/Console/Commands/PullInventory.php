<?php

namespace App\Console\Commands;

use App\Models\Actionlog;
use App\Models\SyncAdapterInstance;
use App\SyncAdapters\SyncAdapter;
use App\SyncAdapters\SyncErrorFormatter;
use App\Traits\LocksLongRunningOperation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

class PullInventory extends Command
{
    use LocksLongRunningOperation;

    protected $signature = 'snipeit:pull-inventory
        {adapter? : The adapter instance slug. Omit to pull every enabled instance.}
        {--dry-run : Fetch and log each normalized record but do not create, update, or link any Snipe-IT assets. Overrides the per-instance pull_dry_run config for this one run.}
        {--limit= : Stop after processing this many records per adapter. Useful for sanity-checking a dry run on a large fleet without waiting for a full pull.}';

    protected $description = 'Pull host inventory from configured sync-adapter instances and upsert as Snipe-IT assets.';

    private const TABLE_HEADERS = ['Adapter', 'Status', 'Synced', 'Skipped', 'Errors', 'Elapsed'];

    public function handle(): int
    {
        $slug = $this->argument('adapter');

        if ($slug !== null) {
            $instance = SyncAdapterInstance::query()->where('slug', $slug)->first();

            if ($instance === null) {
                $available = SyncAdapterInstance::query()->pluck('slug')->implode(', ');
                $availableLabel = $available !== '' ? $available : '(none configured)';
                $this->error("Unknown adapter instance \"{$slug}\". Available: {$availableLabel}.");

                return self::FAILURE;
            }

            $rows = [];
            $exit = $this->runInstance($instance, $rows);
            $this->table(self::TABLE_HEADERS, $rows);

            return $exit;
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
        $totalStartedAt = microtime(true);
        $rows = [];

        foreach ($instances as $instance) {
            $adapter = $instance->adapter();
            if ($adapter === null || ! $adapter->isEnabled()) {
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
        $this->info("Ran {$ranCount} enabled instance(s) in {$totalElapsed}.");

        return $anyFailed ? self::FAILURE : self::SUCCESS;
    }

    /**
     * Run a single instance. Appends one row to $rows describing the
     * outcome for the final table render, and returns the shell exit
     * code (SUCCESS or FAILURE) so the iterate-all path can aggregate
     * across many instances. Config-error paths still emit inline
     * $this->error output because those message shapes carry
     * remediation hints that don't fit a table row.
     */
    private function runInstance(SyncAdapterInstance $instance, array &$rows): int
    {
        $slug = $instance->slug;
        $startedAt = microtime(true);

        $adapter = $instance->adapter();
        if ($adapter === null) {
            $this->error("Instance \"{$slug}\" references adapter_type \"{$instance->adapter_type}\" which is not registered.");
            $rows[] = [$slug, 'Config error', '-', '-', self::formatElapsed($startedAt)];

            return self::FAILURE;
        }

        if (! $adapter->isEnabled()) {
            $this->error("Adapter \"{$slug}\" is not active or is missing configuration. Set it up under Settings -> Sync Adapters.");
            $rows[] = [$slug, 'Not active', '-', '-', self::formatElapsed($startedAt)];

            return self::FAILURE;
        }

        // Per-instance mutex. Pull and Push on the same adapter share
        // one key, so a scheduled pull and a UI push on the same
        // instance can't collide. Fleet pulling doesn't block Jamf
        // pulling because the key is scoped to the instance slug.
        // Return SUCCESS on contention so a cron tick that fires
        // during a long UI run isn't a scheduler failure, just a
        // no-op.
        if (! $this->acquireOperationLock($instance->lockKey())) {
            $message = "{$slug} sync skipped: another pull or push is already running for this instance.";
            Log::channel('sync-adapters')->info($message);
            $this->warn($message);
            $rows[] = [$slug, 'Already running', '-', '-', '-', self::formatElapsed($startedAt)];

            return self::SUCCESS;
        }

        try {
            // CLI PHP usually has no time cap, but being explicit is
            // harmless and matches the interactive controller.
            set_time_limit(0);

            $seen = 0;
            $skipped = 0;
            $errors = 0;
            // External ids this pull actually returned
            $seenExternalIds = [];

            Log::channel('sync-adapters')->info("{$slug} sync starting (CLI)");

            // --dry-run flag on the CLI overrides the per-instance config
            // for this one run so admins can preview a sync without first
            // flipping and saving the setting in the web UI. Push the
            // override onto the adapter so any inner isPullDryRun() check
            // honors it too.
            if ($this->option('dry-run') === true) {
                $adapter->withPullDryRunOverride(true);
            }
            $dryRun = $adapter->isPullDryRun();
            if ($dryRun) {
                Log::channel('sync-adapters')->info("{$slug} sync [dry-run]: no assets will be created or updated");
                $this->warn("Pull dry-run is on for {$slug}: records will be logged, no assets will be written.");
            }

            // --limit caps how many records this adapter processes before
            // we break out. Null = no limit. Non-positive values are
            // treated as no-limit so operators don't foot-gun themselves
            // with --limit=0 and get a silent zero-record run.
            $limitInput = $this->option('limit');
            $limit = ($limitInput !== null && (int) $limitInput > 0) ? (int) $limitInput : null;
            if ($limit !== null) {
                Log::channel('sync-adapters')->info("{$slug} sync: --limit set to {$limit}, will stop after that many records");
                $this->warn("Pull limit is {$limit} for {$slug}: run stops after that many records.");
            }

            try {
                Actionlog::withActionSource('sync:' . $slug, function () use ($adapter, $slug, $dryRun, $limit, &$seen, &$skipped, &$errors, &$seenExternalIds) {
                    foreach ($adapter->pull() as $record) {
                        if ($limit !== null && ($seen + $skipped + $errors) >= $limit) {
                            Log::channel('sync-adapters')->info("{$slug} sync: --limit {$limit} reached, stopping early");
                            break;
                        }
                        try {
                            // Per-record "vendor says this is gone"
                            $vendorReportsGone = $adapter->recordReportsRemoved($record);

                            if ($dryRun) {
                                // Mirror of the push dry-run shape: log the
                                // normalized record, tally it as "seen" so the
                                // run summary reports useful counts, skip the
                                // asset-side write entirely.
                                Log::channel('sync-adapters')->info(sprintf(
                                    '%s sync [dry-run]: would upsert record source=%s id=%s hostname=%s serial=%s%s',
                                    $slug,
                                    $record->sourceKey,
                                    $record->sourceId,
                                    $record->hostname ?? '(none)',
                                    $record->hardwareSerial ?? '(none)',
                                    $vendorReportsGone ? ' (vendor reports removed)' : '',
                                ));
                                $seen++;
                                if (!$vendorReportsGone && $record->sourceId !== null && $record->sourceId !== '') {
                                    $seenExternalIds[] = (string) $record->sourceId;
                                }

                                continue;
                            }

                            $result = SyncAdapter::syncFromRecord($record);
                            if ($result === null) {
                                $skipped++;
                            } else {
                                $seen++;
                            }

                            // Record what the vendor returned regardless of
                            // whether syncFromRecord upserted.
                            if (!$vendorReportsGone && $record->sourceId !== null && $record->sourceId !== '') {
                                $seenExternalIds[] = (string) $record->sourceId;
                            }
                        } catch (Throwable $e) {
                            $errors++;
                            $sourceId = $record->sourceId;
                            $message = $e->getMessage();
                            // A single bad record shouldn't take down the run.
                            // Log the offending host and keep going.
                            $this->warn("Failed to sync host {$sourceId}: {$message}");
                            Log::channel('sync-adapters')->warning("{$slug} sync: failed to upsert host {$sourceId}: {$message}");
                        }
                    }
                });
            } catch (Throwable $e) {
                // Brief summary for the UI "Last Synced" field and the
                // console error output.
                $summary = SyncErrorFormatter::summary($e);
                $abortSummary = "Sync aborted: {$summary}";
                $instance->last_synced_at = now();
                $instance->last_sync_result = $abortSummary;
                $instance->save();

                $elapsed = self::formatElapsed($startedAt);
                $this->error("{$slug} {$abortSummary} ({$elapsed})");
                Log::channel('sync-adapters')->warning(
                    "{$slug} sync aborted after {$seen} record(s), {$skipped} skipped, {$errors} error(s), elapsed {$elapsed}: {$summary}",
                    SyncErrorFormatter::logContext($e),
                );
                $rows[] = [$slug, 'Aborted', $seen, $skipped, $errors, $elapsed];

                return self::FAILURE;
            }

            // Auto-archive reconcile (issue #20015). Only runs when
            // the pull completed without abort, actually returned at
            // least one record, and is not dry-run. An empty response
            // or a failed pull must not trip the reconciler, or an
            // API outage would sweep every linked asset to the
            // configured status.
            $reconcileSummary = null;
            if (!$dryRun && $seen > 0 && $adapter->archivesMissingLinks()) {
                try {
                    $recon = $adapter->reconcileMissingLinks($seenExternalIds);
                    $reconcileSummary = trans('admin/settings/sync_adapters.archive_missing_sync_summary', [
                        'reconciled' => $recon['reconciled'],
                        'archived' => $recon['archived'],
                        'flagged_for_review' => $recon['flagged_for_review'],
                        'skipped_confirm' => $recon['skipped_confirm'],
                        'skipped_checked_out' => $recon['skipped_checked_out'],
                    ]);
                    Log::channel('sync-adapters')->info("{$slug} sync: {$reconcileSummary}");
                } catch (Throwable $e) {
                    // The reconcile pass is advisory. A failure here
                    // must not mark the whole pull as failed, so swallow
                    // and log. Admins see it in the sync-adapters log.
                    Log::channel('sync-adapters')->warning(
                        "{$slug} sync: auto-archive reconcile failed: " . SyncErrorFormatter::summary($e),
                        SyncErrorFormatter::logContext($e),
                    );
                }
            }

            // Same lang key as the UI so both paths render the same phrasing
            // on the settings page.
            $result = trans('admin/settings/sync_adapters.sync_complete', [
                'count' => $seen,
                'skipped' => $skipped,
                'errors' => $errors,
            ]);
            if ($reconcileSummary !== null) {
                $result .= ' ' . $reconcileSummary;
            }
            $instance->last_synced_at = now();
            $instance->last_sync_result = $result;
            $instance->save();

            $elapsed = self::formatElapsed($startedAt);
            Log::channel('sync-adapters')->info("{$slug} sync complete: {$seen} record(s) processed, {$skipped} skipped, {$errors} error(s), elapsed {$elapsed}");

            $status = $errors > 0 ? 'Errors' : 'OK';
            $rows[] = [$slug, $status, $seen, $skipped, $errors, $elapsed];

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
