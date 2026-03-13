<?php

namespace App\Http\Transformers;

use App\Helpers\Helper;

class LoginAttemptsTransformer
{
    public function transformLoginAttempts($login_attempts, $total)
    {
        $array = [];
        foreach ($login_attempts as $login_attempt) {
            $array[] = self::transformLoginAttempt($login_attempt);
        }

        return (new DatatablesTransformer)->transformDatatables($array, $total);
    }

    public function transformLoginAttempt($login_attempt)
    {
        if ($login_attempt) {
            $array = [
                'id' => (int) $login_attempt->id,
                'username' => e($login_attempt->username),
                'user_agent' => e($login_attempt->user_agent),
                'remote_ip' => (! config('app.lock_passwords')) ? e($login_attempt->remote_ip) : '--',
                'successful' => e($login_attempt->successful),
                'created_at' => Helper::getFormattedDateObject($login_attempt->created_at, 'datetime'),
            ];

            return $array;
        }
    }
}
