<?php

namespace Tests\Feature\Departments\Ui;

use App\Models\User;
use Tests\TestCase;

class IndexDepartmentsTest extends TestCase
{
    public function test_permission_required_to_view_departments_list()
    {
        $this->actingAs(User::factory()->create())
            ->get(route('departments.index'))
            ->assertForbidden();
    }

    public function test_page_renders()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('departments.index'))
            ->assertOk();
    }

    public function test_user_can_list_departments()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('departments.index'))
            ->assertOk();
    }
}
