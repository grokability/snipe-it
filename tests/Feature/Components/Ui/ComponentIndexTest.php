<?php

namespace Tests\Feature\Components\Ui;

use App\Models\User;
use Tests\TestCase;

class ComponentIndexTest extends TestCase
{
    public function test_permission_required_to_view_components_list()
    {
        $this->actingAs(User::factory()->create())
            ->get(route('components.index'))
            ->assertForbidden();
    }

    public function test_user_can_list_components()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('components.index'))
            ->assertOk();
    }
}
