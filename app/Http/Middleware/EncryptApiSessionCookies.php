<?php

namespace App\Http\Middleware;

use Illuminate\Cookie\Middleware\EncryptCookies;
use Laravel\Passport\Passport;

class EncryptApiSessionCookies extends EncryptCookies
{
    /**
     * Leave Passport's encrypted token cookie for its API guard to decrypt.
     */
    public function isDisabled(mixed $name): bool
    {
        return $name === Passport::cookie() || parent::isDisabled($name);
    }
}
