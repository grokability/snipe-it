<?php

namespace Tests\Feature\Manufacturers\Api;

use App\Models\Manufacturer;
use App\Models\Category;
use App\Models\User;
use Tests\TestCase;

class UpdateManufacturersTest extends TestCase
{
    public function testPermissionRequiredToStoreManufacturer()
    {
        $this->actingAs(User::factory()->create())
            ->post(route('manufacturers.store'), [
                'name' => 'Test Manufacturer',
            ])
            ->assertStatus(403)
            ->assertForbidden();
    }

    public function testPageRenders()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('manufacturers.edit', Manufacturer::factory()->create()->id))
            ->assertOk();
    }

    public function testUserCanEditManufacturers()
    {
        $department = Manufacturer::factory()->create(['name' => 'Test Manufacturer']);
        $this->assertTrue(Manufacturer::where('name', 'Test Manufacturer')->exists());

        $response = $this->actingAs(User::factory()->superuser()->create())
            ->put(route('manufacturers.update', ['manufacturer' => $department]), [
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
