<?php

namespace Tests\Feature\Groups\Ui;

use App\Models\User;
use Tests\TestCase;

class IndexGroupTest extends TestCase
{
    public function test_permission_required_to_view_group_list()
    {
        $this->actingAs(User::factory()->create())
            ->get(route('groups.index'))
            ->assertForbidden();

        // $this->followRedirects($response)->assertSee('sad-panda.png');
    }

    public function test_user_can_list_groups()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('groups.index'))
            ->assertOk();
    }
}
