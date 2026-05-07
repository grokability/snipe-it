<?php

namespace Tests\Feature\Depreciations\Ui;

use App\Models\User;
use Tests\TestCase;

class DepreciationsIndexTest extends TestCase
{
    public function test_permission_required_to_view_depreciations_list()
    {
        $this->actingAs(User::factory()->create())
            ->get(route('depreciations.index'))
            ->assertForbidden();
    }

    public function test_user_can_list_depreciations()
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get(route('depreciations.index'))
            ->assertOk();
    }
}
