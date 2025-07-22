<?php

namespace App\Console\Commands;

use App\Models\Setting;
use Illuminate\Console\Command;

class DisableSAML extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'snipeit:saml-disable';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This is a rescue command that can be used to turn off SAML settings in the event that you managed to lock yourself out using bad SAML settings.';

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
        if ($this->confirm("\n****************************************************\nThis will disable SAML support. You will not be able \nto login with an account that does not exist \nlocally in the Snipe-IT local database. \n****************************************************\n\nDo you wish to continue? [y|N]")) {
            $setting = Setting::getSettings();
            $setting->saml_enabled = 0;
            if ($setting->save()) {
                $this->info('SAML has been set to disabled.');
            } else {
                $this->info('Unable to disable SAML.');
            }
        } else {
            $this->info('Canceled. No actions taken.');
        }
    }
}
