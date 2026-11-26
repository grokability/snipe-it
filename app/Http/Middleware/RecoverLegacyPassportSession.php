<?php

namespace App\Http\Middleware;

use Illuminate\Contracts\Session\Session;
use Illuminate\Http\Request;
use Illuminate\Session\Middleware\StartSession;
use TypeError;

class RecoverLegacyPassportSession extends StartSession
{
    /**
     * Passport upgrades can leave serialized bridge users with integer IDs in
     * existing sessions. Discard those sessions when the new string property
     * cannot be restored, before authentication or application code runs.
     *
     * This middleware can be removed for a 'stock' StartSession in a later release,
     * whenever we _think_ the appropriate amount of time has elapsed that we can
     * tolerate the error showing back up for users who do an upgrade later.
     *
     * @param  Session  $session
     */
    protected function startSession(Request $request, $session): Session
    {
        try {
            return parent::startSession($request, $session);
        } catch (TypeError $error) {
            if ($error->getMessage() !== 'Cannot assign int to property Laravel\\Passport\\Bridge\\User::$identifier of type string') {
                throw $error;
            }

            $session->invalidate();

            return parent::startSession($request, $session);
        }
    }
}
