<?php

use App\Models\Depreciation;
use App\Models\User;

test('requires permission', function () {
    $depreciation = Depreciation::factory()->create();

    $this->actingAsForApi(User::factory()->create())
        ->deleteJson(route('api.depreciations.destroy', $depreciation))
        ->assertForbidden();

    $this->assertDatabaseHas('depreciations', ['id' => $depreciation->id]);
});

test('cannot delete depreciation that has associated models', function () {
    $depreciation = Depreciation::factory()->hasModels()->create();

    $this->actingAsForApi(User::factory()->deleteDepreciations()->create())
        ->deleteJson(route('api.depreciations.destroy', $depreciation))
        ->assertStatusMessageIs('error');

    $this->assertDatabaseHas('depreciations', ['id' => $depreciation->id]);
});

test('can delete depreciation', function () {
    $depreciation = Depreciation::factory()->create();

    $this->actingAsForApi(User::factory()->deleteDepreciations()->create())
        ->deleteJson(route('api.depreciations.destroy', $depreciation))
        ->assertStatusMessageIs('success');

    $this->assertDatabaseMissing('depreciations', ['id' => $depreciation->id]);
});
