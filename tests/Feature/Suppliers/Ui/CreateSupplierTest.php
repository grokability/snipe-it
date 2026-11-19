<?php

use App\Models\User;

test('page renders', function () {
    $this->actingAs(User::factory()->superuser()->create())
        ->get(route('suppliers.create'))
        ->assertOk();
});
