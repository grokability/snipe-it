<?php

namespace Tests\Feature\Livewire;

use App\Livewire\PersonalAccessTokens;
use App\Models\User;
use Livewire\Livewire;
use Tests\TestCase;

class PersonalAccessTokensTest extends TestCase
{
    public function test_the_component_can_render()
    {
        $this->actingAs(User::factory()->selfApi()->create());

        Livewire::test(PersonalAccessTokens::class)
            ->assertStatus(200);
    }

    public function test_create_token_validation_fails_without_name()
    {
        $this->actingAs(User::factory()->selfApi()->create());

        Livewire::test(PersonalAccessTokens::class)
            ->set('name', '')
            ->call('createToken')
            ->assertHasErrors(['name' => 'required']);
    }

    /**
     * Regression for the Livewire snapshot-replay class of vuln reported
     * by PizzaStev3 (2026-07-31). Without the boot() gate, a user who was
     * blocked from /account/api by the self.api middleware could still
     * mint a PAT by replaying a valid signed snapshot of the
     * PersonalAccessTokens component to POST /livewire/update.
     *
     * boot() fires on both the initial mount AND every subsequent
     * /livewire/update, so a 403 at mount here implies the same 403 on any
     * replayed action call.
     */
    public function test_user_without_self_api_permission_cannot_mount_or_replay_the_component()
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(PersonalAccessTokens::class)
            ->assertStatus(403);
    }
}
