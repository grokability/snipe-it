<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class NormalizeUserNames extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'snipeit:normalize-names';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Normalizes weirdly formatted names as first-letter upercased';

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

        $users = User::get();
            $this->info($users->count() . ' users');

            foreach ($users as $user) {
                $user->first_name = ucwords(strtolower($user->first_name));
                $user->last_name = ucwords(strtolower($user->last_name));
                $user->email = strtolower($user->email);
                $user->save();
        }
    }
}
