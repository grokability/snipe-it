<?php

namespace Tests\Feature\Fmcs;

use App\Models\User;
use Tests\TestCase;

/**
 * Regression coverage for GitHub issue #19192, part 3 of 4.
 *
 * The User gate lives in SaveUserRequest, not on the model $rules
 * array (Users don't run through ValidatingTrait the same way the other
 * Companyable models do because pivot memberships live on company_user).
 * Exercise the HTTP endpoints directly to lock in that strict FMCS
 * rejects a blank company_ids for a non-superuser and accepts it for a
 * superuser.
 */
class FmcsStrictUsersHttpTest extends TestCase
{
    public function test_strict_fmcs_rejects_empty_company_ids_for_non_superuser()
    {
        $this->settings->enableMultipleFullCompanySupport();
        $this->settings->disableFloaterMode();

        $actor = User::factory()->create();
        $username = 'strict-null-target-'.uniqid();

        $this->actingAs($actor)
            ->post(route('users.store'), [
                'first_name' => 'Test',
                'last_name' => 'User',
                'username' => $username,
                'email' => $username.'@example.com',
                'password' => 'SomeGreatPassword-123',
                'password_confirmation' => 'SomeGreatPassword-123',
                // No company_ids submitted.
            ])
            ->assertSessionHasErrors('company_ids');

        $this->assertDatabaseMissing('users', ['username' => $username]);
    }

    public function test_strict_fmcs_allows_empty_company_ids_for_superuser()
    {
        $this->settings->enableMultipleFullCompanySupport();
        $this->settings->disableFloaterMode();

        $actor = User::factory()->superuser()->create();
        $username = 'super-null-'.uniqid();

        $this->actingAs($actor)
            ->post(route('users.store'), [
                'first_name' => 'Superuser-Created',
                'last_name' => 'User',
                'username' => $username,
                'email' => $username.'@example.com',
                'password' => 'SomeGreatPassword-123',
                'password_confirmation' => 'SomeGreatPassword-123',
            ])
            ->assertSessionHasNoErrors('company_ids');
    }
}
