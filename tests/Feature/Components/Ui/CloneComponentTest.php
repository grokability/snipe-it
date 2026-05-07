<?php

namespace Tests\Feature\Components\Ui;

use App\Models\Component;
use App\Models\User;
use Tests\TestCase;

class CloneComponentTest extends TestCase
{
    public function test_permission_required_to_create_component()
    {
        $component = Component::factory()->create();
        $this->actingAs(User::factory()->create())
            ->get(route('components.clone.create', $component))
            ->assertForbidden();
    }

    public function test_page_can_be_accessed(): void
    {
        $component = Component::factory()->create();
        $response = $this->actingAs(User::factory()->createComponents()->create())
            ->get(route('components.clone.create', $component));
        $response->assertStatus(200);
    }

    public function test_component_can_be_cloned()
    {
        $component_to_clone = Component::factory()->create(['name' => 'Component to clone']);
        $this->actingAs(User::factory()->createComponents()->create())
            ->get(route('components.clone.create', $component_to_clone))
            ->assertOk()
            ->assertSee([
                'Component to clone',
            ], false);
    }
}
