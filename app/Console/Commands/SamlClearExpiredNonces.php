<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SamlNonce;

class SamlClearExpiredNonces extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'saml:clear_expired_nonces';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clears out expired SAML assertions from the saml_nonces table';

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
     * @return int
     */
    public function handle()
    {
        SamlNonce::where('not_valid_after','<=',now())->delete();
        return 0;
    }
}
