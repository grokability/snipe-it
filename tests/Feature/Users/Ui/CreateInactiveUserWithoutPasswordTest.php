<?php

namespace Tests\Feature\Users\Ui;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * When a new user is created deactivated ("This user can login" unchecked),
 * SaveUserRequest skips the password rule entirely for that request. The
 * controller then stores User::noPassword() raw so Hash::check at login
 * always fails. Matches the CSV importer's long-standing "no password"
 * behavior and the API's existing fallback.
 */
class CreateInactiveUserWithoutPasswordTest extends TestCase
{
    public function test_inactive_user_can_be_created_without_a_password(): void
    {
        $actor = User::factory()->createUsers()->create();

        $this->actingAs($actor)
            ->from(route('users.create'))
            ->post(route('users.store'), [
                'first_name' => 'Inactive',
                'last_name' => 'User',
                'username' => 'inactive_user',
                'email' => 'inactive@example.test',
                'notes' => 'created deactivated with no password',
                // activated deliberately omitted
                // password + password_confirmation deliberately omitted
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'username' => 'inactive_user',
            'activated' => 0,
        ]);

        $created = User::where('username', 'inactive_user')->first();
        $this->assertNotNull($created);

        // The stored value must be the raw sentinel, not a bcrypt hash of
        // it. If the controller accidentally bcrypted the sentinel, that
        // hash could be recovered by anyone who knows the sentinel value.
        $this->assertSame($created->noPassword(), $created->password);
    }

    public function test_stored_sentinel_can_never_authenticate(): void
    {
        // Belt-and-suspenders: even if an attacker somehow knew the
        // sentinel string, Hash::check should reject it because a plain
        // string is not a valid bcrypt hash. This holds no matter what
        // the input is.
        $actor = User::factory()->createUsers()->create();

        $this->actingAs($actor)
            ->from(route('users.create'))
            ->post(route('users.store'), [
                'first_name' => 'Sentinel',
                'last_name' => 'Test',
                'username' => 'sentinel_user',
                'email' => 'sentinel@example.test',
                'notes' => 'sentinel storage check',
            ])
            ->assertSessionHasNoErrors();

        $created = User::where('username', 'sentinel_user')->first();

        $this->assertFalse(Hash::check($created->noPassword(), $created->password));
        $this->assertFalse(Hash::check('anything at all', $created->password));
    }

    public function test_active_user_still_requires_a_password(): void
    {
        // Regression guard: the injection only fires when activated is
        // falsy. An active user with an empty password must still fail
        // validation.
        $actor = User::factory()->createUsers()->create();

        $this->actingAs($actor)
            ->from(route('users.create'))
            ->post(route('users.store'), [
                'first_name' => 'Active',
                'last_name' => 'User',
                'username' => 'active_needs_password',
                'email' => 'active@example.test',
                'activated' => '1',
                // password deliberately omitted
            ])
            ->assertSessionHasErrors('password');

        $this->assertDatabaseMissing('users', ['username' => 'active_needs_password']);
    }

    public function test_strict_complexity_rules_do_not_block_no_password_deactivated_create(): void
    {
        // Admin can configure password complexity via settings
        // (letters, numbers, case_diff, symbols, min length, uncommon).
        // Because SaveUserRequest skips the password rule entirely when
        // activated is off, complexity rules never fire on this path.
        // A strict-security-policy admin can still create deactivated
        // users without a password. Turn everything on and verify.
        $this->settings->set([
            'pwd_secure_min' => 16,
            'pwd_secure_uncommon' => 1,
            'pwd_secure_complexity' => 'letters|numbers|case_diff|symbols',
        ]);

        $actor = User::factory()->createUsers()->create();

        $this->actingAs($actor)
            ->from(route('users.create'))
            ->post(route('users.store'), [
                'first_name' => 'Strict',
                'last_name' => 'Policy',
                'username' => 'strict_no_password',
                'email' => 'strict@example.test',
                'notes' => 'strict complexity rules should still allow no-password inactive create',
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', ['username' => 'strict_no_password', 'activated' => 0]);
    }

    public function test_inactive_user_with_a_password_still_gets_it_bcrypted(): void
    {
        // If the operator DOES supply a password even for an inactive
        // user (say they plan to activate later), we should NOT overwrite
        // it with the sentinel. The provided password gets hashed
        // normally.
        $actor = User::factory()->createUsers()->create();

        $this->actingAs($actor)
            ->from(route('users.create'))
            ->post(route('users.store'), [
                'first_name' => 'Inactive',
                'last_name' => 'WithPassword',
                'username' => 'inactive_with_password',
                'email' => 'iw@example.test',
                'notes' => 'has a real password despite being inactive',
                'password' => 'RealPassword_1234!',
                'password_confirmation' => 'RealPassword_1234!',
            ])
            ->assertSessionHasNoErrors();

        $created = User::where('username', 'inactive_with_password')->first();

        $this->assertNotNull($created);
        $this->assertNotSame($created->noPassword(), $created->password);
        $this->assertTrue(Hash::check('RealPassword_1234!', $created->password));
    }
}
