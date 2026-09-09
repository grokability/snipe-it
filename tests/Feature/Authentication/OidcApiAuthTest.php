<?php

namespace Tests\Feature\Authentication;

use App\Models\User;
use App\Services\Oidc\OidcTokenValidator;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Route;
use Laravel\Passport\Passport;
use PHPUnit\Framework\Assert;
use Tests\TestCase;

/**
 * Provider-agnostic OIDC bearer authentication for the API. Exercised through a
 * throwaway route under the real `auth:oidc,api` multi-guard, with JWTs signed
 * by a local RSA keypair. The validator's JWKS retrieval is stubbed with that
 * keypair's public key (the network/discovery path is unchanged in production),
 * so these tests never touch a network or a real IdP.
 *
 * @SuppressWarnings(PHPMD.TooManyMethods)
 */
class OidcApiAuthTest extends TestCase
{
    private string $issuer = 'https://issuer.test/v2.0';

    private string $audience = 'api://snipe-test';

    private string $kid = 'test-key-1';

    private string $privatePem;

    protected function setUp(): void
    {
        parent::setUp();

        // Snipe redirects unauthenticated requests to the setup wizard (302)
        // until at least one user exists -- create one so we get real 401s.
        User::factory()->create();

        $res = openssl_pkey_new([
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);
        $privatePem = '';
        openssl_pkey_export($res, $privatePem);
        $this->privatePem = $privatePem;
        $publicPem = openssl_pkey_get_details($res)['key'];

        config([
            'oidc.enabled' => true,
            'oidc.issuers' => [$this->issuer],
            'oidc.audiences' => [$this->audience],
            'oidc.algorithms' => ['RS256'],
            'oidc.username_claim' => 'preferred_username',
            'oidc.leeway' => 60,
        ]);

        // Stub JWKS retrieval with the local public key so the validator never
        // hits the network; iss/aud/exp/signature validation still run for real.
        $this->app->instance(OidcTokenValidator::class, new class($publicPem, $this->kid, $this->issuer) extends OidcTokenValidator
        {
            private string $pub;

            private string $signingKid;

            private string $expectedIssuer;

            public function __construct(string $pub, string $signingKid, string $expectedIssuer)
            {
                $this->pub = $pub;
                $this->signingKid = $signingKid;
                $this->expectedIssuer = $expectedIssuer;
            }

            protected function signingKeys(string $issuer): array
            {
                // Keys are only ever served for the issuer the validator already
                // matched against the trusted list -- asserting it here means an
                // untrusted issuer can never reach key resolution unnoticed.
                Assert::assertSame($this->expectedIssuer, $issuer);

                return [$this->signingKid => new Key($this->pub, 'RS256')];
            }
        });

        Route::middleware('auth:oidc,api')->get('/_test/oidc-whoami', function () {
            return response()->json(['username' => auth()->user()->username]);
        });
    }

    private function token(array $overrides = []): string
    {
        $now = time();
        $payload = array_merge([
            'iss' => $this->issuer,
            'aud' => $this->audience,
            'sub' => 'subject-123',
            'preferred_username' => 'oidcuser',
            'iat' => $now,
            'nbf' => $now,
            'exp' => $now + 3600,
        ], $overrides);

        return JWT::encode($payload, $this->privatePem, 'RS256', $this->kid);
    }

    private function bearer(string $token): array
    {
        return ['Authorization' => 'Bearer '.$token];
    }

    public function test_valid_token_authenticates_the_matching_user()
    {
        User::factory()->create(['username' => 'oidcuser', 'activated' => 1]);

        $this->withHeaders($this->bearer($this->token()))
            ->getJson('/_test/oidc-whoami')
            ->assertOk()
            ->assertJson(['username' => 'oidcuser']);
    }

    public function test_bearer_is_ignored_when_oidc_is_disabled()
    {
        config(['oidc.enabled' => false]);
        User::factory()->create(['username' => 'oidcuser', 'activated' => 1]);

        $this->withHeaders($this->bearer($this->token()))
            ->getJson('/_test/oidc-whoami')
            ->assertUnauthorized();
    }

    public function test_unknown_user_is_rejected()
    {
        $this->withHeaders($this->bearer($this->token(['preferred_username' => 'ghost'])))
            ->getJson('/_test/oidc-whoami')
            ->assertUnauthorized();
    }

    public function test_deactivated_user_is_rejected()
    {
        User::factory()->create(['username' => 'oidcuser', 'activated' => 0]);

        $this->withHeaders($this->bearer($this->token()))
            ->getJson('/_test/oidc-whoami')
            ->assertUnauthorized();
    }

    public function test_wrong_audience_is_rejected()
    {
        User::factory()->create(['username' => 'oidcuser', 'activated' => 1]);

        $this->withHeaders($this->bearer($this->token(['aud' => 'api://someone-else'])))
            ->getJson('/_test/oidc-whoami')
            ->assertUnauthorized();
    }

    public function test_untrusted_issuer_is_rejected()
    {
        User::factory()->create(['username' => 'oidcuser', 'activated' => 1]);

        $this->withHeaders($this->bearer($this->token(['iss' => 'https://evil.test/v2.0'])))
            ->getJson('/_test/oidc-whoami')
            ->assertUnauthorized();
    }

    public function test_expired_token_is_rejected()
    {
        User::factory()->create(['username' => 'oidcuser', 'activated' => 1]);
        $past = time() - 300;

        $this->withHeaders($this->bearer($this->token(['iat' => $past, 'nbf' => $past, 'exp' => $past + 60])))
            ->getJson('/_test/oidc-whoami')
            ->assertUnauthorized();
    }

    public function test_malformed_bearer_is_rejected()
    {
        $this->withHeaders($this->bearer('not-a-jwt'))
            ->getJson('/_test/oidc-whoami')
            ->assertUnauthorized();
    }

    public function test_absent_configured_claim_is_not_matched_by_a_different_claim()
    {
        // username_claim is preferred_username. A token that lacks it must NOT
        // authenticate by silently falling back to another claim (e.g. email),
        // even when that other claim matches a real username.
        User::factory()->create(['username' => 'victim@ecuad.ca', 'activated' => 1]);

        $token = $this->token(['preferred_username' => null, 'email' => 'victim@ecuad.ca']);

        $this->withHeaders($this->bearer($token))
            ->getJson('/_test/oidc-whoami')
            ->assertUnauthorized();
    }

    public function test_passport_token_still_authenticates_via_multiguard()
    {
        // The oidc guard must not break the existing Passport path: a route
        // under auth:oidc,api still authenticates a Passport-acting user.
        $user = User::factory()->create(['activated' => 1]);
        Passport::actingAs($user);

        $this->getJson('/_test/oidc-whoami')
            ->assertOk()
            ->assertJson(['username' => $user->username]);
    }
}
