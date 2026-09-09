<?php

namespace App\Services\Oidc;

use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Maps a validated OIDC token's claims to an existing Snipe-IT user. The
 * matched user's own permissions then apply unchanged -- this class grants no
 * privileges of its own.
 *
 * Lookup mirrors the SAML path (by username, active + not soft-deleted). A token
 * that resolves to no existing user is rejected -- there is no just-in-time
 * provisioning, so the IdP cannot conjure Snipe-IT accounts.
 */
class OidcUserResolver
{
    public function resolve(array $claims): ?User
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

        if ($user) {
            return $user;
        }

        Log::warning('[OIDC] No active Snipe-IT user for token', ['username' => $username]);

        return null;
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
