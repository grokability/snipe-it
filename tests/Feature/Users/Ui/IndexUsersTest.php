<?php

namespace Tests\Feature\Users\Ui;

use App\Models\User;
use Tests\TestCase;

class IndexUsersTest extends TestCase
{
    public function testPageRenders()
    {
        $this->actingAs(User::factory()->viewUsers()->create())
            ->get(route('users.index'))
            ->assertOk();
    }
}
