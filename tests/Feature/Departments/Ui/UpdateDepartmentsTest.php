<?php

use App\Models\Department;
use App\Models\User;

test('permission required to store department', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('departments.store'), [
            'name' => 'Test Department',
        ])
        ->assertStatus(403)
        ->assertForbidden();
});

test('page renders', function () {
    $this->actingAs(User::factory()->superuser()->create())
        ->get(route('departments.edit', Department::factory()->create()))
        ->assertOk();
});

test('user can edit departments', function () {
    $department = Department::factory()->create(['name' => 'Test Department']);
    expect(Department::where('name', 'Test Department')->exists())->toBeTrue();

    $response = $this->actingAs(User::factory()->superuser()->create())
        ->put(route('departments.update', $department), [
            'name' => 'Test Department Edited',
            'notes' => 'Test Note Edited',
        ])
        ->assertStatus(302)
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('departments.index'));

    $this->followRedirects($response)->assertSee('Success');
    expect(Department::where('name', 'Test Department Edited')->where('notes', 'Test Note Edited')->exists())->toBeTrue();
});
