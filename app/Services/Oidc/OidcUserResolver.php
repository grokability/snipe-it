<?php

namespace App\Services\Oidc;

use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Maps a validated OIDC token's claims to an existing Snipe-IT user. The
 * matched user's own permissions then apply unchanged -- this class grants no
 * privileges of its own.
 *
 * A token that resolves to no existing user is rejected -- there is no
 * just-in-time provisioning, so the IdP cannot conjure Snipe-IT accounts.
 */
class OidcUserResolver
{
    public function resolve(array $claims): ?User
    {
        $issuer = is_string($claims['iss'] ?? null) ? $claims['iss'] : null;
        $subject = is_string($claims['sub'] ?? null) ? $claims['sub'] : null;

        if ($issuer === null || $subject === null) {
            Log::warning('[OIDC] Token missing iss or sub claim');

            return null;
        }

        $tupleMatch = $this->findByIssuerAndSubject($issuer, $subject);
        if ($tupleMatch !== null) {
            return $tupleMatch;
        }

        return $this->fallbackUsernameMatch($claims, $issuer, $subject);
    }

    protected function findByIssuerAndSubject(string $issuer, string $subject): ?User
    {
        return User::where('oidc_issuer', '=', $issuer)
            ->where('oidc_subject', '=', $subject)
            ->whereNull('deleted_at')
            ->where('activated', '=', '1')
            ->first();
    }

    /*
     * Username-match fallback for users whose (iss, sub) tuple hasn't been
     * populated yet.
     */
    protected function fallbackUsernameMatch(array $claims, string $issuer, string $subject): ?User
    {
        $username = $this->usernameFromClaims($claims);
        if (empty($username)) {
            Log::warning('[OIDC] Token carries no usable username claim');

            return null;
        }

        $user = User::where('username', '=', $username)
            ->whereNull('deleted_at')
            ->where('activated', '=', '1')
            ->first();

        $verified = User::verifyExactUsernameMatch($user, $username);
        if ($verified === null) {
            Log::warning('[OIDC] No active Snipe-IT user for token', ['username' => $username]);

            return null;
        }

        if (!$this->issuerFallbackAllowed($verified, $issuer)) {
            return null;
        }

        $this->pinIssuerAndSubject($verified, $issuer, $subject);

        return $verified;
    }

    /*
     * Gate the username-only fallback. On a single-issuer deployment there is
     * exactly one trusted iss, so a bare username match cannot collide
     * across trust domains and the fallback is safe.
     */
    protected function issuerFallbackAllowed(User $user, string $tokenIssuer): bool
    {
        $trustedIssuers = array_values(array_filter((array) config('oidc.issuers')));
        if (count($trustedIssuers) < 2) {
            return true;
        }

        $pinned = $user->oidc_issuer ?? null;
        if (!is_string($pinned) || $pinned === '') {
            Log::warning('[OIDC] Rejected fallback: multi-issuer deployment but user has no oidc_issuer pin', [
                'username' => $user->username,
                'token_iss' => $tokenIssuer,
            ]);

            return false;
        }

        if (!hash_equals($pinned, $tokenIssuer)) {
            Log::warning('[OIDC] Rejected fallback: user is pinned to a different issuer', [
                'username' => $user->username,
                'token_iss' => $tokenIssuer,
                'user_pinned_iss' => $pinned,
            ]);

            return false;
        }

        return true;
    }

    /*
     * Write (iss, sub) back to the user after a successful fallback match
     * so the next request goes through the tuple path and the username
     * fallback never fires again for this user.
     */
    protected function pinIssuerAndSubject(User $user, string $issuer, string $subject): void
    {
        if ($user->oidc_issuer === $issuer && $user->oidc_subject === $subject) {
            return;
        }

        $user->oidc_issuer = $issuer;
        $user->oidc_subject = $subject;
        $user->saveQuietly();
    }

    protected function usernameFromClaims(array $claims): ?string
    {
        // Match ONLY the admin-configured claim. Falling back to other claims
        // (upn/email) would let a token that lacks the trusted claim authenticate
        // via a different, possibly less-trustworthy or differently-scoped claim
        // -- an account-confusion / bypass vector. Provider differences belong in
        // config (OIDC_API_USERNAME_CLAIM), not a silent fallback. Absent the
        // configured claim, reject.
        $claimName = config('oidc.username_claim', 'preferred_username');

        return ! empty($claims[$claimName]) ? (string) $claims[$claimName] : null;
    }
}
