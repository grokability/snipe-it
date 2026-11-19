<?php

use App\Models\User;

test('permission required to view group list', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('groups.index'))
        ->assertForbidden();

    // $this->followRedirects($response)->assertSee('sad-panda.png');
});

test('user can list groups', function () {
    $this->actingAs(User::factory()->superuser()->create())
        ->get(route('groups.index'))
        ->assertOk();
});
