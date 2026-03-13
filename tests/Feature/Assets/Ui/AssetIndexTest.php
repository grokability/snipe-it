<?php

namespace Tests\Feature\Assets\Ui;

use App\Models\User;
use Tests\TestCase;

class AssetIndexTest extends TestCase
{
    public function testPageRenders()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('hardware.index'))
            ->assertOk();
    }
}
