<?php

namespace App\Livewire;

use Livewire\Component;

class PersonalAccessTokens extends Component
{
    public $name;

    public $newTokenString;

    protected $listeners = [
        'openModal' => 'autoFocusModalEvent',
        'resetCreateTokenForm' => 'resetCreateTokenForm',
    ];

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

    /**
     * Clear the form and validation state so reopening the create modal does not
     * pre-fill the just-used name into the input and does not show the previous
     * validation errors.
     */
    public function resetCreateTokenForm(): void
    {
        $this->reset('name');
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.personal-access-tokens', [
            'tokens' => auth()->user()->tokens,
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
        ];
    }

    public function createToken(): void
    {
        $this->validate();

        // PATs on /account/api are REST-only. MCP access flows
        // through admin-registered OAuth clients.
        $newToken = auth()->user()->createToken($this->name, []);

        $this->newTokenString = $newToken->accessToken;

        // Clear the form so reopening the create modal does not
        // pre-fill the just-used name into the input.
        $this->reset('name');

        $this->dispatch('tokenCreated', token: $newToken->accessToken);
    }

    public function deleteToken($tokenId): void
    {
        // this needs safety (though the scope of auth::user might kind of do it...)
        // seems like it does, test more
        auth()->user()->tokens()->find($tokenId)?->delete();
    }
}
