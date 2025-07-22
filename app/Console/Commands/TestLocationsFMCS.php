<?php

namespace App\Console\Commands;

use App\Helpers\Helper;
use Illuminate\Console\Command;

class TestLocationsFMCS extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'snipeit:test-locations-fmcs {--location_id=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test for company ID inconsistencies if FullMultipleCompanySupport with scoped locations will be used.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('This script checks for company ID inconsistencies if Full Multiple Company Support with scoped locations will be used.');
        $this->info('This could take a few moments if have a very large dataset.');
        $this->newLine();

        // if parameter location_id is set, only test this location
        $location_id = null;
        if ($this->option('location_id')) {
            $location_id = $this->option('location_id');
        }

        $mismatched = Helper::test_locations_fmcs(true, $location_id);
        $this->warn(trans_choice('admin/settings/message.location_scoping.mismatch', count($mismatched)));
        $this->newLine();
        $this->info('Edit your locations to associate them with the correct company.');

        $header = ['Type', 'ID', 'Name', 'Checkout Type',  'Company ID', 'Item Company', 'Item Location', 'Location Company', 'Location Company ID'];
        sort($mismatched);

        $this->table($header, $mismatched);

    }

}
