<?php

namespace App\Livewire;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Laravel\Passport\Client;
use Laravel\Passport\ClientRepository;
use Livewire\Component;

class OauthClients extends Component
{
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
                $tokenCountsByClientId = DB::table('oauth_access_tokens')
                    ->whereIn('client_id', $clients->pluck('id')->all())
                    ->selectRaw('client_id, COUNT(*) as token_count')
                    ->groupBy('client_id')
                    ->pluck('token_count', 'client_id');

                $clients->each(function ($client) use ($tokenCountsByClientId): void {
                    $client->setAttribute('associated_token_count', (int) ($tokenCountsByClientId[$client->id] ?? 0));
                });
            }
        }

        $authorizedApplications = collect();
        if ($this->showAuthorizedApplications()) {
            $authorizedTokenSummary = DB::table('oauth_access_tokens as tokens')
                ->where('tokens.revoked', false)
                ->selectRaw('tokens.client_id')
                ->selectRaw('MAX(tokens.scopes) as scopes')
                ->selectRaw('MAX(tokens.created_at) as created_at')
                ->selectRaw('MAX(tokens.expires_at) as expires_at')
                ->groupBy('tokens.client_id');

            $authorizedApplications = DB::table('oauth_clients as clients')
                ->joinSub($authorizedTokenSummary, 'token_summary', function ($join) {
                    $join->on('clients.id', '=', 'token_summary.client_id');
                })
                ->leftJoin('users as creators', 'clients.user_id', '=', 'creators.id')
                ->select([
                    'clients.id as client_id',
                    'clients.name as client_name',
                    'clients.user_id as client_owner_id',
                    'creators.display_name as client_owner_display_name',
                    'creators.username as client_owner_username',
                    'creators.deleted_at as client_owner_deleted_at',
                    'token_summary.scopes',
                    'token_summary.created_at',
                    'token_summary.expires_at',
                ])
                ->orderByDesc('token_summary.created_at')
                ->get();
        }

        return view('livewire.oauth-clients', [
            'clients' => $clients,
            'authorizedApplications' => $authorizedApplications,
        ]);
    }

    public function createClient(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'redirect' => 'required|url|max:255',
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
        $revokedTokenCount = DB::table('oauth_access_tokens')
            ->where('client_id', $clientId)
            ->where('revoked', false)
            ->update(['revoked' => true]);

        if ($revokedTokenCount > 0) {
            session()->flash('success', trans('admin/settings/message.oauth.token_deleted'));
        } else {
            Log::warning('User '.auth()->id().' attempted to revoke authorized application client '.$clientId.' without matching active tokens.');
            $this->authorizationError = trans('admin/settings/message.oauth.token_delete_denied');
        }
    }

    public function editClient(Client $editClientId): void
    {
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
