<?php

namespace App\Livewire;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Laravel\Passport\Client;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\Token;
use Livewire\Attributes\Locked;
use Livewire\Component;

class OauthClients extends Component
{
    /**
     * Locked so a client-side snapshot replay cannot flip the section from
     * an admin context (oauth-clients) into a lower-privilege context
     * (authorized-applications) to bypass the boot() authorization gate.
     */
    #[Locked]
    public string $section = 'all';

    public $name;

    public $redirect;

    public $editClientId;

    public $editName;

    public $editRedirect;

    public $authorizationError;

    public function mount(?string $section = null): void
    {
        if ($section !== null) {
            $this->section = $section;
        }
    }

    /**
     * Livewire boot() fires on the initial mount AND on every subsequent
     * POST /livewire/update from the same component instance. Route-level
     * middleware (superuser gate on /admin/oauth) protects the initial page
     * render but NOT snapshot replays that arrive at /livewire/update
     * carrying a valid signed snapshot of this component. Enforce the same
     * authorization here so a low-privilege attacker who obtains a signed
     * snapshot (e.g. from a shared admin page, a proxied response, a
     * partially-leaked prior session) cannot invoke createClient /
     * deleteAuthorizedApplication under their own session and mint /
     * revoke admin-scoped tokens.
     */
    public function boot(): void
    {
        if ($this->showOauthClients() && ! auth()->user()?->isSuperUser()) {
            abort(403);
        }
    }

    public function showOauthClients(): bool
    {
        return in_array($this->section, ['all', 'oauth-clients'], true);
    }

    public function showAuthorizedApplications(): bool
    {
        return in_array($this->section, ['all', 'authorized-applications'], true);
    }

    public function render()
    {
        $clients = collect();
        if ($this->showOauthClients()) {
            $clients = Client::query()
                ->orderByDesc('created_at')
                ->get();

            if ($clients->isNotEmpty()) {
                $tokenCountsByClientId = Token::query()
                    ->whereIn('client_id', $clients->pluck('id')->all())
                    ->get(['client_id'])
                    ->groupBy('client_id')
                    ->map->count();

                $clients->each(function ($client) use ($tokenCountsByClientId): void {
                    $client->setAttribute('associated_token_count', (int) ($tokenCountsByClientId[$client->id] ?? 0));
                });
            }
        }

        $authorizedApplications = collect();
        if ($this->showAuthorizedApplications()) {
            $authorizedApplications = Token::query()
                ->where('revoked', false)
                ->with([
                    'client',
                    'client.user' => fn ($q) => $q->withTrashed(),
                ])
                ->orderByDesc('created_at')
                ->get()
                ->unique('client_id')
                ->filter(fn ($token) => $token->client !== null)
                ->map(fn ($token) => (object) [
                    'client_id' => $token->client_id,
                    'client_name' => $token->client->name,
                    'client_owner_id' => $token->client->user_id,
                    'client_owner_display_name' => $token->client->user?->display_name,
                    'client_owner_username' => $token->client->user?->username,
                    'client_owner_deleted_at' => $token->client->user?->deleted_at,
                    'scopes' => $token->scopes,
                    'created_at' => $token->created_at,
                    'expires_at' => $token->expires_at,
                ])
                ->values();
        }

        return view('livewire.oauth-clients', [
            'clients' => $clients,
            'authorizedApplications' => $authorizedApplications,
        ]);
    }

    public function createClient(): void
    {
        // Defense in depth on top of boot(). createClient is only reachable
        // from the admin OAuth-clients management surface, which is
        // superuser-gated at the route level. Snapshot replay to
        // POST /livewire/update can reach here regardless of route gating,
        // so re-check the same authorization here explicitly.
        if (! auth()->user()?->isSuperUser()) {
            abort(403);
        }

        $this->validate([
            'name' => 'required|string|max:255',
            'redirect' => 'required|string|max:255',
        ]);

        app(ClientRepository::class)->create(
            auth()->id(),
            $this->name,
            $this->redirect,
        );

        session()->flash('success', trans('admin/settings/message.oauth.client_created'));
        $this->dispatch('clientCreated');
    }

    public function deleteClient(Client $clientId): void
    {
        // test for safety
        // ->delete must be of type Client - thus the model binding
        if ((auth()->user()?->isSuperUser()) || ($clientId->user_id == auth()->id())) {
            app(ClientRepository::class)->delete($clientId);
            session()->flash('success', trans('admin/settings/message.oauth.client_deleted'));
        } else {
            Log::warning('User '.auth()->id().' attempted to delete client '.$clientId->id.' which belongs to user '.$clientId->created_by);
            $this->authorizationError = trans('admin/settings/message.oauth.client_delete_denied');
        }
    }

    public function deleteAuthorizedApplication(int $clientId): void
    {
        // Only revoke tokens the caller actually owns. Superusers may revoke
        // any authorized-application entry (matches their admin-surface
        // reach). Anyone else is limited to their own access tokens for the
        // named client. Prevents a snapshot replay from calling this method
        // and revoking another user's active tokens (denial of service on
        // legitimate integrations).
        $query = DB::table('oauth_access_tokens')
            ->where('client_id', $clientId)
            ->where('revoked', false);

        if (! auth()->user()?->isSuperUser()) {
            $query->where('user_id', auth()->id());
        }

        $revokedTokenCount = $query->update(['revoked' => true]);

        if ($revokedTokenCount > 0) {
            session()->flash('success', trans('admin/settings/message.oauth.token_deleted'));
        } else {
            Log::warning('User '.auth()->id().' attempted to revoke authorized application client '.$clientId.' without matching active tokens.');
            $this->authorizationError = trans('admin/settings/message.oauth.token_delete_denied');
        }
    }

    public function editClient(Client $editClientId): void
    {
        // Only the client owner or a superuser may pre-fill the edit modal.
        // Without this check, snapshot replay could load any client's name
        // and redirect URI into the component's public props, exposing them
        // via the next render() response.
        if (! auth()->user()?->isSuperUser() && $editClientId->user_id != auth()->id()) {
            abort(403);
        }

        $this->editName = $editClientId->name;
        $this->editRedirect = $editClientId->redirect;

        $this->editClientId = $editClientId->id;

        $this->dispatch('editClient');
    }

    public function updateClient(Client $editClientId): void
    {
        $this->validate([
            'editName' => 'required|string|max:255',
            'editRedirect' => 'required|url|max:255',
        ]);

        $client = app(ClientRepository::class)->find($editClientId->id);
        if ($client->user_id == auth()->id()) {
            $client->name = $this->editName;
            $client->redirect = $this->editRedirect;
            $client->save();
            session()->flash('success', trans('admin/settings/message.oauth.client_updated'));
        } else {
            Log::warning('User '.auth()->id().' attempted to edit client '.$editClientId->id.' which belongs to user '.$client->created_by);
            $this->authorizationError = trans('admin/settings/message.oauth.client_edit_denied');
        }

        $this->dispatch('clientUpdated');

    }
}
