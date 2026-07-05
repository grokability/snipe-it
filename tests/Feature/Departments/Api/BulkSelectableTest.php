<?php

namespace Tests\Feature\Departments\Api;

use App\Models\Department;
use App\Models\User;
use Tests\TestCase;

/**
 * Verifies the JS-visible flag that drives the bulk-delete checkbox on the
 * departments index. The bootstrap-table `checkboxEnabledFormatter` reads
 * `available_actions.bulk_selectable.delete` and disables the row's checkbox
 * when every entry there is false. A department with any assigned user must
 * therefore report `bulk_selectable.delete === false`; a clean department
 * must report `true`.
 */
class BulkSelectableTest extends TestCase
{
    public function test_clean_department_is_bulk_selectable()
    {
        $department = Department::factory()->create();

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(route('api.departments.show', $department))
            ->assertOk()
            ->assertJsonPath('available_actions.bulk_selectable.delete', true);
    }

    public function test_department_with_users_is_not_bulk_selectable()
    {
        $department = Department::factory()->create();
        User::factory()->create(['department_id' => $department->id]);

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(route('api.departments.show', $department))
            ->assertOk()
            ->assertJsonPath('available_actions.bulk_selectable.delete', false);
    }
}
