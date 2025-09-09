<?php

namespace Tests\Feature\Maintenances\Ui;

use App\Models\User;
use Tests\TestCase;

class MaintenanceIndexTest extends TestCase
{
    public function testPageRenders()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('maintenances.index'))
            ->assertOk();
    }
}
