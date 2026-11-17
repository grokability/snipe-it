<?php

namespace Tests\Feature\Authentication;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

/**
 * Pins that soft-deleting a user cuts off their existing Passport tokens
 * at the auth:api layer.
 *
 * Why this works end-to-end without issuing a signed JWT:
 *
 * Passport v13's TokenGuard verifies the signed bearer, extracts a user
 * id from the token claims, then calls:
 *
 *     $this->provider->retrieveById($userId)
 *
 * The provider is the standard Laravel EloquentUserProvider (we register
 * no custom provider), which runs:
 *
 *     $this->createModel()->newQuery()->find($id)
 *
 * newQuery() applies registered global scopes, and the User model
 * `use SoftDeletes`, which registers SoftDeletingScope. That scope adds
 * `whereNull('deleted_at')` to every default query. If retrieveById
 * returns null, TokenGuard treats the request as unauthenticated and
 * auth:api responds 401.
 *
 * So the two assertions below ARE the proof: trashed users do not
 * resolve through the auth provider path, which means no signed token
 * can authenticate past auth:api, which means no REST or MCP request
 * from that user can succeed.
 */
class SoftDeletedUserApiAccessTest extends TestCase
{
    public function test_trashed_user_is_hidden_from_default_eloquent_query(): void
    {
        $user = User::factory()->create();
        $user->delete();

        $this->assertNull(User::find($user->id));
        $this->assertNotNull(User::withTrashed()->find($user->id));
    }

    public function test_eloquent_user_provider_cannot_retrieve_trashed_user(): void
    {
        $user = User::factory()->create();
        $id = $user->id;

        $provider = Auth::createUserProvider('users');
        $this->assertNotNull($provider->retrieveById($id));

        $user->delete();

        $this->assertNull(
            $provider->retrieveById($id),
            'EloquentUserProvider::retrieveById must return null for a soft-deleted user. '.
            'This is the invariant Passport TokenGuard relies on to refuse tokens for deleted accounts.'
        );
    }
}
