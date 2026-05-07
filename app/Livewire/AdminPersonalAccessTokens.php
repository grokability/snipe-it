<?php

namespace App\Livewire;

use Illuminate\Support\Facades\DB;
use Livewire\Component;

/**
 * Livewire component for the admin-facing User API Tokens (Personal Access Tokens) table.
 * Displays all personal access tokens across all users, used on the Settings > OAuth page.
 */
class AdminPersonalAccessTokens extends Component
{
    public function render()
    {
        $tokens = DB::table('oauth_access_tokens')
            ->join('oauth_clients', 'oauth_access_tokens.client_id', '=', 'oauth_clients.id')
            ->leftJoin('users', 'oauth_access_tokens.user_id', '=', 'users.id')
            ->where('oauth_clients.personal_access_client', true)
            ->select([
                'oauth_access_tokens.id',
                'oauth_access_tokens.name',
                'oauth_access_tokens.revoked',
                'oauth_access_tokens.created_at',
                'oauth_access_tokens.expires_at',
                'oauth_access_tokens.user_id as token_user_id',
                'oauth_clients.name as client_name',
                'users.id as existing_user_id',
                'users.username as username',
                'users.display_name as display_name',
                'users.deleted_at as user_deleted_at',
            ])
            ->orderByDesc('oauth_access_tokens.created_at')
            ->get();

        return view('livewire.admin-personal-access-tokens', [
            'tokens' => $tokens,
        ]);
    }
}
