<?php

namespace App\Console\Commands;

use App\Models\CheckoutAcceptance;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PurgeEulaPDFs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'snipeit:purge-eula-pdfs  
                            {--older-than-days= : The number of days we should delete before } 
                            {--force : Skip the interactive yes/no prompt for confirmation}
                            {--dryrun : Show the records that would be deleted but don\'t update the database or delete files from disk}
                            {--with-output : Display the results in a table in your console}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This purges signature files and EULAs from the system if they are older than the date passed with --older-than-days=.';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $before = $this->option('older-than-days');

        if (($before=='') || (!is_numeric($before))) {
            return $this->error('ERROR: You must pass a valid number for --older-than-days (example: snipeit:purge-eula-pdfs --older-than-days=365.)');
        }

        $interval_date = Carbon::now()->subDays($before);
        $signature_path = 'private_uploads/signatures/';
        $eula_path = 'private_uploads/eula-pdfs/';

        if (!Storage::exists($eula_path)) {
            $this->fail('The storage directory "'.$eula_path.'" does not exist. No EULA files will be deleted.');
        }

        if (!Storage::exists($signature_path)) {
            $this->fail('The storage directory "'.$signature_path.'" does not exist. No signature files will be deleted.');
        }


        if ($this->option('dryrun')) {
            $this->info('This script is being run with the --dryrun option. No files or records will be deleted.');

        }
        $acceptances = CheckoutAcceptance::HasFiles()->where('updated_at','<', $interval_date)->with('assignedTo')->get();

        if (!$this->option('force')) {
            if ($this->confirm("\n****************************************************\nTHIS WILL DELETE ALL OF THE SIGNATURES AND EULA PDF FILES SINCE $interval_date. \nThere is NO undo! \n****************************************************\n\nDo you wish to continue? No backsies! [y|N]")) {
            }
        }



        if ($acceptances->count() == 0) {
            return $this->warn('There are no item acceptances with signatures or EULA PDFs from before '.$interval_date);
        }

        $this->info(number_format($acceptances->count()) . ' EULA PDFs from before '.$interval_date.' will be purged');

        if (!$this->option('with-output')) {
            $this->info('Run this command with the --with-output option to see the full list in the console.');
        } else {
            $this->table(
                [
                    trans('general.user'),
                    trans('general.type'),
                    trans('general.item'),
                    trans('general.category'),
                    trans('general.accepted_date'),
                    trans('general.declined_date'),
                    trans('general.signature'),
                    trans('general.filename'),

                ],
                $acceptances->map(fn($acceptance) => [
                    trans('general.user') => $acceptance->assignedTo->display_name,
                    trans('general.type') => $acceptance->display_checkoutable_type,
                    trans('general.item') => $acceptance->checkoutable_type::find($acceptance->checkoutable_id)->display_name,
                    trans('general.category') => $acceptance->checkoutable_category_name,
                    trans('general.accepted_date') => $acceptance->accepted_at,
                    trans('general.declined_date') => $acceptance->declined_at,
                    trans('general.signature') => $acceptance->signature_filename,
                    trans('general.filename') => $acceptance->stored_eula_file,
                ])
            );
        }



        foreach ($acceptances as $acceptance) {

            $signature_file = $signature_path.$acceptance->signature_filename;
            $eula_file = $eula_path.$acceptance->stored_eula_file;

            if (Storage::exists($signature_file)) {
                if (!$this->option('dryrun')) {
                    Storage::delete($signature_file);
                }
            } else {
                $this->error('The file "'. $signature_file.'" does not exist.');
            }


            if (Storage::exists($eula_file)) {
                if (!$this->option('dryrun')) {
                    Storage::delete($eula_file);
                }
            } else {
                $this->error('The file "'.$eula_file.'" does not exist.');
            }

            if (!$this->option('dryrun')) {
                $acceptance->delete();
            }
        }

    }
}
