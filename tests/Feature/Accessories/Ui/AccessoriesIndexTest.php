<?php

namespace Tests\Feature\Accessories\Ui;

use App\Models\User;
use Tests\Concerns\TestsPermissionsRequirement;
use Tests\TestCase;

class AccessoriesIndexTest extends TestCase implements TestsPermissionsRequirement
{
    public function test_requires_permission()
    {
        $this->actingAs(User::factory()->create())
            ->get(route('accessories.index'))
            ->assertForbidden();
    }

    public function test_renders_accessories_index_page()
    {
        $this->actingAs(User::factory()->viewAccessories()->create())
            ->get(route('accessories.index'))
            ->assertOk()
            ->assertViewIs('accessories.index');
    }

    public function test_page_renders()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('accessories.index'))
            ->assertOk();
    }
}
