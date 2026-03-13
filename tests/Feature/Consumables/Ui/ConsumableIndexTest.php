<?php

namespace Tests\Feature\Consumables\Ui;

use App\Models\User;
use Tests\TestCase;

class ConsumableIndexTest extends TestCase
{
    public function testPermissionRequiredToViewConsumablesList()
    {
        $this->actingAs(User::factory()->create())
            ->get(route('consumables.index'))
            ->assertForbidden();
    }

    public function testUserCanListConsumables()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('consumables.index'))
            ->assertOk();
    }
}
