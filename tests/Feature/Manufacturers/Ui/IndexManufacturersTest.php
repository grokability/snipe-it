<?php

namespace Tests\Feature\Manufacturers\Ui;

use App\Models\Manufacturer;
use App\Models\User;
use Tests\TestCase;

class IndexManufacturersTest extends TestCase
{
    public function test_page_renders()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('manufacturers.index'))
            ->assertOk();
    }

    public function test_cannot_seed_if_manufacturers_exist()
    {
        Manufacturer::factory()->create();

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('manufacturers.seed'))
            ->assertStatus(302)
            ->assertSessionHas('error')
            ->assertRedirect(route('manufacturers.index'));
    }
}
