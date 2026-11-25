<?php

namespace Tests\Feature\Livewire;

use App\Livewire\OauthClients;
use App\Models\User;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Regression coverage for the admin create-client flow. Covers the
 * two-intent picker (MCP vs Mobile / SPA) and the reveal-once panel
 * the view renders after a successful create.
 *
 * The plaintext client secret is populated on the Passport Client
 * model for exactly one HTTP request (the create call). The reveal
 * panel is the only UI surface that can show it. If a regression
 * ever stops capturing plainSecret here, admins silently lose access
 * to the just-created client's credentials.
 */
class OauthClientsCreateClientTest extends TestCase
{
    public function test_mcp_intent_creates_confidential_client_and_populates_reveal_state()
    {
        $this->actingAs(User::factory()->superuser()->create());

        $component = Livewire::test(OauthClients::class, ['section' => 'oauth-clients'])
            ->set('name', 'Copilot Studio')
            ->set('redirect', 'https://example.test/cb')
            ->set('intent', 'mcp')
            ->call('createClient');

        $component
            ->assertSet('newClientId', fn ($id) => is_string($id) && $id !== '')
            ->assertSet('newClientSecret', fn ($secret) => is_string($secret) && $secret !== '');
    }

    public function test_mobile_intent_creates_public_client_without_secret()
    {
        // Mobile / SPA registers as public (no secret) because the
        // app cannot keep one. plainSecret stays null when Passport
        // creates a public client.
        $this->actingAs(User::factory()->superuser()->create());

        $component = Livewire::test(OauthClients::class, ['section' => 'oauth-clients'])
            ->set('name', 'Public SPA')
            ->set('redirect', 'https://example.test/cb')
            ->set('intent', 'mobile')
            ->call('createClient');

        $component
            ->assertSet('newClientId', fn ($id) => is_string($id) && $id !== '')
            ->assertSet('newClientSecret', null);
    }

    public function test_invalid_intent_fails_validation()
    {
        $this->actingAs(User::factory()->superuser()->create());

        Livewire::test(OauthClients::class, ['section' => 'oauth-clients'])
            ->set('name', 'Bogus Intent')
            ->set('redirect', 'https://example.test/cb')
            ->set('intent', 'something-else')
            ->call('createClient')
            ->assertHasErrors('intent');

        $this->assertDatabaseMissing('oauth_clients', ['name' => 'Bogus Intent']);
    }

    public function test_dismiss_new_client_clears_reveal_state()
    {
        $this->actingAs(User::factory()->superuser()->create());

        $component = Livewire::test(OauthClients::class, ['section' => 'oauth-clients'])
            ->set('name', 'Dismiss Test')
            ->set('redirect', 'https://example.test/cb')
            ->set('intent', 'mcp')
            ->call('createClient')
            ->call('dismissNewClient');

        $component
            ->assertSet('newClientId', null)
            ->assertSet('newClientSecret', null);
    }

    public function test_create_resets_form_fields_for_the_next_use()
    {
        $this->actingAs(User::factory()->superuser()->create());

        Livewire::test(OauthClients::class, ['section' => 'oauth-clients'])
            ->set('name', 'First Client')
            ->set('redirect', 'https://example.test/cb')
            ->set('intent', 'mobile')
            ->call('createClient')
            ->assertSet('name', '')
            ->assertSet('redirect', '')
            ->assertSet('intent', 'mcp');
    }

    public function test_reset_create_client_form_clears_state_and_validation()
    {
        // Covers the hidden.bs.modal-driven reset: an admin who hits
        // Create with an empty name sees the has-error outline and
        // alert-msg, dismisses the modal, and reopens. The form must
        // be back to clean state (no residual red outline, no stale
        // message under the field).
        $this->actingAs(User::factory()->superuser()->create());

        $component = Livewire::test(OauthClients::class, ['section' => 'oauth-clients'])
            ->set('name', '')
            ->set('redirect', '')
            ->call('createClient')
            ->assertHasErrors(['name', 'redirect']);

        $component->call('resetCreateClientForm');

        $component
            ->assertSet('name', null)
            ->assertSet('redirect', null)
            ->assertSet('intent', 'mcp')
            ->assertHasNoErrors();
    }
}
