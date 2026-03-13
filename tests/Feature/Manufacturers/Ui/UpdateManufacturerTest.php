<?php

namespace Tests\Feature\Manufacturers\Ui;

use App\Models\Manufacturer;
use App\Models\User;
use Tests\TestCase;

class UpdateManufacturerTest extends TestCase
{
    public function testPageRenders()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('manufacturers.edit', Manufacturer::factory()->create()))
            ->assertOk();
    }

    public function testUserCanEditManufacturers()
    {
        $manufacturer = Manufacturer::factory()->create(['name' => 'Test Manufacturer']);
        $this->assertTrue(Manufacturer::where('name', 'Test Manufacturer')->exists());

        $response = $this->actingAs(User::factory()->superuser()->create())
            ->put(route('manufacturers.update', $manufacturer), [
                'name' => 'Test Manufacturer Edited',
                'notes' => 'Test Note Edited',
            ])
            ->assertStatus(302)
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('manufacturers.index'));

        $this->followRedirects($response)->assertSee('Success');
        $this->assertTrue(Manufacturer::where('name', 'Test Manufacturer Edited')->where('notes', 'Test Note Edited')->exists());
    }

}
