<?php

namespace Tests\Feature\Manufacturers\Ui;

use App\Models\User;
use App\Models\Manufacturer;
use Tests\TestCase;

class CreateManufacturerTest extends TestCase
{
    public function testPageRenders()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('manufacturers.create'))
            ->assertOk();
    }

    public function testUserCanCreateManufacturer()
    {
        $this->assertFalse(Manufacturer::where('name', 'Test Manufacturer')->exists());

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('manufacturers.store'), [
                'name' => 'Test Manufacturer',
                'notes' => 'Test Note',
            ])
            ->assertRedirect(route('manufacturers.index'));

        $this->assertTrue(Manufacturer::where('name', 'Test Manufacturer')->where('notes', 'Test Note')->exists());
    }

}
