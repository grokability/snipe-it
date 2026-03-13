<?php

namespace Tests\Feature\Consumables\Ui;

use App\Models\Consumable;
use App\Models\User;
use Tests\TestCase;

class ConsumableViewTest extends TestCase
{
    public function testPermissionRequiredToViewConsumable()
    {
        $consumable = Consumable::factory()->create();
        $this->actingAs(User::factory()->create())
            ->get(route('consumables.show', $consumable))
            ->assertForbidden();
    }

    public function testUserCanViewAConsumable()
    {
        $consumable = Consumable::factory()->create();
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('consumables.show', $consumable))
            ->assertOk();
    }
}
