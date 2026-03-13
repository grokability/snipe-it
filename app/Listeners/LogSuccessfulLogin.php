<?php

namespace App\Listeners;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Log;
class LogSuccessfulLogin
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  Login  $event
     * @return void
     */
    public function handle(Login $event)
    {
        $now = new Carbon();

        try {
            DB::table('login_attempts')->insert(
                [
                    'username' => $event->user->username,
                    'user_agent' => request()->header('User-Agent'),
                    'remote_ip' => request()->ip(),
                    'successful' => 1,
                    'created_at' => $now,
                ]
            );
        } catch (\Exception $e) {
            Log::debug($e);
        }
    }
}
