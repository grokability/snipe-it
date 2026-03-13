<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class KillAllSessions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'snipeit:global-logout  {--force : Skip the danger prompt; assuming you enter "y"} ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will destroy all web sessions on disk and will force a re-login for all users.';

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

        if (!$this->option('force') && !$this->confirm("****************************************************\nTHIS WILL FORCE A LOGIN FOR ALL LOGGED IN USERS.\n\nAre you SURE you wish to continue? ")) {
            return $this->error("Session loss not confirmed");
        }

        $session_files = glob(storage_path("framework/sessions/*"));

        $count = 0;
        foreach ($session_files as $file) {

            if (is_file($file))
                unlink($file);
                $count++;
        }
        \DB::table('users')->update(['remember_token' => null]);

        $this->info($count. ' sessions cleared!');

    }
}
