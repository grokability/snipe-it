<?php

namespace Tests\Feature\Scim;

use App\Models\User;
use Laravel\Passport\Passport;
use Tests\TestCase;

/**
 * Regression: `emails` on a SCIM user was serializing as a bare
 * associative object (e.g. `{"value":"...","type":"work","primary":true}`)
 * instead of the JSON array RFC 7643 §4.1.2 mandates for multi-valued
 * attributes. Some SCIM clients treated the scalar shape as reason to
 * construct malformed filter keys against the attribute, which then
 * blew up server-side (see the malformed-key 400 fix in the same PR).
 *
 * SnipeSCIMConfig's `emails` doRead now wraps the object in an outer
 * array so `emails` always serializes as `[{...}]`.
 */
class UserEmailsShapeTest extends TestCase
{
    public function test_emails_is_returned_as_a_json_array_on_get(): void
    {
        Passport::actingAs(User::factory()->superuser()->create());
        $target = User::factory()->create(['email' => 'audit@example.com']);

        $response = $this->getJson('/scim/v2/Users/'.$target->id);

        $response->assertOk();

        $emails = $response->json('emails');

        $this->assertIsArray($emails, '`emails` must be a JSON array per RFC 7643 §4.1.2');
        $this->assertArrayHasKey(0, $emails, '`emails` must be a list-shaped array, not an associative object');
        $this->assertSame('audit@example.com', $emails[0]['value']);
        $this->assertSame('work', $emails[0]['type']);
        $this->assertTrue($emails[0]['primary']);
    }

    public function test_emails_key_is_omitted_when_the_user_has_no_email(): void
    {
        // Guard the "no email → attribute omitted from response" path
        // still holds after the array-wrapping change. Empty array
        // would also be spec-compliant, but the library's Complex::read
        // treats an empty result as "omit the attribute", which is
        // what we want here.
        Passport::actingAs(User::factory()->superuser()->create());
        $target = User::factory()->create(['email' => null]);

        $response = $this->getJson('/scim/v2/Users/'.$target->id);

        $response->assertOk();
        $this->assertArrayNotHasKey('emails', $response->json());
    }

    public function test_emails_shape_is_consistent_across_users_in_list_response(): void
    {
        // Direct reproduction of the ListResponse Snipe pasted from the
        // scim log: multiple users, `emails` on each should be an
        // array. Regression guard against a future one-off doRead
        // returning a bare object when a caller passes an unusual
        // attribute filter.
        Passport::actingAs(User::factory()->superuser()->create());
        User::factory()->count(3)->create();

        $response = $this->getJson('/scim/v2/Users?count=10');

        $response->assertOk();

        foreach ($response->json('Resources') as $resource) {
            if (! array_key_exists('emails', $resource)) {
                continue;
            }
            $this->assertIsArray($resource['emails']);
            $this->assertArrayHasKey(0, $resource['emails']);
        }
    }
}
