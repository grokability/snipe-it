<?php

namespace App\Auth;

use App\Services\Oidc\OidcTokenValidator;
use App\Services\Oidc\OidcUserResolver;
use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;

/**
 * Stateless bearer-token guard: validates an `Authorization: Bearer <jwt>`
 * against a trusted OIDC provider and resolves it to a Snipe-IT user. Returns
 * null on any failure so the framework produces a normal 401.
 *
 * Registered as the `oidc` driver in config/auth.php and layered alongside
 * Passport via the `auth:oidc,api` multi-guard, so a route accepts either an
 * OIDC token or a Passport token. Inert until config('oidc.enabled') is true.
 */
class OidcGuard implements Guard
{
    use GuardHelpers;

    protected Request $request;

    protected OidcTokenValidator $validator;

    protected OidcUserResolver $resolver;

    public function __construct(
        UserProvider $provider,
        Request $request,
        OidcTokenValidator $validator,
        OidcUserResolver $resolver
    ) {
        $this->provider = $provider;
        $this->request = $request;
        $this->validator = $validator;
        $this->resolver = $resolver;
    }

    public function user()
    {
        if (! is_null($this->user)) {
            return $this->user;
        }

        if (! config('oidc.enabled')) {
            return null;
        }

        $token = $this->bearerToken();
        if ($token === null) {
            return null;
        }

        $claims = $this->validator->validate($token);
        if ($claims === null) {
            return null;
        }

        return $this->user = $this->resolver->resolve($claims);
    }

    public function validate(array $credentials = [])
    {
        if (empty($credentials['token'])) {
            return false;
        }

        $claims = $this->validator->validate($credentials['token']);

        return $claims !== null && $this->resolver->resolve($claims) !== null;
    }

    protected function bearerToken(): ?string
    {
        $header = (string) $this->request->header('Authorization', '');
        if (stripos($header, 'bearer ') !== 0) {
            return null;
        }

        $token = trim(substr($header, 7));

        return $token !== '' ? $token : null;
    }
}
