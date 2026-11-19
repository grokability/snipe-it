<?php

use App\Models\Asset;
use App\Models\License;
use App\Models\User;

test('upload file modal renders on asset view', function () {
    $asset = Asset::factory()->create();

    $this->actingAs(User::factory()->superuser()->create())
        ->get(route('hardware.show', $asset))
        ->assertOk()
        ->assertSee('id="uploadFileModal"', false)
        ->assertSee(route('ui.files.store', ['object_type' => 'assets', 'id' => $asset->id]));
});

test('add note modal renders on asset view', function () {
    $asset = Asset::factory()->create();

    $this->actingAs(User::factory()->superuser()->create())
        ->get(route('hardware.show', $asset))
        ->assertOk()
        ->assertSee('id="createNoteModal"', false)
        ->assertSee(route('notes.store'));
});

test('confirm action modal renders on license view', function () {
    $license = License::factory()->create();

    $this->actingAs(User::factory()->superuser()->create())
        ->get(route('licenses.show', $license))
        ->assertOk()
        ->assertSee('id="checkinFromAllModal"', false)
        ->assertSee('id="checkoutFromAllModal"', false)
        ->assertSee(route('licenses.bulkcheckin', $license->id))
        ->assertSee(route('licenses.bulkcheckout', $license->id));
});
