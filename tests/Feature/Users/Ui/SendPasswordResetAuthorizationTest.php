<?php

namespace Tests\Feature\Users\Ui;

use App\Models\Company;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SendPasswordResetAuthorizationTest extends TestCase
{
    public function test_send_password_reset_requires_view_permission(): void
    {
        $target = User::factory()->create();

        $this->actingAs(User::factory()->create())
            ->from(route('users.show', $target))
            ->post(route('users.password', $target->id))
            ->assertForbidden();
    }

    public function test_send_password_reset_does_not_leak_or_notify_cross_company_target_under_fmcs(): void
    {
        Notification::fake();
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $admin = User::factory()->viewUsers()->forCompany($companyA)->create();
        $target = User::factory()->forCompany($companyB)->create();

        $response = $this->actingAs($admin)
            ->from(route('users.show', $target->id))
            ->post(route('users.password', $target->id));

        // No success flash carrying the target email, no reset dispatched,
        // and the response body must not contain the target email string.
        $this->assertNotSame('success', session()->get('_flash.new')[0] ?? null);
        $this->assertNull(session('success'));
        Notification::assertNothingSent();

        // Follow the redirect and check the rendered page doesn't leak
        // the target email into the flash text.
        if ($response->isRedirection()) {
            $followed = $this->actingAs($admin)->get($response->headers->get('Location'));
            $followed->assertDontSee($target->email);
        } else {
            $response->assertDontSee($target->email);
        }
    }

    public function test_send_password_reset_succeeds_for_same_company_target_under_fmcs(): void
    {
        Notification::fake();
        $this->settings->enableMultipleFullCompanySupport();

        $company = Company::factory()->create();
        $admin = User::factory()->viewUsers()->forCompany($company)->create();
        $target = User::factory()->forCompany($company)->create();

        $this->actingAs($admin)
            ->from(route('users.show', $target->id))
            ->post(route('users.password', $target->id))
            ->assertRedirect(route('users.show', $target->id))
            ->assertSessionHas('success');

        Notification::assertSentTo($target, ResetPassword::class);
    }

    public function test_send_password_reset_authorize_blocks_when_query_scope_is_bypassed(): void
    {
        // A superuser bypasses CompanyableScope on User queries. Even so,
        // for a non-existent user id the endpoint must not error out or
        // emit misleading output that leaks scope. Guarding sendPasswordReset
        // with an explicit view authorize keeps the failure path uniform
        // regardless of how User::find resolves.
        Notification::fake();

        $admin = User::factory()->superuser()->create();

        $this->actingAs($admin)
            ->from(route('users.index'))
            ->post(route('users.password', 999_999_999))
            ->assertRedirect();

        Notification::assertNothingSent();
    }
}
