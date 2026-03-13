<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class PurgeLoginAttempts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'snipeit:purge-logins';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clears the login_attempts table';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if ($this->confirm("\n****************************************************\nTHIS WILL DELETE ALL OF THE YOUR LOGIN ATTEMPT RECORDS. \nThere is NO undo! \n****************************************************\n\nDo you wish to continue? No backsies! [y|N]")) {
            \DB::statement('delete from login_attempts');
        }
    }
}
