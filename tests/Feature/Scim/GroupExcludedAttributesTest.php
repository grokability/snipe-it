<?php

namespace Tests\Feature\Scim;

use App\Models\Group;
use App\Models\User;
use Laravel\Passport\Passport;
use Tests\TestCase;

class GroupExcludedAttributesTest extends TestCase
{
    public function test_group_response_omits_members_when_excluded(): void
    {
        Passport::actingAs(User::factory()->superuser()->create());

        $group = Group::factory()->create();
        $group->users()->attach(User::factory()->create()->id);

        $response = $this->getJson('/scim/v2/Groups/'.$group->id.'?excludedAttributes=members');

        $response->assertOk();
        $response->assertJsonMissingPath('members');
        $this->assertSame($group->name, $response->json('displayName'));
    }

    public function test_group_response_still_lists_members_by_default(): void
    {
        Passport::actingAs(User::factory()->superuser()->create());

        $group = Group::factory()->create();
        $user = User::factory()->create();
        $group->users()->attach($user->id);

        $response = $this->getJson('/scim/v2/Groups/'.$group->id);

        $response->assertOk();
        $this->assertSame((string) $user->id, $response->json('members.0.value'));
    }
}
