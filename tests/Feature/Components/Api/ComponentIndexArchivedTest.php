<?php

use App\Models\Component;
use App\Models\User;

test('default list hides archived components when setting off', function () {
    $this->settings->set(['show_archived_in_list' => 0]);

    $live = Component::factory()->create();
    $archived = Component::factory()->archived()->create();

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(route('api.components.index'))
        ->assertOk()
        ->assertResponseContainsInRows($live)
        ->assertResponseDoesNotContainInRows($archived);
});

test('default list shows archived when setting on', function () {
    $this->settings->set(['show_archived_in_list' => 1]);

    $live = Component::factory()->create();
    $archived = Component::factory()->archived()->create();

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(route('api.components.index'))
        ->assertOk()
        ->assertResponseContainsInRows($live)
        ->assertResponseContainsInRows($archived);
});

test('status archived returns only archived components', function () {
    $live = Component::factory()->create();
    $archived = Component::factory()->archived()->create();

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(route('api.components.index', ['status' => 'Archived']))
        ->assertOk()
        ->assertResponseContainsInRows($archived)
        ->assertResponseDoesNotContainInRows($live);
});

test('status deleted returns only soft deleted components', function () {
    $live = Component::factory()->create();
    $deleted = Component::factory()->create();
    $deleted->delete();

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(route('api.components.index', ['status' => 'Deleted']))
        ->assertOk()
        ->assertResponseContainsInRows($deleted)
        ->assertResponseDoesNotContainInRows($live);
});

test('transformer exposes archived flag', function () {
    $archived = Component::factory()->archived()->create();

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(route('api.components.index', ['status' => 'Archived']))
        ->assertOk()
        ->assertJsonFragment(['id' => $archived->id, 'archived' => true]);
});
