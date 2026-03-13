<?php

namespace Tests\Feature\Groups\Ui;

use App\Models\Group;
use App\Models\User;
use Tests\TestCase;

class ShowGroupTest extends TestCase
{
    public function testPageRenders()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('groups.show', Group::factory()->create()->id))
            ->assertOk();
    }
}
