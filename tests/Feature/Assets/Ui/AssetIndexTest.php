<?php

use App\Models\User;

test('page renders', function () {
    $this->actingAs(User::factory()->superuser()->create())
        ->get(route('hardware.index'))
        ->assertOk();
});

test('page renders with array query inputs', function () {
    $this->actingAs(User::factory()->superuser()->create())
        ->get(route('hardware.index', [
            'status_type' => ['Deleted'],
            'order_number' => [123],
            'company_id' => [1],
            'status_id' => [1],
        ]))
        ->assertOk();
});

test('page renders when status type is an associative array probe', function () {
    // Regression pin for an array-shaped query probe like
    // ?status_type[$ptt]=RTD. request()->status_type returns an
    // associative array, the old breadcrumb branch compared it
    // to '' (truthy for arrays), then passed it to e(), which
    // crashed htmlspecialchars with a TypeError and surfaced as
    // a ViewException on the whole /hardware page. The guard
    // now requires is_string before the e() call.
    $this->actingAs(User::factory()->superuser()->create())
        ->get('/hardware?status_type[$ptt]=RTD')
        ->assertOk();
});
