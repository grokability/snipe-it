<?php

namespace Tests\Feature\Consumables\Ui;

use App\Models\Consumable;
use App\Models\User;
use Tests\TestCase;

class ConsumableViewTest extends TestCase
{
    public function test_permission_required_to_view_consumable()
    {
        $consumable = Consumable::factory()->create();
        $this->actingAs(User::factory()->create())
            ->get(route('consumables.show', $consumable))
            ->assertForbidden();
    }

    public function test_user_can_view_a_consumable()
    {
        $consumable = Consumable::factory()->create();
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('consumables.show', $consumable))
            ->assertOk();
    }
}
