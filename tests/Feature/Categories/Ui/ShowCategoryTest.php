<?php

use App\Models\Category;
use App\Models\User;

test('page renders', function () {
    $this->actingAs(User::factory()->superuser()->create())
        ->get(route('categories.show', Category::factory()->create()))
        ->assertOk();
});
