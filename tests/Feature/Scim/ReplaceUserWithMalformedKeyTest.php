<?php

namespace Tests\Feature\Scim;

use App\Models\User;
use Laravel\Passport\Passport;
use Tests\TestCase;

class ReplaceUserWithMalformedKeyTest extends TestCase
{
    /**
     * Regression for a Rollbar 500 traced to keys the Tmilos filter parser
     * can't parse at all, distinct from the ReplaceUserWithFilterKeyTest
     * case where the parser succeeds but returns a value-path-only Path.
     * Here the key starts with `[` with no leading attribute name, and
     * Parser::parse() itself throws:
     *
     *   Tmilos\ScimFilterParser\Error\FilterException [Syntax Error]
     *   line 0, col 0: Error: Expected attribute path, got '['
     *
     * SnipeSCIMConfig::add() and ::replace() now wrap the Parser::parse
     * calls in try/catch and rethrow as SCIMException, so the client sees
     * a 400 pointing at the malformed key instead of an uncaught 500.
     */
    public function test_put_with_malformed_key_returns_400_not_500(): void
    {
        Passport::actingAs(User::factory()->superuser()->create());
        $target = User::factory()->create();

        $response = $this->putJson('/scim/v2/Users/'.$target->id, [
            'schemas' => ['urn:ietf:params:scim:schemas:core:2.0:User'],
            'userName' => $target->username,
            // The bad key: a bare value-path filter with no leading
            // attribute name. Reproduces the FilterException-from-parser
            // 500 seen in Rollbar.
            '[type eq "work"]' => 'newaddress@example.com',
        ]);

        $response->assertStatus(400);
    }

    public function test_patch_with_malformed_add_value_key_returns_400_not_500(): void
    {
        // Same malformed key shape but arriving through the PATCH add()
        // path (SnipeRootComplex::add()) instead of PUT replace(). Azure
        // and Entra send add/replace ops without a top-level path field
        // and put attribute keys inside `value`, so this exercises the
        // sibling try/catch we added.
        Passport::actingAs(User::factory()->superuser()->create());
        $target = User::factory()->create();

        $response = $this->patchJson('/scim/v2/Users/'.$target->id, [
            'schemas' => ['urn:ietf:params:scim:api:messages:2.0:PatchOp'],
            'Operations' => [
                [
                    'op' => 'add',
                    'value' => [
                        '[type eq "work"]' => 'newaddress@example.com',
                    ],
                ],
            ],
        ]);

        $response->assertStatus(400);
    }
}
