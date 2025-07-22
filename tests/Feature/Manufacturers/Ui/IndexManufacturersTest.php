<?php

namespace Tests\Feature\Manufacturers\Ui;

use App\Models\Manufacturer;
use App\Models\User;
use Tests\TestCase;

class IndexManufacturersTest extends TestCase
{
    public function testPageRenders()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('manufacturers.index'))
            ->assertOk();
    }

    public function testCannotSeedIfManufacturersExist()
    {
        Manufacturer::factory()->create();

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('manufacturers.seed'))
            ->assertStatus(302)
            ->assertSessionHas('error')
            ->assertRedirect(route('manufacturers.index'));
    }
}
