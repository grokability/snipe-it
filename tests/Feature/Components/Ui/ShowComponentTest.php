<?php

namespace Tests\Feature\Components\Ui;

use App\Models\Component;
use App\Models\User;
use Tests\TestCase;

class ShowComponentTest extends TestCase
{
    public function testPageRenders()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('components.show', Component::factory()->create()))
            ->assertOk();
    }
}
