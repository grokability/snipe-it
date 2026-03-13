<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Livewire\Component;

class PersonalAccessTokens extends Component
{
    public $name;
    public $newTokenString;

    protected $listeners = ['openModal' => 'autoFocusModalEvent'];

    //this is just an annoying thing to make the modal input autofocus
    public function autoFocusModalEvent(): void
    {
        $this->dispatch('autoFocusModal');
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

       $newToken = auth()->user()->createToken($this->name);

       $this->newTokenString = $newToken->accessToken;

        $this->dispatch('tokenCreated', token: $newToken->accessToken);
    }

    public function deleteToken($tokenId): void
    {
        //this needs safety (though the scope of auth::user might kind of do it...)
        //seems like it does, test more
        auth()->user()->tokens()->find($tokenId)?->delete();
    }
}
