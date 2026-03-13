<?php

namespace App\Console\Commands;

use App\Enums\ActionType;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MigrateLicenseSeatQuantitiesInActionLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'snipeit:migrate-license-seat-quantities-in-action-logs
                            {--no-interaction: Do not ask any interactive question}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates quantity field in action_logs table for license seats that were added or deleted.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $query = DB::table('action_logs')
            ->whereIn('action_type', [
                ActionType::AddSeats->value,
                ActionType::DeleteSeats->value,
            ])
            ->where('quantity', '=', 1)
            ->orderBy('id');

        $count = $query->count();

        if ($count === 0) {
            $this->info('Nothing to update');

            return 0;
        }

        $this->info("{$count} logs to update");

        if ($this->option('no-interaction') || $this->confirm('Update quantities in the action log?')) {
            $query->chunk(50, function ($logs) {
                $logs->each(function ($log) {
                    $quantityFromNote = Str::between($log->note, "ed ", " seats");

                    if (!is_numeric($quantityFromNote)) {
                        $this->error('Could not parse quantity from ID: {id}', ['id' => $log->id]);
                    }

                    if ($log->quantity !== (int) $quantityFromNote) {
                        $this->info(vsprintf('Updating id: %s to quantity %s', [
                            'id' => $log->id,
                            'new_quantity' => $quantityFromNote,
                        ]));

                        DB::table('action_logs')->where('id', $log->id)->update(['quantity' => (int) $quantityFromNote]);
                    }
                });
            });
        }

        return 0;
    }
}
