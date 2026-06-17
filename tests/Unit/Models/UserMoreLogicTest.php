<?php

namespace Tests\Unit\Models;

use App\Models\User;
use Illuminate\Support\Collection;
use Tests\TestCase;

class UserMoreLogicTest extends TestCase
{
    public function test_effective_permissions_by_section_returns_array(): void
    {
        $user = User::factory()->create();

        $this->assertIsArray($user->getEffectivePermissionsBySection());
    }

    public function test_all_assigned_count_is_numeric(): void
    {
        $user = User::factory()->create();

        $this->assertIsNumeric($user->allAssignedCount());
    }

    public function test_get_assigned_items_with_pending_acceptance_returns_collection(): void
    {
        $user = User::factory()->create();

        $this->assertInstanceOf(Collection::class, $user->getAssignedItemsWithPendingAcceptance());
    }

    public function test_two_factor_active_and_enrolled_returns_bool(): void
    {
        $user = User::factory()->create();

        $this->assertIsBool((bool) $user->two_factor_active_and_enrolled());
    }

    public function test_manages_users_relation(): void
    {
        $manager = User::factory()->create();
        User::factory()->create(['manager_id' => $manager->id]);

        $this->assertInstanceOf(Collection::class, $manager->managesUsers()->get());
        $this->assertGreaterThanOrEqual(1, $manager->managesUsers()->count());
    }

    public function test_get_full_name_with_only_first_name(): void
    {
        $user = User::factory()->create(['first_name' => 'Cher', 'last_name' => '']);

        $this->assertEquals('Cher', trim($user->getFullNameAttribute()));
    }

    public function test_has_individual_permissions_false_when_empty(): void
    {
        $user = User::factory()->create(['permissions' => null]);

        $this->assertIsBool($user->hasIndividualPermissions());
    }
}
