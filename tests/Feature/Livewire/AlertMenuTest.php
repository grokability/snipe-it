<?php

namespace Tests\Feature\Livewire;

use App\Livewire\AlertMenu;
use App\Models\User;
use Livewire\Livewire;
use Tests\TestCase;

class AlertMenuTest extends TestCase
{
    public function test_the_component_renders(): void
    {
        Livewire::actingAs(User::factory()->superuser()->create())
            ->test(AlertMenu::class)
            ->assertStatus(200);
    }

    public function test_placeholder_reserves_bell_footprint(): void
    {
        $placeholder = (new AlertMenu)->placeholder();

        $this->assertStringContainsString('dropdown', $placeholder);
        $this->assertStringContainsString('fa-bell', $placeholder);
    }
}
