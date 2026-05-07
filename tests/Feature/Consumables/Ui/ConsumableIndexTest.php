<?php

namespace Tests\Feature\Consumables\Ui;

use App\Models\User;
use Tests\TestCase;

class ConsumableIndexTest extends TestCase
{
    public function test_permission_required_to_view_consumables_list()
    {
        $this->actingAs(User::factory()->create())
            ->get(route('consumables.index'))
            ->assertForbidden();
    }

    public function test_user_can_list_consumables()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('consumables.index'))
            ->assertOk();
    }
}
