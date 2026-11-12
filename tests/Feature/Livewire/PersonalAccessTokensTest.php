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

    public function test_default_has_no_scopes_selected()
    {
        // Scopeless PATs remain valid for the REST API. Defaulting to
        // no scopes means users minting a token for curl against
        // /api/v1/* can skip the scope checkboxes entirely. MCP users
        // explicitly check what they need.
        $this->actingAs(User::factory()->selfApi()->create());

        Livewire::test(PersonalAccessTokens::class)
            ->assertSet('selectedScopes', []);
    }

    public function test_rules_do_not_require_a_scope()
    {
        // Scopeless PATs stay valid for REST API callers. Testing
        // the rules() method directly avoids the full Livewire
        // createToken() path, which needs a seeded Passport personal
        // access client that this test harness does not have.
        $this->actingAs(User::factory()->selfApi()->create());

        $component = new PersonalAccessTokens;
        $rules = $component->rules();

        $this->assertStringNotContainsString('required', (string) $rules['selectedScopes']);
        $this->assertStringNotContainsString('min:1', (string) $rules['selectedScopes']);
    }

    public function test_cannot_create_token_with_an_unknown_scope()
    {
        $this->actingAs(User::factory()->selfApi()->create());

        Livewire::test(PersonalAccessTokens::class)
            ->set('name', 'bogus-scope-token')
            ->set('selectedScopes', ['not:a:real:scope'])
            ->call('createToken')
            ->assertHasErrors('selectedScopes.0');
    }
}
