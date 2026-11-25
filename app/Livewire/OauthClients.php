<?php

namespace App\Livewire;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Laravel\Passport\Client;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\Token;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Renderless;
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

    /**
     * Toggle for the Approved OAuth Clients tab. When false, revoked
     * clients are hidden from the table (default). When true, they're
     * included so admins can see historical registrations that were
     * revoked via the per-client delete action.
     */
    public bool $showRevoked = false;

    /**
     *   mcp     : Confidential client. External app has a backend
     *             that can keep a secret.
     *   mobile  : Public client. No secret. Admin tells the app to
     *             use PKCE for the code exchange.
     *
     * Scopes are deliberately NOT collected here. Scopes are on the
     * TOKEN, not to the client row (Passport does not have a
     * per-client scope allow-list in this schema). The external app
     * requests whichever scopes it needs at authorize time, the user
     * consents on the Snipe-IT consent screen, and the resulting
     * token carries exactly those scopes.
     */
    public string $intent = 'mcp';
    
    public ?string $newClientId = null;

    public ?string $newClientSecret = null;

    protected $listeners = [
        'resetCreateClientForm' => 'resetCreateClientForm',
        'resetEditClientForm' => 'resetEditClientForm',
    ];

    public function mount(?string $section = null): void
    {
        if ($section !== null) {
            $this->section = $section;
        }

        // Pick up the ?revoked=1 URL param set by the Show Revoked
        // bs-table button in oauthButtons. Matches the archived-toggle
        // pattern on other list views (query-param-driven, page reload
        // on toggle).
        $this->showRevoked = request()->boolean('revoked');
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
            // The OAuth Clients tab lists approved + non-revoked
            // third-party OAuth clients. Excludes:
            //   - Pending/rejected clients (own tab for pending,
            //     dead rows for rejected until the pruner sweeps)
            //   - Personal access clients (Passport internal plumbing
            //     for the PAT grant, with no user-visible redirect;
            //     the Personal Access Tokens tab handles PATs)
            //   - Revoked clients unless $showRevoked is toggled on
            // Toggle semantics mirror the Show Archived pattern on
            // other list views. Default state shows only active (non-
            // revoked) clients. Toggle shows ONLY revoked clients,
            // not revoked PLUS active in the same list.
            $clients = Client::query()
                ->where('status', \App\Models\OAuthClient::STATUS_APPROVED)
                ->where('personal_access_client', false)
                ->where('revoked', $this->showRevoked ? true : false)
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
                    'client_id' => $token->getAttribute('client_id'),
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
            'redirect' => 'required|url|max:255',
            'intent' => ['required', Rule::in(['mcp', 'mobile'])],
        ]);

        // MCP integrations are server-side external apps that need
        // the client_id + secret pair to exchange authorization codes.
        // Mobile / SPA clients cannot keep a secret and use PKCE
        // instead, so they register as public (no secret).
        $confidential = $this->intent === 'mcp';

        $client = app(ClientRepository::class)->createAuthorizationCodeGrantClient(
            name: $this->name,
            redirectUris: [$this->redirect],
            confidential: $confidential,
            user: auth()->user(),
        );

        // plainSecret is a runtime-only attribute Passport sets on
        // the Client model during create, so this is the only point
        // in the request lifecycle where the cleartext value exists.
        // Public clients have no secret, so plainSecret stays null.
        $this->newClientId = (string) $client->id;
        $this->newClientSecret = $client->plainSecret;

        $this->reset(['name', 'redirect']);
        // Explicit reset so the next create defaults back to the mcp
        // intent. Livewire's reset() restores the property to its
        // class default, but spelling it out keeps the intent next to
        // the other post-create cleanup rather than hidden in a
        // property initializer.
        $this->intent = 'mcp';
        $this->resetValidation();

        session()->flash('success', trans('admin/settings/message.oauth.client_created'));
        $this->dispatch('clientCreated');
    }

    /**
     * Clear the previous info from new modals on same page load.
     */
    #[Renderless]
    public function dismissNewClient(): void
    {
        $this->newClientId = null;
        $this->newClientSecret = null;
    }

    /**
     * Reset the create-modal form state server-side.
     */
    #[Renderless]
    public function resetCreateClientForm(): void
    {
        $this->reset(['name', 'redirect']);
        $this->intent = 'mcp';
        $this->resetValidation();
    }

    /**
     * Same reset hook as the create form, scoped to the edit modal's
     * fields. editName / editRedirect are populated by editClient()
     * when the admin clicks the row's pencil button, so this wipes
     * them along with validation state on dismiss.
     */
    #[Renderless]
    public function resetEditClientForm(): void
    {
        $this->reset(['editName', 'editRedirect', 'editClientId']);
        $this->authorizationError = null;
        $this->resetValidation();
    }

    public function deleteClient(Client $clientId): void
    {
        // test for safety
        // ->delete must be of type Client - thus the model binding
        if ((auth()->user()?->isSuperUser()) || ($clientId->user_id == auth()->id())) {
            app(ClientRepository::class)->delete($clientId);
            session()->flash('success', trans('admin/settings/message.oauth.client_deleted'));
        } else {
            Log::warning('User ' . auth()->id() . ' attempted to delete client ' . $clientId->id . ' which belongs to user ' . $clientId->user_id);
            $this->authorizationError = trans('admin/settings/message.oauth.client_delete_denied');
        }

        // Signals the view's listener to re-init bs-table after the
        // morph strips its toolbar + pagination wrappers. Fires on
        // both the success and the denied branch so the listener
        // runs regardless of outcome.
        $this->dispatch('clientDeleted');
    }

    public function deleteAuthorizedApplication(string $clientId): void
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
        // Match the authorization shape of editClient / deleteClient:
        // superusers can edit any client, other users can only edit
        // their own. Previously this branch gated purely on
        // owner-match, so a superuser editing a DCR'd client (or
        // another user's client) would see the denied message even
        // though they already got past the edit-modal preload.
        if (auth()->user()?->isSuperUser() || $client->user_id == auth()->id()) {
            $client->name = $this->editName;
            $client->redirect = $this->editRedirect;
            $client->save();
            session()->flash('success', trans('admin/settings/message.oauth.client_updated'));
        } else {
            Log::warning('User ' . auth()->id() . ' attempted to edit client ' . $editClientId->id . ' which belongs to user ' . $client->user_id);
            $this->authorizationError = trans('admin/settings/message.oauth.client_edit_denied');
        }

        $this->dispatch('clientUpdated');
    }
}
