<?php

namespace Tests\Feature\Users\Ui\BulkActions;

use App\Models\Group;
use App\Models\Asset;
use App\Models\User;
use Tests\TestCase;

class BulkEditUsersTest extends TestCase
{
    public function test_requires_correct_permission()
    {
        $this->actingAs(User::factory()->create())
            ->post(route('users/bulkeditsave'), [
                'ids' => [User::factory()->create()->id],
            ])
            ->assertForbidden();
    }

    public function test_non_admin_cannot_deactivate_admin_via_bulk_edit()
    {
        $actor = User::factory()->editUsers()->create();
        $admin = User::factory()->admin()->create(['activated' => 1]);

        $this->actingAs($actor)
            ->post(route('users/bulkeditsave'), [
                'ids' => [$admin->id],
                'activated' => '0',
            ])
            ->assertRedirect(route('users.index'));

        $this->assertEquals(1, $admin->fresh()->activated);
    }

    public function test_non_admin_cannot_deactivate_superuser_via_bulk_edit()
    {
        $actor = User::factory()->editUsers()->create();
        $superuser = User::factory()->superuser()->create(['activated' => 1]);

        $this->actingAs($actor)
            ->post(route('users/bulkeditsave'), [
                'ids' => [$superuser->id],
                'activated' => '0',
            ])
            ->assertRedirect(route('users.index'));

        $this->assertEquals(1, $superuser->fresh()->activated);
    }

    public function test_admin_cannot_deactivate_superuser_via_bulk_edit()
    {
        $admin = User::factory()->admin()->create();
        $superuser = User::factory()->superuser()->create(['activated' => 1]);

        $this->actingAs($admin)
            ->post(route('users/bulkeditsave'), [
                'ids' => [$superuser->id],
                'activated' => '0',
            ])
            ->assertRedirect(route('users.index'));

        $this->assertEquals(1, $superuser->fresh()->activated);
    }

    public function test_non_admin_can_deactivate_regular_user_via_bulk_edit()
    {
        $actor = User::factory()->editUsers()->create();
        $target = User::factory()->create(['activated' => 1]);

        $this->actingAs($actor)
            ->post(route('users/bulkeditsave'), [
                'ids' => [$target->id],
                'activated' => '0',
            ])
            ->assertRedirect(route('users.index'));

        $this->assertEquals(0, $target->fresh()->activated);
    }

    public function test_admin_can_deactivate_regular_user_via_bulk_edit()
    {
        $admin = User::factory()->admin()->create();
        $target = User::factory()->create(['activated' => 1]);

        $this->actingAs($admin)
            ->post(route('users/bulkeditsave'), [
                'ids' => [$target->id],
                'activated' => '0',
            ])
            ->assertRedirect(route('users.index'));

        $this->assertEquals(0, $target->fresh()->activated);
    }

    public function test_non_admin_cannot_set_ldap_import_on_admin_via_bulk_edit()
    {
        $actor = User::factory()->editUsers()->create();
        $admin = User::factory()->admin()->create(['ldap_import' => 0]);

        $this->actingAs($actor)
            ->post(route('users/bulkeditsave'), [
                'ids' => [$admin->id],
                'ldap_import' => '1',
            ])
            ->assertRedirect(route('users.index'));

        $this->assertEquals(0, $admin->fresh()->ldap_import);
    }

    public function test_non_auth_fields_are_still_updated_for_admin_targets()
    {
        $actor = User::factory()->editUsers()->create();
        $admin = User::factory()->admin()->create(['city' => 'Springfield']);

        $this->actingAs($actor)
            ->post(route('users/bulkeditsave'), [
                'ids' => [$admin->id],
                'city' => 'Shelbyville',
            ])
            ->assertRedirect(route('users.index'));

        $this->assertEquals('Shelbyville', $admin->fresh()->city);
    }

    public function test_superuser_can_assign_groups_via_bulk_edit()
    {
        $group = Group::factory()->create();
        $target = User::factory()->create();

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('users/bulkeditsave'), [
                'ids' => [$target->id],
                'groups' => [$group->id],
            ])
            ->assertRedirect(route('users.index'));

        $this->assertTrue($target->fresh()->groups->contains($group));
    }

    public function test_non_superuser_cannot_assign_groups_via_bulk_edit()
    {
        $group = Group::factory()->create();
        $target = User::factory()->create();

        $this->actingAs(User::factory()->editUsers()->create())
            ->post(route('users/bulkeditsave'), [
                'ids' => [$target->id],
                'groups' => [$group->id],
            ])
            ->assertRedirect(route('users.index'));

        $this->assertFalse($target->fresh()->groups->contains($group));
    }
}
