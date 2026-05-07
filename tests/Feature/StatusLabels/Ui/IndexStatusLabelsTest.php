<?php

namespace Tests\Feature\StatusLabels\Ui;

use App\Models\User;
use Tests\TestCase;

class IndexStatusLabelsTest extends TestCase
{
    public function test_page_renders()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('statuslabels.index'))
            ->assertOk();
    }
}
