<?php

namespace Tests\Feature\Modals\Ui;

use App\Models\User;
use Tests\TestCase;

class ShowModalsTest extends TestCase
{
    public function testUserModalRenders()
    {
        $admin = User::factory()->createUsers()->create();
        $response = $this->actingAs($admin)
            ->get('modals/user')
            ->assertOk();

        $response->assertStatus(200);
        $response->assertDontSee($admin->first_name);
        $response->assertDontSee($admin->last_name);
        $response->assertDontSee($admin->email);
    }

    public function testDepartmentModalRenders()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get('modals/model')
            ->assertOk();
    }

    public function testStatusLabelModalRenders()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get('modals/statuslabel')
            ->assertOk();
    }

    public function testLocationModalRenders()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get('modals/location')
            ->assertOk();
    }

    public function testCategoryModalRenders()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get('modals/category')
            ->assertOk();
    }

    public function testManufacturerModalRenders()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get('modals/manufacturer')
            ->assertOk();
    }

    public function testSupplierModalRenders()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get('modals/supplier')
            ->assertOk();
    }


}
