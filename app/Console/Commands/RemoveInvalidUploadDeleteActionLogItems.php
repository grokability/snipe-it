<?php

namespace App\Console\Commands;

use App\Models\Actionlog;
use Illuminate\Console\Command;

class RemoveInvalidUploadDeleteActionLogItems extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'snipeit:remove-invalid-upload-delete-action-log-items';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Permanently remove invalid "upload deleted" action log items that have a null filename. This command can potentially result in deleted files being "resurrected" in the UI.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $invalidLogs = Actionlog::query()
            ->where('action_type', 'upload deleted')
            ->whereNull('filename')
            ->withTrashed()
            ->get();

        $this->info("{$invalidLogs->count()} invalid log items found.");

        if ($invalidLogs->count() === 0) {
            return 0;
        }

        $this->table(['ID', 'Action Type', 'Item Type', 'Item ID', 'Created At', 'Deleted At'], $invalidLogs->map(fn($log) => [
            $log->id,
            $log->action_type,
            $log->item_type,
            $log->item_id,
            $log->created_at,
            $log->deleted_at,
        ])->toArray());

        if ($this->confirm("Do you wish to remove {$invalidLogs->count()} log items?")) {
            $invalidLogs->each(fn($log) => $log->forceDelete());
        }

        return 0;
    }
}
