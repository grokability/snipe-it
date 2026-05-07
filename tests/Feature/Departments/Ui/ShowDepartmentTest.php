<?php

namespace Tests\Feature\Departments\Ui;

use App\Models\Department;
use App\Models\User;
use Tests\TestCase;

class ShowDepartmentTest extends TestCase
{
    public function test_page_renders()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('departments.show', Department::factory()->create()))
            ->assertOk();
    }
}
