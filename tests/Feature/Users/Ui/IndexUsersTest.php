<?php

namespace Tests\Feature\Users\Ui;

use App\Models\User;
use Tests\TestCase;

class IndexUsersTest extends TestCase
{
    public function test_requires_permission()
    {
        $this->actingAs(User::factory()->create())
            ->get(route('users.index'))
            ->assertForbidden();
    }

    public function test_page_renders()
    {
        $this->actingAs(User::factory()->viewUsers()->create())
            ->get(route('users.index'))
            ->assertOk();
    }

    public function test_page_renders_with_array_query_inputs()
    {
        $this->actingAs(User::factory()->viewUsers()->create())
            ->get(route('users.index', [
                'manager_id' => [1],
                'company_id' => [1],
                'status' => ['deleted'],
            ]))
            ->assertOk();
    }
}
