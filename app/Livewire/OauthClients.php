<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Laravel\Passport\Client;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\TokenRepository;
use Livewire\Component;

class OauthClients extends Component
{
    public $name;
    public $redirect;
    public $editClientId;
    public $editName;
    public $editRedirect;

    public $authorizationError;

    public function render()
    {
        return view('livewire.oauth-clients', [
            'clients' => app(ClientRepository::class)->activeForUser(auth()->id()),
            'authorized_tokens' => app(TokenRepository::class)->forUser(auth()->id())->where('revoked', false),
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

        $this->dispatch('clientCreated');
    }

    public function deleteClient(Client $clientId): void
    {
        // test for safety
        // ->delete must be of type Client - thus the model binding
        if ($clientId->user_id == auth()->id()) {
            app(ClientRepository::class)->delete($clientId);
        } else {
            Log::warning('User ' . auth()->id() . ' attempted to delete client ' . $clientId->id . ' which belongs to user ' . $clientId->created_by);
            $this->authorizationError = 'You are not authorized to delete this client.';
        }
    }

    public function deleteToken($tokenId): void
    {
        $token = app(TokenRepository::class)->find($tokenId);
        if ($token->created_by == auth()->id()) {
            app(TokenRepository::class)->revokeAccessToken($tokenId);
        } else {
            Log::warning('User ' . auth()->id() . ' attempted to delete token ' . $tokenId . ' which belongs to user ' . $token->created_by);
            $this->authorizationError = 'You are not authorized to delete this token.';
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
        if ($client->created_by == auth()->id()) {
            $client->name = $this->editName;
            $client->redirect = $this->editRedirect;
            $client->save();
        } else {
            Log::warning('User ' . auth()->id() . ' attempted to edit client ' . $editClientId->id . ' which belongs to user ' . $client->created_by);
            $this->authorizationError = 'You are not authorized to edit this client.';
        }

        $this->dispatch('clientUpdated');

    }
}
