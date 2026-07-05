<?php

namespace Tests\Feature\Scim;

use App\Models\Group;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\Passport;
use Tests\TestCase;

class CreateGroupWithMembersTest extends TestCase
{
    public function test_post_group_with_members_in_initial_body_attaches_pivot_rows()
    {
        // Regression for the Rollbar 23000 crash: the SCIM library runs
        // attribute mappers before saving the parent resource, so a
        // POST /scim/v2/Groups body that carries `members` alongside
        // displayName used to write pivot rows with a NULL group_id
        // and blow up with an integrity-constraint violation. The
        // SnipeMutableCollection::add override saves the parent first.
        Passport::actingAs(User::factory()->superuser()->create());

        $memberOne = User::factory()->create();
        $memberTwo = User::factory()->create();

        $response = $this->postJson('/scim/v2/Groups', [
            'schemas' => ['urn:ietf:params:scim:schemas:core:2.0:Group'],
            'displayName' => 'SCIM Group With Members',
            'members' => [
                ['value' => $memberOne->id],
                ['value' => $memberTwo->id],
            ],
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('displayName', 'SCIM Group With Members');

        $group = Group::where('name', 'SCIM Group With Members')->firstOrFail();

        $this->assertDatabaseHas('users_groups', [
            'group_id' => $group->id,
            'user_id' => $memberOne->id,
        ]);
        $this->assertDatabaseHas('users_groups', [
            'group_id' => $group->id,
            'user_id' => $memberTwo->id,
        ]);
        $this->assertSame(0, DB::table('users_groups')
            ->where('group_id', $group->id)
            ->whereNull('user_id')
            ->count());
    }

    public function test_post_group_without_members_still_works()
    {
        // Sanity check that the save-if-unsaved shortcut doesn't break the
        // ordinary create path where no members are attached.
        Passport::actingAs(User::factory()->superuser()->create());

        $response = $this->postJson('/scim/v2/Groups', [
            'schemas' => ['urn:ietf:params:scim:schemas:core:2.0:Group'],
            'displayName' => 'SCIM Group No Members',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('permission_groups', ['name' => 'SCIM Group No Members']);
    }
}
