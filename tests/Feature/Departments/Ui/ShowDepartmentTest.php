<?php

use App\Models\Department;
use App\Models\User;

test('page renders', function () {
    $this->actingAs(User::factory()->superuser()->create())
        ->get(route('departments.show', Department::factory()->create()))
        ->assertOk();
});
