<?php

namespace App\Auth\Passport;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Laravel\Passport\Bridge\User as BridgeUser;
use Laravel\Passport\Contracts\AuthorizationViewResponse;
use Laravel\Passport\Exceptions\OAuthServerException;
use Laravel\Passport\Http\Controllers\AuthorizationController as BaseAuthorizationController;
use League\OAuth2\Server\RequestTypes\AuthorizationRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Companion override to App\Auth\Passport\BridgeUserRepository. The
 * parent's authorize() instantiates Bridge\User inline on line 79
 * (as of Passport v13) via the FQCN, so there's no container seam.
 * Copy of the parent's method, differing only in casting the
 * identifier to string before handing it to Bridge\User.
 *
 * Keep this method in sync with upstream on Passport upgrades. See
 * BridgeUserRepository for the broader rationale on why
 * User::getAuthIdentifier() stays int.
 */
class AuthorizationController extends BaseAuthorizationController
{
    public function authorize(
        ServerRequestInterface $psrRequest,
        Request $request,
        ResponseInterface $psrResponse,
        AuthorizationViewResponse $viewResponse
    ): Response|AuthorizationViewResponse {
        $authRequest = $this->withErrorHandling(
            fn (): AuthorizationRequestInterface => $this->server->validateAuthorizationRequest($psrRequest),
            ($psrRequest->getQueryParams()['response_type'] ?? null) === 'token'
        );

        $prompt = $request->string('prompt')->explode(' ')->map(trim(...))->filter()->values();

        if ($prompt->contains('none')) {
            $prompt = collect(['none']);
        }

        if ($this->guard->guest()) {
            $prompt->contains('none')
                ? throw OAuthServerException::loginRequired($authRequest)
                : $this->promptForLogin($request);
        }

        if ($prompt->contains('login') &&
            ! $request->session()->get('promptedForLogin', false)) {
            $this->guard->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $this->promptForLogin($request);
        }

        $request->session()->forget('promptedForLogin');

        $user = $this->guard->user();
        $authRequest->setUser(new BridgeUser((string) $user->getAuthIdentifier()));

        $scopes = $this->parseScopes($authRequest);
        $client = $this->clients->find($authRequest->getClient()->getIdentifier());

        if ($prompt->doesntContain('consent') &&
            ($client->skipsAuthorization($user, $scopes) || $this->hasGrantedScopes($user, $client, $scopes))) {
            return $this->approveRequest($authRequest, $psrResponse);
        }

        if ($prompt->contains('none')) {
            throw OAuthServerException::consentRequired($authRequest);
        }

        $request->session()->put('authToken', $authToken = Str::random());
        $request->session()->put('authRequest', serialize($authRequest));

        return $viewResponse->withParameters([
            'client' => $client,
            'user' => $user,
            'scopes' => $scopes,
            'request' => $request,
            'authToken' => $authToken,
        ]);
    }
}
