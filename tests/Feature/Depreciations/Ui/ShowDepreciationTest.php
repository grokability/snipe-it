<?php

namespace Tests\Feature\Depreciations\Ui;

use App\Models\Depreciation;
use App\Models\User;
use Tests\TestCase;

class ShowDepreciationTest extends TestCase
{
    public function test_page_renders()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('depreciations.show', Depreciation::factory()->create()->id))
            ->assertOk();
    }
}
