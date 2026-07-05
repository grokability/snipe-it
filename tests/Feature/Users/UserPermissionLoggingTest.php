<?php

namespace Tests\Feature\Users;

use App\Models\Actionlog;
use App\Models\User;
use Tests\TestCase;

class UserPermissionLoggingTest extends TestCase
{
    /**
     * Saving a user whose permissions are null (e.g. CSV-imported) while sending
     * "0" (inherit) for every permission should produce no log entry, because
     * null and "0" are functionally identical — both mean "inherit".
     */
    public function test_null_permissions_saved_as_zero_does_not_create_log_entry()
    {
        $user = User::factory()->create(['permissions' => null]);
        $actor = User::factory()->superuser()->create();

        $existingLogIds = Actionlog::where('item_type', User::class)
            ->where('item_id', $user->id)
            ->pluck('id');

        $this->actingAsForApi($actor)
            ->patchJson(route('api.users.update', $user), [
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'username' => $user->username,
                'permissions' => ['assets.view' => '0', 'assets.create' => '0'],
            ])
            ->assertOk();

        $newUpdateLogs = Actionlog::where('item_type', User::class)
            ->where('item_id', $user->id)
            ->where('action_type', 'update')
            ->whereNotIn('id', $existingLogIds)
            ->count();

        $this->assertEquals(0, $newUpdateLogs, 'Saving null permissions as "0" (inherit) should not create a log entry');
    }

    /**
     * Same as above but starting from an empty JSON object rather than null —
     * the other common state for imported users.
     */
    public function test_empty_permissions_saved_as_zero_does_not_create_log_entry()
    {
        $user = User::factory()->create(['permissions' => '{}']);
        $actor = User::factory()->superuser()->create();

        $existingLogIds = Actionlog::where('item_type', User::class)
            ->where('item_id', $user->id)
            ->pluck('id');

        $this->actingAsForApi($actor)
            ->patchJson(route('api.users.update', $user), [
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'username' => $user->username,
                'permissions' => ['assets.view' => '0'],
            ])
            ->assertOk();

        $newUpdateLogs = Actionlog::where('item_type', User::class)
            ->where('item_id', $user->id)
            ->where('action_type', 'update')
            ->whereNotIn('id', $existingLogIds)
            ->count();

        $this->assertEquals(0, $newUpdateLogs, 'Saving empty permissions as "0" (inherit) should not create a log entry');
    }

    /**
     * Changing a permission from null/inherit to an explicit grant SHOULD be logged.
     */
    public function test_changing_permission_from_null_to_one_creates_log_entry()
    {
        $user = User::factory()->create(['permissions' => null]);
        $actor = User::factory()->superuser()->create();

        $existingLogIds = Actionlog::where('item_type', User::class)
            ->where('item_id', $user->id)
            ->pluck('id');

        $this->actingAsForApi($actor)
            ->patchJson(route('api.users.update', $user), [
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'username' => $user->username,
                'permissions' => ['assets.view' => '1'],
            ])
            ->assertOk();

        $newUpdateLogs = Actionlog::where('item_type', User::class)
            ->where('item_id', $user->id)
            ->where('action_type', 'update')
            ->whereNotIn('id', $existingLogIds)
            ->get();

        $this->assertCount(1, $newUpdateLogs, 'Granting a permission should create a log entry');

        $meta = json_decode($newUpdateLogs->first()->log_meta, true);
        $this->assertArrayHasKey('permissions', $meta);
        $this->assertStringContainsString('assets.view', $meta['permissions']['new']);
    }

    /**
     * Changing a permission from "0" (explicit inherit) to "1" (grant) SHOULD be logged.
     */
    public function test_changing_permission_from_zero_to_one_creates_log_entry()
    {
        $user = User::factory()->create(['permissions' => json_encode(['assets.view' => '0'])]);
        $actor = User::factory()->superuser()->create();

        $existingLogIds = Actionlog::where('item_type', User::class)
            ->where('item_id', $user->id)
            ->pluck('id');

        $this->actingAsForApi($actor)
            ->patchJson(route('api.users.update', $user), [
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'username' => $user->username,
                'permissions' => ['assets.view' => '1'],
            ])
            ->assertOk();

        $newUpdateLogs = Actionlog::where('item_type', User::class)
            ->where('item_id', $user->id)
            ->where('action_type', 'update')
            ->whereNotIn('id', $existingLogIds)
            ->get();

        $this->assertCount(1, $newUpdateLogs, 'Changing from "0" to "1" should create a log entry');
    }

    /**
     * Revoking a permission (going back to inherit) from an explicit grant SHOULD be logged.
     */
    public function test_revoking_permission_from_one_to_zero_creates_log_entry()
    {
        $user = User::factory()->create(['permissions' => json_encode(['assets.view' => '1'])]);
        $actor = User::factory()->superuser()->create();

        $existingLogIds = Actionlog::where('item_type', User::class)
            ->where('item_id', $user->id)
            ->pluck('id');

        $this->actingAsForApi($actor)
            ->patchJson(route('api.users.update', $user), [
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'username' => $user->username,
                'permissions' => ['assets.view' => '0'],
            ])
            ->assertOk();

        $newUpdateLogs = Actionlog::where('item_type', User::class)
            ->where('item_id', $user->id)
            ->where('action_type', 'update')
            ->whereNotIn('id', $existingLogIds)
            ->count();

        $this->assertGreaterThan(0, $newUpdateLogs, 'Revoking a permission should create a log entry');
    }
}
