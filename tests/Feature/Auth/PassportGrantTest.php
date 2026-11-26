<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\Token;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PassportGrantTest extends TestCase
{
    public function test_password_grant_issues_tokens_for_an_integer_user_id(): void
    {
        $user = User::factory()->create();
        $client = app(ClientRepository::class)->createPasswordGrantClient('Password grant test');

        $this->assertIsInt($user->getAuthIdentifier());

        $this->post('/oauth/token', [
            'grant_type' => 'password',
            'client_id' => $client->id,
            'username' => $user->email,
            'password' => 'password',
        ])->assertOk()->assertJsonStructure(['access_token', 'refresh_token', 'token_type', 'expires_in']);

        $this->assertDatabaseHas('oauth_access_tokens', [
            'user_id' => $user->id,
            'client_id' => $client->id,
            'revoked' => false,
        ]);
    }

    public function test_password_grant_rejects_an_incorrect_password(): void
    {
        $user = User::factory()->create();
        $client = app(ClientRepository::class)->createPasswordGrantClient('Password grant test');

        $this->post('/oauth/token', [
            'grant_type' => 'password',
            'client_id' => $client->id,
            'username' => $user->email,
            'password' => 'incorrect-password',
        ])->assertBadRequest()->assertJsonPath('error', 'invalid_grant');

        $this->assertDatabaseMissing('oauth_access_tokens', ['client_id' => $client->id]);
    }

    #[DataProvider('authorization_scopes')]
    public function test_authorization_code_grant_completes_for_an_integer_user_id(string $scope, string $view): void
    {
        $user = User::factory()->create();
        $client = app(ClientRepository::class)->createAuthorizationCodeGrantClient(
            'Authorization grant test',
            ['https://client.example/callback'],
        );

        $this->assertIsInt($user->getAuthIdentifier());

        $this->actingAs($user)->get('/oauth/authorize?'.http_build_query([
            'client_id' => $client->id,
            'redirect_uri' => 'https://client.example/callback',
            'response_type' => 'code',
            'state' => 'test-state',
            'scope' => $scope,
        ]))->assertOk()->assertViewIs($view);

        $approval = $this->post('/oauth/authorize', [
            'client_id' => $client->id,
            'auth_token' => session('authToken'),
            'state' => 'test-state',
        ])->assertRedirect();

        $location = $approval->headers->get('Location');
        $this->assertStringStartsWith('https://client.example/callback?', $location);
        parse_str(parse_url($location, PHP_URL_QUERY), $query);
        $this->assertSame('test-state', $query['state']);
        $this->assertNotEmpty($query['code']);

        $this->post('/oauth/token', [
            'grant_type' => 'authorization_code',
            'client_id' => $client->id,
            'client_secret' => $client->plainSecret,
            'redirect_uri' => 'https://client.example/callback',
            'code' => $query['code'],
        ])->assertOk()->assertJsonStructure(['access_token', 'refresh_token']);

        $token = Token::where('client_id', $client->id)->sole();
        $this->assertEquals($user->id, $token->user_id);
        $this->assertFalse($token->revoked);
        $this->assertSame($scope === '' ? [] : [$scope], $token->scopes);
        $this->assertDatabaseHas('oauth_refresh_tokens', [
            'access_token_id' => $token->id,
            'revoked' => false,
        ]);
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function authorization_scopes(): array
    {
        return [
            'REST consent' => ['', 'passport.authorize'],
            'MCP consent' => ['mcp:use', 'mcp.authorize'],
        ];
    }

    public function test_denied_authorization_does_not_issue_tokens(): void
    {
        $user = User::factory()->create();
        $client = app(ClientRepository::class)->createAuthorizationCodeGrantClient(
            'Denied authorization test',
            ['https://client.example/callback'],
        );

        $this->actingAs($user)->get('/oauth/authorize?'.http_build_query([
            'client_id' => $client->id,
            'redirect_uri' => 'https://client.example/callback',
            'response_type' => 'code',
            'state' => 'denied-state',
        ]))->assertOk();

        $denial = $this->delete('/oauth/authorize', [
            'client_id' => $client->id,
            'auth_token' => session('authToken'),
            'state' => 'denied-state',
        ])->assertRedirect();

        parse_str(parse_url($denial->headers->get('Location'), PHP_URL_QUERY), $query);
        $this->assertSame('access_denied', $query['error']);
        $this->assertSame('denied-state', $query['state']);
        $this->assertArrayNotHasKey('code', $query);
        $this->assertDatabaseMissing('oauth_access_tokens', ['client_id' => $client->id]);
    }
}
