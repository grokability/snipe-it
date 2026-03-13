<?php

namespace Tests\Feature\StatusLabels\Ui;

use App\Models\User;
use Tests\TestCase;

class CreateStatusLabelTest extends TestCase
{
    public function testPageRenders()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('statuslabels.create'))
            ->assertOk();
    }
}
