<?php

namespace Tests\Feature\Accessories\Ui;

use App\Models\User;
use Tests\Concerns\TestsPermissionsRequirement;
use Tests\TestCase;

class AccessoriesIndexTest extends TestCase implements TestsPermissionsRequirement
{
    public function testRequiresPermission()
    {
        $this->actingAs(User::factory()->create())
            ->get(route('accessories.index'))
            ->assertForbidden();
    }


    public function testRendersAccessoriesIndexPage()
    {
        $this->actingAs(User::factory()->viewAccessories()->create())
            ->get(route('accessories.index'))
            ->assertOk()
            ->assertViewIs('accessories.index');
    }
  
    public function testPageRenders()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('accessories.index'))
            ->assertOk();
    }
}
