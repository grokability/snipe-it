<?php

use App\Models\User;

test('viewing depreciation index requires permission', function () {
    $this->actingAsForApi(User::factory()->create())
        ->getJson(route('api.depreciations.index'))
        ->assertForbidden();
});
