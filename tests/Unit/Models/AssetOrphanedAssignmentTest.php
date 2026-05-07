<?php

namespace Tests\Unit\Models;

use App\Models\Asset;
use App\Models\Location;
use App\Models\User;
use Tests\TestCase;

class AssetOrphanedAssignmentTest extends TestCase
{
    public function test_has_orphaned_assignment_returns_true_when_assigned_to_set_but_type_missing()
    {
        $asset = Asset::factory()->create();
        $asset->assigned_to = 999;
        $asset->assigned_type = null;
        $asset->forceSave();

        $asset->refresh();

        $this->assertTrue($asset->hasOrphanedAssignment());
    }

    public function test_has_orphaned_assignment_returns_true_when_assigned_target_hard_deleted()
    {
        $user = User::factory()->create();
        $asset = Asset::factory()->create();
        $asset->assigned_to = $user->id;
        $asset->assigned_type = User::class;
        $asset->save();

        // Hard delete the user
        $user->forceDelete();

        $asset->refresh();
        $this->assertTrue($asset->hasOrphanedAssignment());
    }

    public function test_has_orphaned_assignment_returns_false_when_properly_assigned()
    {
        $user = User::factory()->create();
        $asset = Asset::factory()->create();
        $asset->assigned_to = $user->id;
        $asset->assigned_type = User::class;
        $asset->save();

        $this->assertFalse($asset->hasOrphanedAssignment());
    }

    public function test_has_orphaned_assignment_returns_false_when_not_assigned()
    {
        $asset = Asset::factory()->create();
        $asset->assigned_to = null;
        $asset->assigned_type = null;
        $asset->save();

        $this->assertFalse($asset->hasOrphanedAssignment());
    }

    public function test_has_orphaned_assignment_returns_false_when_assigned_to_location()
    {
        $location = Location::factory()->create();
        $asset = Asset::factory()->create();
        $asset->assigned_to = $location->id;
        $asset->assigned_type = Location::class;
        $asset->save();

        $this->assertFalse($asset->hasOrphanedAssignment());
    }

    public function test_has_orphaned_assignment_returns_true_when_assigned_to_hard_deleted_location()
    {
        $location = Location::factory()->create();
        $asset = Asset::factory()->create();
        $asset->assigned_to = $location->id;
        $asset->assigned_type = Location::class;
        $asset->save();

        // Hard delete the location
        $location->forceDelete();

        $asset->refresh();
        $this->assertTrue($asset->hasOrphanedAssignment());
    }

    public function test_has_orphaned_assignment_returns_false_when_assigned_to_soft_deleted_user()
    {
        $user = User::factory()->create();
        $asset = Asset::factory()->create();
        $asset->assigned_to = $user->id;
        $asset->assigned_type = User::class;
        $asset->save();

        // Soft delete the user (not hard delete)
        $user->delete();

        $asset->refresh();
        // Should still be false because withTrashed() loads soft-deleted records
        $this->assertFalse($asset->hasOrphanedAssignment());
    }
}
