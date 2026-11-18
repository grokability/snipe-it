<?php

use App\Models\AssetModel;
use App\Models\User;

test('page renders', function () {
    $this->actingAs(User::factory()->superuser()->create())
        ->get(route('models.show', AssetModel::factory()->create()))
        ->assertOk();
});
