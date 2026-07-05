<?php

namespace Tests\Feature\Users\Ui\BulkActions;

use App\Models\Actionlog;
use App\Models\Group;
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

    public function test_bulk_edit_logs_general_field_changes_to_activity_report()
    {
        $target = User::factory()->create(['city' => 'Springfield', 'jobtitle' => 'Engineer']);

        $existingLogIds = Actionlog::where('item_type', User::class)
            ->where('item_id', $target->id)
            ->pluck('id');

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('users/bulkeditsave'), [
                'ids' => [$target->id],
                'city' => 'Shelbyville',
                'jobtitle' => 'Senior Engineer',
            ])
            ->assertRedirect(route('users.index'));

        $log = Actionlog::where('item_type', User::class)
            ->where('item_id', $target->id)
            ->where('action_type', 'update')
            ->whereNotIn('id', $existingLogIds)
            ->first();

        $this->assertNotNull($log, 'Bulk edit should produce an activity log entry');

        $meta = json_decode($log->log_meta, true);
        $this->assertArrayHasKey('city', $meta);
        $this->assertEquals('Springfield', $meta['city']['old']);
        $this->assertEquals('Shelbyville', $meta['city']['new']);
        $this->assertArrayHasKey('jobtitle', $meta);
        $this->assertEquals('Engineer', $meta['jobtitle']['old']);
        $this->assertEquals('Senior Engineer', $meta['jobtitle']['new']);
    }

    public function test_bulk_edit_logs_null_clear_operations_to_activity_report()
    {
        $target = User::factory()->create(['notes' => 'Some notes', 'phone' => '555-1234']);

        $existingLogIds = Actionlog::where('item_type', User::class)
            ->where('item_id', $target->id)
            ->pluck('id');

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('users/bulkeditsave'), [
                'ids' => [$target->id],
                'null_notes' => '1',
                'null_phone' => '1',
            ])
            ->assertRedirect(route('users.index'));

        $log = Actionlog::where('item_type', User::class)
            ->where('item_id', $target->id)
            ->where('action_type', 'update')
            ->whereNotIn('id', $existingLogIds)
            ->first();

        $this->assertNotNull($log, 'Bulk clear should produce an activity log entry');

        $meta = json_decode($log->log_meta, true);
        $this->assertArrayHasKey('notes', $meta);
        $this->assertNull($meta['notes']['new']);
        $this->assertArrayHasKey('phone', $meta);
        $this->assertNull($meta['phone']['new']);
    }

    public function test_bulk_edit_logs_auth_field_changes_for_eligible_users()
    {
        $target = User::factory()->create(['activated' => 1]);

        $existingLogIds = Actionlog::where('item_type', User::class)
            ->where('item_id', $target->id)
            ->pluck('id');

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('users/bulkeditsave'), [
                'ids' => [$target->id],
                'activated' => '0',
                'city' => 'Portland',
            ])
            ->assertRedirect(route('users.index'));

        $logs = Actionlog::where('item_type', User::class)
            ->where('item_id', $target->id)
            ->where('action_type', 'update')
            ->whereNotIn('id', $existingLogIds)
            ->get();

        $this->assertCount(1, $logs, 'All changes should appear in a single log entry');

        $meta = json_decode($logs->first()->log_meta, true);
        $this->assertArrayHasKey('activated', $meta, 'activated change should be logged');
        $this->assertArrayHasKey('city', $meta, 'city change should be logged');
    }

    public function test_bulk_edit_creates_one_log_entry_per_user()
    {
        $targets = User::factory()->count(3)->create(['city' => 'Old City']);

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('users/bulkeditsave'), [
                'ids' => $targets->pluck('id')->all(),
                'city' => 'New City',
            ])
            ->assertRedirect(route('users.index'));

        foreach ($targets as $target) {
            $count = Actionlog::where('item_type', User::class)
                ->where('item_id', $target->id)
                ->where('action_type', 'update')
                ->count();

            $this->assertEquals(1, $count, "User {$target->id} should have exactly one update log entry");
        }
    }
}
