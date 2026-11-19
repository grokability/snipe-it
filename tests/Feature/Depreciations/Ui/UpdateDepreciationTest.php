<?php

use App\Models\Depreciation;
use App\Models\User;

test('page renders', function () {
    $this->actingAs(User::factory()->superuser()->create())
        ->get(route('depreciations.edit', Depreciation::factory()->create()->id))
        ->assertOk();
});
