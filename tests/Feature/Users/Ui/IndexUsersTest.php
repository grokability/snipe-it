<?php

namespace Tests\Feature\Users\Ui;

use App\Models\User;
use Tests\TestCase;

class IndexUsersTest extends TestCase
{
    public function testRequiresPermission()
    {
        $this->actingAs(User::factory()->create())
            ->get(route('users.index'))
            ->assertForbidden();
    }

    public function testPageRenders()
    {
        $this->actingAs(User::factory()->viewUsers()->create())
            ->get(route('users.index'))
            ->assertOk();
    }
}
