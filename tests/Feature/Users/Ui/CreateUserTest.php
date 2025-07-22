<?php

namespace Tests\Feature\Users\Ui;

use App\Models\User;
use Tests\TestCase;

class CreateUserTest extends TestCase
{

    public function testPermissionRequiredToCreateUser()
    {
        $this->actingAs(User::factory()->create())
            ->get(route('users.create'))
            ->assertForbidden();
    }

    public function testPageRenders()
    {
        $this->actingAs(User::factory()->createUsers()->create())
            ->get(route('users.create'))
            ->assertOk();

    }

    public function testCanCreateUser()
    {

        $response = $this->actingAs(User::factory()->createUsers()->viewUsers()->create())
            ->from(route('users.index'))
            ->post(route('users.store'), [
                'first_name' => 'Test First Name',
                'last_name' => 'Test Last Name',
                'username' => 'testuser',
                'password' => 'testpassword1235!!',
                //'notes' => 'Test Note',
            ])
            ->assertStatus(302)
            ->assertRedirect(route('users.index'));

        $this->followRedirects($response)->assertSee('Success');

    }
}
