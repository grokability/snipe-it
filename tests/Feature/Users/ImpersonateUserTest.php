<?php

namespace Tests\Feature\Users;

use App\Models\Actionlog;
use App\Models\Company;
use App\Models\User;
use Tests\TestCase;

class ImpersonateUserTest extends TestCase
{
    protected function allow(User ...$users): void
    {
        config(['app.user_impersonation_usernames' => array_map(fn ($u) => $u->username, $users)]);
    }

    public function test_impersonate_endpoint_is_disabled_when_list_is_empty()
    {
        config(['app.user_impersonation_usernames' => []]);

        $actor = User::factory()->superuser()->create();
        $target = User::factory()->create(['activated' => 1]);

        $this->actingAs($actor)
            ->post(route('users.impersonate.start', $target))
            ->assertNotFound();

        $this->assertNull(session('impersonator_id'));
    }

    public function test_non_superuser_cannot_impersonate_even_if_id_is_in_list()
    {
        $actor = User::factory()->admin()->create();
        $target = User::factory()->create(['activated' => 1]);
        $this->allow($actor);

        $this->actingAs($actor)
            ->post(route('users.impersonate.start', $target))
            ->assertForbidden();

        $this->assertNull(session('impersonator_id'));
    }

    public function test_superuser_not_in_allowlist_cannot_impersonate()
    {
        $actor = User::factory()->superuser()->create();
        $someoneElse = User::factory()->superuser()->create();
        $target = User::factory()->create(['activated' => 1]);
        $this->allow($someoneElse);

        $this->actingAs($actor)
            ->post(route('users.impersonate.start', $target))
            ->assertForbidden();

        $this->assertNull(session('impersonator_id'));
    }

    public function test_allowlisted_superuser_can_impersonate_activated_user()
    {
        $actor = User::factory()->superuser()->create();
        $target = User::factory()->create(['activated' => 1]);
        $this->allow($actor);

        $response = $this->actingAs($actor)
            ->post(route('users.impersonate.start', $target), ['note' => 'Investigating ticket #4242']);

        $response->assertRedirect(route('home'));
        $this->assertSame($target->id, auth()->id());
        $this->assertSame($actor->id, session('impersonator_id'));

        $log = Actionlog::where('item_type', User::class)
            ->where('item_id', $target->id)
            ->where('created_by', $actor->id)
            ->where('action_type', 'impersonated')
            ->first();

        $this->assertNotNull($log, 'impersonated action log entry not written');
        $this->assertSame('Investigating ticket #4242', $log->note);
    }

    public function test_impersonate_requires_a_note()
    {
        $actor = User::factory()->superuser()->create();
        $target = User::factory()->create(['activated' => 1]);
        $this->allow($actor);

        // No note
        $this->actingAs($actor)
            ->post(route('users.impersonate.start', $target))
            ->assertRedirect(route('users.show', $target))
            ->assertSessionHas('error', trans('admin/users/general.impersonate_note_required'));

        $this->assertSame($actor->id, auth()->id());
        $this->assertNull(session('impersonator_id'));

        // Whitespace-only note
        $this->actingAs($actor)
            ->post(route('users.impersonate.start', $target), ['note' => '   '])
            ->assertRedirect(route('users.show', $target))
            ->assertSessionHas('error', trans('admin/users/general.impersonate_note_required'));

        $this->assertNull(session('impersonator_id'));
    }

    public function test_allowlisted_superuser_cannot_impersonate_deactivated_user()
    {
        $actor = User::factory()->superuser()->create();
        $target = User::factory()->create(['activated' => 0]);
        $this->allow($actor);

        $this->actingAs($actor)
            ->post(route('users.impersonate.start', $target))
            ->assertRedirect(route('users.show', $target));

        $this->assertSame($actor->id, auth()->id());
        $this->assertNull(session('impersonator_id'));
    }

    public function test_allowlisted_superuser_cannot_impersonate_another_superuser()
    {
        $actor = User::factory()->superuser()->create();
        $target = User::factory()->superuser()->create(['activated' => 1]);
        $this->allow($actor);

        $this->actingAs($actor)
            ->post(route('users.impersonate.start', $target))
            ->assertRedirect(route('users.show', $target));

        $this->assertSame($actor->id, auth()->id());
        $this->assertNull(session('impersonator_id'));
    }

    public function test_button_hidden_when_target_is_a_superuser()
    {
        $actor = User::factory()->superuser()->create();
        $target = User::factory()->superuser()->create(['activated' => 1]);
        $this->allow($actor);

        $this->actingAs($actor)
            ->get(route('users.show', $target))
            ->assertOk()
            ->assertDontSee('confirmImpersonateModal');
    }

    public function test_confirmation_modal_is_rendered_when_button_is_visible()
    {
        $actor = User::factory()->superuser()->create();
        $target = User::factory()->create(['activated' => 1]);
        $this->allow($actor);

        $this->actingAs($actor)
            ->get(route('users.show', $target))
            ->assertOk()
            ->assertSee('confirmImpersonateModal')
            ->assertSee(trans('admin/users/general.impersonate_confirm_title'))
            ->assertSee('name="note"', false)
            ->assertSee('required', false);
    }

