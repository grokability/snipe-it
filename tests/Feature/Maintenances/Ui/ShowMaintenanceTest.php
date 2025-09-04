<?php

namespace Tests\Feature\Maintenances\Ui;

use App\Models\Maintenance;
use App\Models\User;
use Tests\TestCase;

class ShowMaintenanceTest extends TestCase
{
    public function testPageRenders()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('maintenances.show', Maintenance::factory()->create()->id))
            ->assertOk();
    }
}
