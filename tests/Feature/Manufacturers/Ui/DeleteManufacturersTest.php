<?php

use App\Models\Accessory;
use App\Models\Category;
use App\Models\Manufacturer;
use App\Models\User;

test('requires permission', function () {
    $this->actingAs(User::factory()->create())
        ->delete(route('categories.destroy', Category::factory()->create()))
        ->assertForbidden();
});

test('manufacturer cannot be deleted if models still associated', function () {
    $manufacturer = Manufacturer::factory()->create();
    Accessory::factory()->for($manufacturer)->create();

    $this->actingAs(User::factory()->deleteManufacturers()->create())
        ->delete(route('manufacturers.destroy', $manufacturer));

    $this->assertNotSoftDeleted($manufacturer);
});

test('manufacturer can be deleted', function () {
    $manufacturer = Manufacturer::factory()->create();

    $this->assertDatabaseHas('manufacturers', ['id' => $manufacturer->id]);

    $this->actingAs(User::factory()->deleteManufacturers()->create())
        ->delete(route('manufacturers.destroy', $manufacturer))
        ->assertRedirect(route('manufacturers.index'));

    $this->assertSoftDeleted($manufacturer);
});
