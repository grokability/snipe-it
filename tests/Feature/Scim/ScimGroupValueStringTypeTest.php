<?php

namespace Tests\Feature\Scim;

use App\Models\Group;
use App\Models\User;
use Laravel\Passport\Passport;
use Tests\TestCase;

/**
 * RFC 7643 §4.1.2 defines the `value` sub-attribute of SCIM multi-valued
 * complex types as a string. Strict clients (authentik, Okta in some
 * modes) reject the integer form. These regression tests pin both
 * directions. Users resource's groups[].value and Groups resource's
 * members[].value must both serialize as strings, since a sibling fix
 * on only one direction leaves the other as a silent integration-breaker
 * for the next client that validates the response shape.
 *
 * Groups.members[].value regression: #19996 / PR #20005.
 * Users.groups[].value regression: #20006.
 */
class ScimGroupValueStringTypeTest extends TestCase
{
    public function test_users_resource_returns_groups_value_as_string(): void
    {
        Passport::actingAs(User::factory()->superuser()->create());

        $group = Group::factory()->create();
        $user = User::factory()->create();
        $user->groups()->attach($group->id);

        $response = $this->getJson('/scim/v2/Users/'.$user->id);

        $response->assertStatus(200);

        $groups = $response->json('groups');
        $this->assertNotEmpty($groups, 'User should list at least one group.');
        $this->assertIsString($groups[0]['value'], 'groups[].value must be a string per RFC 7643.');
        $this->assertSame((string) $group->id, $groups[0]['value']);
    }

    public function test_groups_resource_returns_members_value_as_string(): void
    {
        Passport::actingAs(User::factory()->superuser()->create());

        $group = Group::factory()->create();
        $user = User::factory()->create();
        $group->users()->attach($user->id);

        $response = $this->getJson('/scim/v2/Groups/'.$group->id);

        $response->assertStatus(200);

        $members = $response->json('members');
        $this->assertNotEmpty($members, 'Group should list at least one member.');
        $this->assertIsString($members[0]['value'], 'members[].value must be a string per RFC 7643.');
        $this->assertSame((string) $user->id, $members[0]['value']);
    }
}
