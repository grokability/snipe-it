<?php

namespace Tests\Feature\Users\Ui;

use App\Models\User;
use Tests\TestCase;

class CloneUserTest extends TestCase
{
    public function test_page_renders()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('users.clone.show', User::factory()->create()))
            ->assertOk();
    }
}
