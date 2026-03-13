<?php

namespace Tests\Feature\Depreciations\Ui;

use App\Models\Depreciation;
use App\Models\User;
use Tests\TestCase;

class UpdateDepreciationTest extends TestCase
{
    public function testPageRenders()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('depreciations.edit', Depreciation::factory()->create()->id))
            ->assertOk();
    }
}
