<?php

namespace Tests\Feature\Components\Ui;

use App\Models\Component;
use App\Models\User;
use Tests\TestCase;

class EditComponentTest extends TestCase
{
    public function test_page_renders()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('components.edit', Component::factory()->create()))
            ->assertOk();
    }
}
