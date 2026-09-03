<?php

namespace App\Services\Oidc;

use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Validates an OIDC JWT (asymmetric RS/ES algorithms only) against the
 * configured trusted issuer(s), audience(s) and JWKS. Returns the claims or
 * null on any failure -- callers never learn *why* (no error oracle); failures
 * are logged server-side.
 *
 * Signing keys are discovered per issuer and cached, so validation does not hit
 * the provider on every request. `signingKeys()` is isolated so tests can seed
 * a local key without a network round-trip.
 */
class OidcTokenValidator
{
    public function validate(string $token): ?array
    {
        $issuers = (array) config('oidc.issuers');
        $audiences = (array) config('oidc.audiences');

        if (empty($issuers) || empty($audiences)) {
            return null;
        }

        // Read the (unverified) issuer first so we can reject untrusted issuers
        // before any crypto, and select the right JWKS for a multi-issuer
        // deployment. The signature is still fully verified below.
        $issuer = $this->unverifiedIssuer($token);
        if ($issuer === null || ! in_array($issuer, $issuers, true)) {
            Log::warning('[OIDC] Rejected bearer token from untrusted issuer', ['iss' => $issuer]);

            return null;
        }

        try {
            $keys = $this->signingKeys($issuer);
            JWT::$leeway = (int) config('oidc.leeway', 60);
            $decoded = (array) JWT::decode($token, $keys);
        } catch (\Throwable $e) {
            Log::warning('[OIDC] Bearer validation failed', ['error' => $e->getMessage()]);

            return null;
        }

        // php-jwt verifies the signature + exp/nbf, but not iss/aud -- do those
        // explicitly.
        if (($decoded['iss'] ?? null) !== $issuer) {
            return null;
        }

        if (! $this->audienceMatches($decoded, $audiences)) {
            Log::warning('[OIDC] Rejected bearer token: audience mismatch');

            return null;
        }

        return $decoded;
    }

    protected function unverifiedIssuer(string $token): ?string
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }

        $payload = json_decode(JWT::urlsafeB64Decode($parts[1]), true);

        return is_array($payload) ? ($payload['iss'] ?? null) : null;
    }

    protected function audienceMatches(array $claims, array $audiences): bool
    {
        $aud = $claims['aud'] ?? null;
        $presented = is_array($aud) ? $aud : [$aud];

        return count(array_intersect($presented, $audiences)) > 0;
    }

    /**
     * Resolve and cache the issuer's signing keys as Firebase\JWT\Key objects.
     * JWKS documents only carry asymmetric public keys, so `none`/HS* can never
     * enter here -- algorithm confusion is structurally impossible.
     */
    protected function signingKeys(string $issuer): array
    {
        $jwksUri = config('oidc.jwks_uri') ?: $this->discoverJwksUri($issuer);
        $defaultAlg = config('oidc.algorithms')[0] ?? 'RS256';

        $jwks = Cache::remember('oidc_jwks_'.md5($jwksUri), 3600, function () use ($jwksUri) {
            return Http::timeout(5)->get($jwksUri)->throw()->json();
        });

        return JWK::parseKeySet($jwks, $defaultAlg);
    }

    protected function discoverJwksUri(string $issuer): string
    {
        $wellKnown = rtrim($issuer, '/').'/.well-known/openid-configuration';

        $doc = Cache::remember('oidc_disco_'.md5($issuer), 3600, function () use ($wellKnown) {
            return Http::timeout(5)->get($wellKnown)->throw()->json();
        });

        if (empty($doc['jwks_uri'])) {
            throw new \RuntimeException('OIDC discovery for '.$issuer.' has no jwks_uri');
        }

        return $doc['jwks_uri'];
    }
}
