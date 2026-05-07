<?php

namespace Tests\Feature\PredefinedKits\Ui;

use App\Models\User;
use Tests\TestCase;

class IndexPredefinedKitsTest extends TestCase
{
    public function test_page_renders()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('kits.index'))
            ->assertOk();
    }
}
