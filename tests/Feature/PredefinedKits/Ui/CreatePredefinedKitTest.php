<?php

namespace Tests\Feature\PredefinedKits\Ui;

use App\Models\User;
use Tests\TestCase;

class CreatePredefinedKitTest extends TestCase
{
    public function testPageRenders()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('kits.create'))
            ->assertOk();
    }
}
