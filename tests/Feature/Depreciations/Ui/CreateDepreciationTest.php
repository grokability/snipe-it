<?php

namespace Tests\Feature\Depreciations\Ui;

use App\Models\User;
use Tests\TestCase;

class CreateDepreciationTest extends TestCase
{
    public function testPageRenders()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('depreciations.create'))
            ->assertOk();
    }
}
