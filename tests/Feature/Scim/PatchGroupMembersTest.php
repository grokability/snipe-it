<?php

namespace Tests\Feature\Scim;

use App\Models\Group;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\Passport;
use Tests\TestCase;

/**
 * Regression for memory exhaustion on PATCH /scim/v2/Groups/{id} against a
 * group with a large membership.
 *
 * Entra sends one member per operation, so the payload is tiny. The cost is
 * everything the request reads around it: `Collection::doRead()` lazy-loads
 * the whole membership to serialize `members`, and `MutableCollection::add()`
 * calls `syncWithoutDetaching()` — whose `sync()` reads every pivot row —
 * then `$object->load()`, hydrating the membership a second time. Two full
 * hydrations plus a sweep of the pivot table is enough to exhaust the PHP
 * memory limit before the request can respond.
 *
 * These assert the shape of the queries rather than a memory figure: a PATCH
 * touching one member must never read the membership bounded only by
 * group_id. Five members is deliberate — the defect is the unbounded query,
 * not the size of any one group, and a shape assertion catches it
 * deterministically without a slow, flaky memory measurement.
 */
class PatchGroupMembersTest extends TestCase
{
    public function test_adding_a_member_does_not_read_the_whole_membership()
    {
        $group = $this->groupWithMembers(5);
        $newMember = User::factory()->create();

        $queries = [];
        // Identifier quoting differs between the sqlite and MySQL runs.
        DB::listen(function ($query) use (&$queries) {
            $queries[] = str_replace(['`', '"'], '', $query->sql);
        });

        $response = $this->patchJson('/scim/v2/Groups/'.$group->id, [
            'schemas' => ['urn:ietf:params:scim:api:messages:2.0:PatchOp'],
            'Operations' => [
                [
                    'op' => 'add',
                    'path' => 'members',
                    'value' => [['value' => (string) $newMember->id]],
                ],
            ],
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('users_groups', [
            'group_id' => $group->id,
            'user_id' => $newMember->id,
        ]);

        $this->assertSame(
            [],
            array_values(preg_grep('/select users\.\*.*users_groups/', $queries)),
            'Every member was hydrated into a User model.'
        );
        $this->assertNotContains(
            'select * from users_groups where users_groups.group_id = ?',
            $queries,
            'sync() read every pivot row for the group.'
        );
    }

    public function test_removing_a_member_does_not_read_the_whole_membership()
    {
        $group = $this->groupWithMembers(5);
        $departing = $group->users()->first();

        $queries = [];
        // Identifier quoting differs between the sqlite and MySQL runs.
        DB::listen(function ($query) use (&$queries) {
            $queries[] = str_replace(['`', '"'], '', $query->sql);
        });

        $response = $this->patchJson('/scim/v2/Groups/'.$group->id, [
            'schemas' => ['urn:ietf:params:scim:api:messages:2.0:PatchOp'],
            'Operations' => [
                [
                    'op' => 'remove',
                    'path' => 'members[value eq "'.$departing->id.'"]',
                ],
            ],
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseMissing('users_groups', [
            'group_id' => $group->id,
            'user_id' => $departing->id,
        ]);

        $this->assertSame(
            [],
            array_values(preg_grep('/select users\.\*.*users_groups/', $queries)),
            'Every member was hydrated into a User model.'
        );
        $this->assertNotContains(
            'select * from users_groups where users_groups.group_id = ?',
            $queries,
            'sync() read every pivot row for the group.'
        );
    }

    public function test_patch_response_still_lists_every_member()
    {
        // The membership is read without hydrating User models, so this locks
        // the representation that rewrite has to keep producing.
        $group = $this->groupWithMembers(5);
        $newMember = User::factory()->create();

        $response = $this->patchJson('/scim/v2/Groups/'.$group->id, [
            'schemas' => ['urn:ietf:params:scim:api:messages:2.0:PatchOp'],
            'Operations' => [
                [
                    'op' => 'add',
                    'path' => 'members',
                    'value' => [['value' => (string) $newMember->id]],
                ],
            ],
        ]);

        $response->assertStatus(200);

        $members = $response->json('members');

        $this->assertCount(6, $members);
        $this->assertEqualsCanonicalizing(
            $group->users()->pluck('users.id')->all(),
            array_column($members, 'value')
        );
        $this->assertSame(
            route('scim.resource', ['resourceType' => 'Users', 'resourceObject' => $newMember->id]),
            collect($members)->firstWhere('value', $newMember->id)['$ref']
        );
    }

    private function groupWithMembers(int $count): Group
    {
        Passport::actingAs(User::factory()->superuser()->create());

        $group = Group::factory()->create();
        $group->users()->attach(User::factory()->count($count)->create()->modelKeys());

        return $group;
    }
}
