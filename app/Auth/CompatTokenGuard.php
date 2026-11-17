<?php

namespace App\Auth;

use Exception;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\AccessToken;
use Laravel\Passport\Guards\TokenGuard;

/**
 * Pre-upgrade PAT compat shim for the Passport v12 -> v13 UUID migration.
 */
class CompatTokenGuard extends TokenGuard
{
    protected function authenticateViaBearerToken(): ?Authenticatable
    {
        $psr = $this->getPsrRequestViaBearerToken();
        if ($psr === null) {
            return null;
        }

        $clientId = $psr->getAttribute('oauth_client_id');
        $client = $this->clients->findActive($clientId);

        if ($client === null) {
            $jti = $psr->getAttribute('oauth_access_token_id');
            $resolvedClientId = DB::table('oauth_access_tokens')
                ->where('id', $jti)
                ->where('revoked', false)
                ->value('client_id');

            if ($resolvedClientId !== null) {
                $client = $this->clients->findActive($resolvedClientId);
            }
        }

        if (! $client ||
            ($client->provider &&
             $client->provider !== $this->provider->getProviderName())) {
            return null;
        }

        $this->setClient($client);

        $oauthUserId = $psr->getAttribute('oauth_user_id');

        if (empty($oauthUserId) ||
            ($oauthUserId === $clientId && $client->hasGrantType('client_credentials'))) {
            return null;
        }

        try {
            $user = $this->provider->retrieveById($oauthUserId);
        } catch (Exception) {
            return null;
        }

        return $user?->withAccessToken(AccessToken::fromPsrRequest($psr));
    }
}
