<?php

namespace App\Livewire;

use App\Enums\McpScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;

class PersonalAccessTokens extends Component
{
    public $name;

    public $newTokenString;

    /**
     * Scopes selected for the new token. Scopes only gate MCP access.
     * A scopeless PAT is still fully valid for every REST API endpoint,
     * so selectedScopes stays empty by default. Users minting a PAT
     * for an MCP client check whichever capabilities they need. Users
     * minting a PAT for curl against /api/v1/* leave every box
     * unchecked.
     *
     * @var array<string>
     */
    public array $selectedScopes = [];

    protected $listeners = ['openModal' => 'autoFocusModalEvent'];

    /**
     * Route-level middleware on /account/api requires the self.api gate,
     * but snapshot replay to POST /livewire/update bypasses that. Re-check
     * the same gate here so a user without self.api cannot mint a PAT by
     * replaying a valid snapshot obtained elsewhere.
     */
    public function boot(): void
    {
        if (! auth()->user()?->can('self.api')) {
            abort(403);
        }
    }

    // this is just an annoying thing to make the modal input autofocus
    public function autoFocusModalEvent(): void
    {
        $this->dispatch('autoFocusModal');
    }

    public function render()
    {
        return view('livewire.personal-access-tokens', [
            'tokens' => auth()->user()->tokens,
            'availableScopes' => McpScope::forPassport(),
        ]);
    }

    public function rules(): array
    {
        // Scopes are optional: a scopeless token still works against
        // the REST API. Only mcp:* scope checks live on top of the
        // standard auth. When supplied, every entry must be one of
        // the registered MCP scopes.
        return [
            'name' => 'required|string|max:255',
            'selectedScopes' => 'array',
            'selectedScopes.*' => ['string', Rule::in(McpScope::values())],
        ];
    }

    public function createToken(): void
    {
        $this->validate();

        $newToken = auth()->user()->createToken($this->name, $this->selectedScopes);

        $this->newTokenString = $newToken->accessToken;

        $this->dispatch('tokenCreated', token: $newToken->accessToken);
    }

    public function deleteToken($tokenId): void
    {
        // this needs safety (though the scope of auth::user might kind of do it...)
        // seems like it does, test more
        auth()->user()->tokens()->find($tokenId)?->delete();
    }
}
