<?php

namespace Tests\Feature\Accessories\Ui;

use App\Models\Accessory;
use App\Models\User;
use Tests\TestCase;

class EditAccessoryTest extends TestCase
{
    public function testPageRenders()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('accessories.edit', Accessory::factory()->create()->id))
            ->assertOk();
    }
}