    public function test_allowlisted_superuser_cannot_impersonate_themselves()
    {
        $actor = User::factory()->superuser()->create();
        $this->allow($actor);

        $this->actingAs($actor)
            ->post(route('users.impersonate.start', $actor))
            ->assertRedirect(route('users.show', $actor));

        $this->assertNull(session('impersonator_id'));
    }

    public function test_stop_impersonation_restores_original_user()
    {
        $actor = User::factory()->superuser()->create();
        $target = User::factory()->create(['activated' => 1]);
        $this->allow($actor);

        $this->actingAs($actor)
            ->post(route('users.impersonate.start', $target), ['note' => 'test note'])
            ->assertRedirect(route('home'));

        $this->assertSame($target->id, auth()->id());

        $stop = $this->post(route('users.impersonate.stop'));

        $stop->assertRedirect(route('users.show', $target));
        $this->assertSame($actor->id, auth()->id());
        $this->assertNull(session('impersonator_id'));

        $this->assertDatabaseHas('action_logs', [
            'item_type' => User::class,
            'item_id' => $target->id,
            'created_by' => $actor->id,
            'action_type' => 'stopped impersonating',
        ]);
    }

    public function test_banner_is_visible_after_impersonating_a_non_admin()
    {
        $actor = User::factory()->superuser()->create(['first_name' => 'Sooper', 'last_name' => 'Actor']);
        $target = User::factory()->create(['activated' => 1, 'first_name' => 'Target', 'last_name' => 'User']);
        $this->allow($actor);

        $this->actingAs($actor)
            ->post(route('users.impersonate.start', $target), ['note' => 'test note'])
            ->assertRedirect(route('home'));

        $this->assertSame($actor->id, session('impersonator_id'));

        $follow = $this->followingRedirects()->get(route('home'));

        $follow->assertOk()
            ->assertSee(trans('admin/users/general.impersonating_banner_title'))
            ->assertSee(route('users.impersonate.stop'), false);
    }

    public function test_banner_and_stop_work_across_company_scoping()
    {
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $actor = User::factory()->superuser()->create();
        $actor->companies()->sync([$companyA->id]);

        $target = User::factory()->forCompany($companyB)->create(['activated' => 1]);
        $target->companies()->sync([$companyB->id]);

        $this->allow($actor);

        $this->actingAs($actor)
            ->post(route('users.impersonate.start', $target), ['note' => 'test note'])
            ->assertRedirect(route('home'));

        $this->assertSame($target->id, auth()->id());
        $this->assertSame($actor->id, session('impersonator_id'));

        $follow = $this->followingRedirects()->get(route('home'));
        $follow->assertOk()
            ->assertSee(trans('admin/users/general.impersonating_banner_title'))
            ->assertSee(route('users.impersonate.stop'), false);

        $this->post(route('users.impersonate.stop'))
            ->assertRedirect(route('users.show', $target));

        $this->assertSame($actor->id, auth()->id());
    }

    public function test_stop_impersonation_no_op_when_not_impersonating()
    {
        $actor = User::factory()->create();

        $this->actingAs($actor)
            ->post(route('users.impersonate.stop'))
            ->assertRedirect(route('home'));

        $this->assertSame($actor->id, auth()->id());
    }

    public function test_button_hidden_when_list_is_empty()
    {
        config(['app.user_impersonation_usernames' => []]);

        $actor = User::factory()->superuser()->create();
        $target = User::factory()->create(['activated' => 1]);

        $this->actingAs($actor)
            ->get(route('users.show', $target))
            ->assertOk()
            ->assertDontSee(route('users.impersonate.start', $target));
    }

    public function test_button_visible_to_allowlisted_superuser()
    {
        $actor = User::factory()->superuser()->create();
        $target = User::factory()->create(['activated' => 1]);
        $this->allow($actor);

        $this->actingAs($actor)
            ->get(route('users.show', $target))
            ->assertOk()
            ->assertSee(route('users.impersonate.start', $target), false);
    }

    public function test_button_hidden_from_non_allowlisted_superuser()
    {
        $actor = User::factory()->superuser()->create();
        $someoneElse = User::factory()->superuser()->create();
        $target = User::factory()->create(['activated' => 1]);
        $this->allow($someoneElse);

        $this->actingAs($actor)
            ->get(route('users.show', $target))
            ->assertOk()
            ->assertDontSee(route('users.impersonate.start', $target));
    }

    public function test_button_hidden_from_non_superuser_in_allowlist()
    {
        $actor = User::factory()->admin()->create();
        $target = User::factory()->create(['activated' => 1]);
        $this->allow($actor);

        $this->actingAs($actor)
            ->get(route('users.show', $target))
            ->assertOk()
            ->assertDontSee(route('users.impersonate.start', $target));
    }
}
