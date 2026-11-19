<?php

use App\Models\Statuslabel;
use App\Models\User;

test('page renders', function () {
    $this->actingAs(User::factory()->superuser()->create())
        ->get(route('statuslabels.show', Statuslabel::factory()->create()))
        ->assertOk();
});
