<?php

namespace App\Console\Commands;

use App\Models\Accessory;
use App\Models\Actionlog;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;

class FixBulkAccessoryCheckinActionLogEntries extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'snipeit:fix-bulk-accessory-action-log-entries {--dry-run : Run the sync process but don\'t update the database} {--skip-backup : Skip pre-execution backup}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This script attempts to fix timestamps and missing created_by values for bulk checkin entries in the log table';

    private bool $dryrun = false;
    private bool $skipBackup = false;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->skipBackup = $this->option('skip-backup');
        $this->dryrun = $this->option('dry-run');

        if ($this->dryrun) {
            $this->info('This is a DRY RUN - no changes will be saved.');
            $this->newLine();
        }

        $logs = Actionlog::query()
            // only look for accessory checkin logs
            ->where('item_type', Accessory::class)
            // that were part of a bulk checkin
            ->where('note', 'Bulk checkin items')
            // logs that were improperly timestamped should have created_at in the 1970s
            ->whereYear('created_at', '1970')
            ->get();

        if ($logs->isEmpty()) {
            $this->info('No logs found with incorrect timestamps.');
            return 0;
        }

        $this->info('Found ' . $logs->count() . ' logs with incorrect timestamps:');

        $this->table(
            ['ID', 'Created By', 'Created At', 'Updated At'],
            $logs->map(function ($log) {
                return [
                    $log->id,
                    $log->created_by,
                    $log->created_at,
                    $log->updated_at,
                ];
            })
        );

        if (!$this->dryrun && !$this->confirm('Update these logs?')) {
            return 0;
        }

        if (!$this->dryrun && !$this->skipBackup) {
            $this->info('Backing up the database before making changes...');
            $this->call('snipeit:backup');
        }

        if ($this->dryrun) {
            $this->newLine();
            $this->info('DRY RUN. NOT ACTUALLY UPDATING LOGS.');
        }

        foreach ($logs as $log) {
            $this->newLine();
            $this->info('Processing log id:' . $log->id);

            // created_by was not being set for accessory bulk checkins
            // so let's see if there was another bulk checkin log
            // with the same timestamp and a created_by value we can use.
            if (is_null($log->created_by)) {
                $createdByFromSimilarLog = $this->getCreatedByAttributeFromSimilarLog($log);

                if ($createdByFromSimilarLog) {
                    $this->line(vsprintf('Updating log id:%s created_by to %s', [$log->id, $createdByFromSimilarLog]));
                    $log->created_by = $createdByFromSimilarLog;
                } else {
                    $this->warn(vsprintf('No created_by found for log id:%s', [$log->id]));
                    $this->warn('Skipping updating this log since no similar log was found to update created_by from.');

                    // If we can't find a similar log then let's skip updating it
                    continue;
                }
            }

            $this->line(vsprintf('Updating log id:%s from %s to %s', [$log->id, $log->created_at, $log->updated_at]));
            $log->created_at = $log->updated_at;

            if (!$this->dryrun) {
                Model::withoutTimestamps(function () use ($log) {
                    $log->saveQuietly();
                });
            }
        }

        $this->newLine();

        if ($this->dryrun) {
            $this->info('DRY RUN. NO CHANGES WERE ACTUALLY MADE.');
        }

        return 0;
    }

    /**
     * Hopefully the bulk checkin included other items like assets or licenses
     * so we can use one of those logs to get the correct created_by value.
     *
     * This method attempts to find a bulk check in log that was
     * created at the same time as the log passed in.
     */
    private function getCreatedByAttributeFromSimilarLog(Actionlog $log): null|int
    {
        $similarLog = Actionlog::query()
            ->whereNotNull('created_by')
            ->where([
                'action_type' => 'checkin from',
                'note' => 'Bulk checkin items',
                'target_id' => $log->target_id,
                'target_type' => $log->target_type,
                'created_at' => $log->updated_at,
            ])
            ->first();

        if ($similarLog) {
            return $similarLog->created_by;
        }

        return null;
    }
}
