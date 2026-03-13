<?php

namespace Tests\Feature\PredefinedKits\Ui;

use App\Models\PredefinedKit;
use App\Models\User;
use Tests\TestCase;

class UpdatePredefinedKitTest extends TestCase
{
    public function testPageRenders()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('kits.edit', PredefinedKit::factory()->create()->id))
            ->assertOk();
    }
}
