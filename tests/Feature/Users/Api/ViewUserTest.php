<?php

namespace Tests\Feature\Users\Api;

use App\Models\User;
use Tests\TestCase;

class ViewUserTest extends TestCase
{
    public function test_can_return_user()
    {
        $user = User::factory()->create();

        $this->actingAsForApi(User::factory()->viewUsers()->create())
            ->getJson(route('api.users.show', $user))
            ->assertOk();
    }
}
