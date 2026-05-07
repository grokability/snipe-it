<?php

namespace Tests\Feature\Manufacturers\Ui;

use App\Models\Manufacturer;
use App\Models\User;
use Tests\TestCase;

class CreateManufacturerTest extends TestCase
{
    public function test_page_renders()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('manufacturers.create'))
            ->assertOk();
    }

    public function test_user_can_create_manufacturer()
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
