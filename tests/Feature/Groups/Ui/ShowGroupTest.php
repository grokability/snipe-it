<?php

use App\Models\Group;
use App\Models\User;

test('page renders', function () {
    $this->actingAs(User::factory()->superuser()->create())
        ->get(route('groups.show', Group::factory()->create()->id))
        ->assertOk();
});
