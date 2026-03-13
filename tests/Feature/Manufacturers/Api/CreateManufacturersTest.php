<?php

namespace Tests\Feature\Manufacturers\Api;

use App\Models\Manufacturer;
use App\Models\User;
use Tests\TestCase;

class CreateManufacturersTest extends TestCase
{


    public function testRequiresPermissionToCreateDepartment()
    {
        $this->actingAsForApi(User::factory()->create())
            ->postJson(route('api.departments.store'))
            ->assertForbidden();
    }

    public function testCanCreateManufacturer()
    {
        $response = $this->actingAsForApi(User::factory()->superuser()->create())
            ->postJson(route('api.manufacturers.store'), [
                'name' => 'Test Manufacturer',
                'notes' => 'Test Note',
            ])
            ->assertOk()
            ->assertStatusMessageIs('success')
            ->assertStatus(200)
            ->json();

        $this->assertTrue(Manufacturer::where('name', 'Test Manufacturer')->where('notes', 'Test Note')->exists());

        $manufacturer = Manufacturer::find($response['payload']['id']);
        $this->assertEquals('Test Manufacturer', $manufacturer->name);
        $this->assertEquals('Test Note', $manufacturer->notes);
    }

}
