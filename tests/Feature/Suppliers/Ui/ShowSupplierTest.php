<?php

use App\Models\Supplier;
use App\Models\User;

test('page renders', function () {
    $this->actingAs(User::factory()->superuser()->create())
        ->get(route('suppliers.show', Supplier::factory()->create()->id))
        ->assertOk();
});
