<?php

namespace App\Auth\Passport;

use Laravel\Passport\Bridge\User as BridgeUser;
use Laravel\Passport\Bridge\UserRepository as BaseUserRepository;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\UserEntityInterface;
use RuntimeException;

/**
 * Passport v13's `Laravel\Passport\Bridge\User::__construct` strictly
 * types `$identifier` as `string`, but Laravel's default
 * `Authenticatable::getAuthIdentifier` returns the primary key as-is
 * (users.id is auto-increment int). Without this adapter, every
 * password-grant token-issuance path fires a TypeError.
 *
 * The parent repository instantiates Bridge\User at three call sites
 * inside getUserEntityByUserCredentials via the fully-qualified class
 * name, so there's no container seam to intercept. The override here
 * is the smallest copy of the parent's body that casts the identifier
 * once at every construction site. Keeps User::getAuthIdentifier()
 * returning int so the rest of the app doesn't have to think about
 * Passport's string requirement.
 *
 * Mirrors the sibling override for AuthorizationController, which has
 * a fourth Bridge\User construction site inline in its authorize()
 * method.
 */
class BridgeUserRepository extends BaseUserRepository
{
    public function getUserEntityByUserCredentials(
        string $username,
        string $password,
        string $grantType,
        ClientEntityInterface $clientEntity
    ): ?UserEntityInterface {
        $provider = $clientEntity->provider ?: config('auth.guards.api.provider');

        if (is_null($model = config('auth.providers.'.$provider.'.model'))) {
            throw new RuntimeException('Unable to determine authentication model from configuration.');
        }

        if (method_exists($model, 'findAndValidateForPassport')) {
            $user = (new $model)->findAndValidateForPassport($username, $password, $clientEntity);

            return $user ? new BridgeUser((string) $user->getAuthIdentifier()) : null;
        }

        $user = method_exists($model, 'findForPassport')
            ? (new $model)->findForPassport($username, $clientEntity)
            : (new $model)->where('email', $username)->first();

        if (! $user) {
            return null;
        }

        if (method_exists($user, 'validateForPassportPasswordGrant')) {
            return $user->validateForPassportPasswordGrant($password)
                ? new BridgeUser((string) $user->getAuthIdentifier())
                : null;
        }

        return $this->hasher->check($password, $user->getAuthPassword())
            ? new BridgeUser((string) $user->getAuthIdentifier())
            : null;
    }
}
