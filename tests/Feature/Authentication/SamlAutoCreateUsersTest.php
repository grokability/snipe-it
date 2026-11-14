<?php

namespace Tests\Feature\Authentication;

use App\Models\User;
use App\Services\Saml;
use Tests\TestCase;

class SamlAutoCreateUsersTest extends TestCase
{
    private function samlData(string $username, array $attributes = []): array
    {
        return [
            'nameId' => $username,
            'attributes' => $attributes ?: [
                Saml::CLAIM_GIVEN_NAME => ['Ada'],
                Saml::CLAIM_SURNAME => ['Lovelace'],
                Saml::CLAIM_EMAIL => ['ada@example.com'],
            ],
        ];
    }

    public function test_unknown_user_is_rejected_when_auto_create_is_disabled()
    {
        config(['app.saml_auto_create_users' => false]);

        $this->assertNull(app(Saml::class)->samlLogin($this->samlData('ada@example.com')));
        $this->assertDatabaseMissing('users', ['username' => 'ada@example.com']);
    }

    public function test_unknown_user_is_created_from_claims_when_auto_create_is_enabled()
    {
        config(['app.saml_auto_create_users' => true]);

        $user = app(Saml::class)->samlLogin($this->samlData('ada@example.com'));

        $this->assertNotNull($user);
        $this->assertSame('ada@example.com', $user->username);
        $this->assertSame('Ada', $user->first_name);
        $this->assertSame('Lovelace', $user->last_name);
        $this->assertSame('ada@example.com', $user->email);
        $this->assertEquals(1, $user->activated);
        $this->assertFalse($user->isSuperUser());
        $this->assertFalse($user->isAdmin());
        $this->assertSame($user->noPassword(), $user->password);
    }

    public function test_first_name_falls_back_to_username_when_claim_is_missing()
    {
        config(['app.saml_auto_create_users' => true]);

        $user = app(Saml::class)->samlLogin($this->samlData('ada@example.com', ['unrelated' => ['x']]));

        $this->assertSame('ada@example.com', $user->first_name);
        $this->assertNull($user->email);
    }

    public function test_existing_user_is_returned_without_creating_another()
    {
        config(['app.saml_auto_create_users' => true]);
        $existing = User::factory()->create(['username' => 'ada@example.com']);

        $user = app(Saml::class)->samlLogin($this->samlData('ada@example.com'));

        $this->assertTrue($existing->is($user));
        $this->assertSame(1, User::where('username', 'ada@example.com')->count());
    }

    public function test_deactivated_user_is_not_recreated()
    {
        config(['app.saml_auto_create_users' => true]);
        User::factory()->create(['username' => 'ada@example.com', 'activated' => 0]);

        $this->assertNull(app(Saml::class)->samlLogin($this->samlData('ada@example.com')));
        $this->assertSame(1, User::withTrashed()->where('username', 'ada@example.com')->count());
    }

    public function test_deleted_user_is_not_recreated()
    {
        config(['app.saml_auto_create_users' => true]);
        User::factory()->create(['username' => 'ada@example.com'])->delete();

        $this->assertNull(app(Saml::class)->samlLogin($this->samlData('ada@example.com')));
        $this->assertSame(1, User::withTrashed()->where('username', 'ada@example.com')->count());
    }
}
