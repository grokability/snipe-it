<?php

namespace App\Console\Commands;

use App\Models\CheckoutAcceptance;
use Illuminate\Console\Command;

class PurgeOrphanedCheckoutAcceptances extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'snipeit:purge-orphaned-checkout-acceptances {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deletes pending checkout acceptances where the user has been deleted.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $orphanedAcceptances = CheckoutAcceptance::pending()
            ->whereDoesntHave('assignedTo')
            ->get();

        if ($orphanedAcceptances->isEmpty()) {
            $this->info('No orphaned checkout acceptances found.');

            return 0;
        }

        $this->info('Found ' . $orphanedAcceptances->count() . ' orphaned checkout acceptances.');

        $this->table(['ID', 'Checkoutable Type', 'Assigned To ID'], $orphanedAcceptances->map(function ($acceptance) {
            return [
                $acceptance->id,
                $acceptance->checkoutable_type,
                $acceptance->assigned_to_id,
            ];
        }));

        if (!$this->option('force') && !$this->confirm('Do you wish to permanently delete these ' . $orphanedAcceptances->count() . ' orphaned checkout acceptances?')) {
            $this->info('Aborting.');

            return 0;
        }

        $orphanedAcceptances->each->forceDelete();

        $this->info('Orphaned checkout acceptances have been deleted.');

        return 0;
    }
}
