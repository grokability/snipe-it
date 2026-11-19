<?php

use App\Models\Department;
use App\Models\User;

test('requires permission', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('departments.bulk.delete'), [
            'ids' => [1, 2, 3],
        ])
        ->assertForbidden();
});

test('department with users is not bulk deleted', function () {
    $department = Department::factory()->create();
    User::factory()->create(['department_id' => $department->id]);

    $this->actingAs(User::factory()->deleteDepartments()->create())
        ->post(route('departments.bulk.delete'), [
            'ids' => [$department->id],
        ]);

    $this->assertModelExists($department);
    $this->assertNotSoftDeleted($department);
});

test('deletable departments are bulk deleted', function () {
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
});

test('partial success deletes the clean ones and reports the rest', function () {
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
});

test('nonexistent ids are reported and do not break the batch', function () {
    $deletable = Department::factory()->create();

    $this->actingAs(User::factory()->deleteDepartments()->create())
        ->post(route('departments.bulk.delete'), [
            'ids' => [$deletable->id, 999999],
        ])
        ->assertRedirect(route('departments.index'))
        ->assertSessionHas('multi_error_messages');

    $this->assertSoftDeleted($deletable);
});

test('bulk success message pluralizes by count', function () {
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
});
