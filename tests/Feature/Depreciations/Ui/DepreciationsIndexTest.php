<?php

namespace Tests\Feature\Depreciations\Ui;

use App\Models\User;
use Tests\TestCase;

class DepreciationsIndexTest extends TestCase
{
    public function testPermissionRequiredToViewDepreciationsList()
    {
        $this->actingAs(User::factory()->create())
            ->get(route('depreciations.index'))
            ->assertForbidden();
    }

    public function testUserCanListDepreciations()
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get(route('depreciations.index'))
            ->assertOk();
    }
}
