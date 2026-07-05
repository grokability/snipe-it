<?php

namespace Tests\Feature\Departments\Ui;

use App\Models\Department;
use App\Models\User;
use Tests\TestCase;

class BulkDeleteDepartmentsTest extends TestCase
{
    public function test_requires_permission()
    {
        $this->actingAs(User::factory()->create())
            ->post(route('departments.bulk.delete'), [
                'ids' => [1, 2, 3],
            ])
            ->assertForbidden();
    }

    public function test_department_with_users_is_not_bulk_deleted()
    {
        $department = Department::factory()->create();
        User::factory()->create(['department_id' => $department->id]);

        $this->actingAs(User::factory()->deleteDepartments()->create())
            ->post(route('departments.bulk.delete'), [
                'ids' => [$department->id],
            ]);

        $this->assertModelExists($department);
        $this->assertNotSoftDeleted($department);
    }

    public function test_deletable_departments_are_bulk_deleted()
    {
        $department1 = Department::factory()->create();
        $department2 = Department::factory()->create();
        $department3 = Department::factory()->create();

        $this->actingAs(User::factory()->deleteDepartments()->create())
            ->post(route('departments.bulk.delete'), [
                'ids' => [$department1->id, $department2->id, $department3->id],
            ])
            ->assertRedirect(route('departments.index'));

        $this->assertSoftDeleted($department1);
        $this->assertSoftDeleted($department2);
        $this->assertSoftDeleted($department3);
    }

    public function test_partial_success_deletes_the_clean_ones_and_reports_the_rest()
    {
        // One clean, one blocked by an assigned user. The clean one should soft-delete
        // and the blocked one should surface in the multi-error flash bag.
        $deletable = Department::factory()->create();
        $blocked = Department::factory()->create();
        User::factory()->create(['department_id' => $blocked->id]);

        $this->actingAs(User::factory()->deleteDepartments()->create())
            ->post(route('departments.bulk.delete'), [
                'ids' => [$deletable->id, $blocked->id],
            ])
            ->assertRedirect(route('departments.index'))
            ->assertSessionHas('success')
            ->assertSessionHas('multi_error_messages');

        $this->assertSoftDeleted($deletable);
        $this->assertNotSoftDeleted($blocked);
    }

    public function test_nonexistent_ids_are_reported_and_do_not_break_the_batch()
    {
        $deletable = Department::factory()->create();

        $this->actingAs(User::factory()->deleteDepartments()->create())
            ->post(route('departments.bulk.delete'), [
                'ids' => [$deletable->id, 999999],
            ])
            ->assertRedirect(route('departments.index'))
            ->assertSessionHas('multi_error_messages');

        $this->assertSoftDeleted($deletable);
    }

    public function test_bulk_success_message_pluralizes_by_count()
    {
        // Single-item batch → singular success message.
        $solo = Department::factory()->create();

        $this->actingAs(User::factory()->deleteDepartments()->create())
            ->post(route('departments.bulk.delete'), [
                'ids' => [$solo->id],
            ])
            ->assertSessionHas('success', trans_choice('admin/departments/message.delete.bulk_success', 1, ['count' => 1]));

        // Multi-item batch → plural success message with the count interpolated.
        $a = Department::factory()->create();
        $b = Department::factory()->create();
        $c = Department::factory()->create();

        $this->actingAs(User::factory()->deleteDepartments()->create())
            ->post(route('departments.bulk.delete'), [
                'ids' => [$a->id, $b->id, $c->id],
            ])
            ->assertSessionHas('success', trans_choice('admin/departments/message.delete.bulk_success', 3, ['count' => 3]));
    }
}
