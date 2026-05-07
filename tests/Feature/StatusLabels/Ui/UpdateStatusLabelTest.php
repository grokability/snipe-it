<?php

namespace Tests\Feature\StatusLabels\Ui;

use App\Models\Statuslabel;
use App\Models\User;
use Tests\TestCase;

class UpdateStatusLabelTest extends TestCase
{
    public function test_page_renders()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('statuslabels.edit', Statuslabel::factory()->create()->id))
            ->assertOk();
    }
}
