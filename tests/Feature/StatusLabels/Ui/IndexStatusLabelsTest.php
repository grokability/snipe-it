<?php

namespace Tests\Feature\StatusLabels\Ui;

use App\Models\User;
use Tests\TestCase;

class IndexStatusLabelsTest extends TestCase
{
    public function testPageRenders()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('statuslabels.index'))
            ->assertOk();
    }
}
