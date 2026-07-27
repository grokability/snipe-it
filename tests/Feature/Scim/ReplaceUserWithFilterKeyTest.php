<?php

namespace Tests\Feature\Scim;

use App\Models\User;
use Laravel\Passport\Passport;
use Tests\TestCase;

class ReplaceUserWithFilterKeyTest extends TestCase
{
    /**
     * Regression for a Rollbar 500 traced back to a misconfigured SCIM
     * client sending PUT /scim/v2/Users/{id} bodies whose keys are
     * filter expressions (`emails[type eq "work"]`) instead of simple
     * attribute names. The library's Path::shiftAttributePathAttributes()
     * unconditionally calls ->getAttributePath()->shiftAttributeName();
     * getAttributePath() is null when a key parses to a value-path-only
     * expression, so the request crashed with:
     *
     *   Call to a member function shiftAttributeName() on null
     *   in vendor/arietimmerman/laravel-scim-server/src/Parser/Path.php:79
     *
     * SnipeSCIMConfig::replace() (and its sibling ::add()) now guards
     * that condition and throws a SCIMException — the client gets a
     * HTTP 400 pointing at the bad key instead of a 500.
     */
    public function test_put_with_filter_expression_key_returns_400_not_500()
    {
        Passport::actingAs(User::factory()->superuser()->create());
        $target = User::factory()->create();

        $response = $this->putJson('/scim/v2/Users/'.$target->id, [
            'schemas' => ['urn:ietf:params:scim:schemas:core:2.0:User'],
            'userName' => $target->username,
            // The bad key: a value-path filter expression as an attribute
            // key. Reproduces the Path::shiftAttributeName-on-null crash.
            'emails[type eq "work"]' => 'newaddress@example.com',
        ]);

        $response->assertStatus(400);
    }
}
