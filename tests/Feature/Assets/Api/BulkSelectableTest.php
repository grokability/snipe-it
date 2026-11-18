<?php

use App\Models\Asset;
use App\Models\User;

test('clean unassigned asset supports edit maintenance checkout audit delete labels', function () {
    $asset = Asset::factory()->create();

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(route('api.assets.show', $asset))
        ->assertOk()
        ->assertJsonPath('available_actions.bulk_selectable.edit', true)
        ->assertJsonPath('available_actions.bulk_selectable.maintenance', true)
        ->assertJsonPath('available_actions.bulk_selectable.checkout', true)
        ->assertJsonPath('available_actions.bulk_selectable.checkin', false)
        ->assertJsonPath('available_actions.bulk_selectable.audit', true)
        ->assertJsonPath('available_actions.bulk_selectable.delete', true)
        ->assertJsonPath('available_actions.bulk_selectable.labels', true)
        ->assertJsonPath('available_actions.bulk_selectable.restore', false);
});

test('assigned asset hides checkout and delete and shows checkin', function () {
    $assignee = User::factory()->create();
    $asset = Asset::factory()->assignedToUser($assignee)->create();

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(route('api.assets.show', $asset))
        ->assertOk()
        ->assertJsonPath('available_actions.bulk_selectable.edit', true)
        ->assertJsonPath('available_actions.bulk_selectable.checkout', false)
        ->assertJsonPath('available_actions.bulk_selectable.checkin', true)
        ->assertJsonPath('available_actions.bulk_selectable.delete', false)
        ->assertJsonPath('available_actions.bulk_selectable.labels', true)
        ->assertJsonPath('available_actions.bulk_selectable.restore', false);
});

test('soft deleted asset surfaces only restore', function () {
    $asset = Asset::factory()->create();
    $asset->delete();

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(route('api.assets.show', $asset))
        ->assertOk()
        ->assertJsonPath('available_actions.bulk_selectable.edit', false)
        ->assertJsonPath('available_actions.bulk_selectable.maintenance', false)
        ->assertJsonPath('available_actions.bulk_selectable.checkout', false)
        ->assertJsonPath('available_actions.bulk_selectable.checkin', false)
        ->assertJsonPath('available_actions.bulk_selectable.audit', false)
        ->assertJsonPath('available_actions.bulk_selectable.delete', false)
        ->assertJsonPath('available_actions.bulk_selectable.labels', false)
        ->assertJsonPath('available_actions.bulk_selectable.restore', true);
});

test('user without edit permission gets edit and maintenance false', function () {
    $asset = Asset::factory()->create();

    $this->actingAsForApi(User::factory()->viewAssets()->create())
        ->getJson(route('api.assets.show', $asset))
        ->assertOk()
        ->assertJsonPath('available_actions.bulk_selectable.edit', false)
        ->assertJsonPath('available_actions.bulk_selectable.maintenance', false);
});

test('user without checkout permission gets checkout false', function () {
    $asset = Asset::factory()->create();

    $this->actingAsForApi(User::factory()->viewAssets()->create())
        ->getJson(route('api.assets.show', $asset))
        ->assertOk()
        ->assertJsonPath('available_actions.bulk_selectable.checkout', false);
});

test('user without checkin permission gets checkin false', function () {
    $assignee = User::factory()->create();
    $asset = Asset::factory()->assignedToUser($assignee)->create();

    $this->actingAsForApi(User::factory()->viewAssets()->create())
        ->getJson(route('api.assets.show', $asset))
        ->assertOk()
        ->assertJsonPath('available_actions.bulk_selectable.checkin', false);
});

test('user without audit permission gets audit false', function () {
    $asset = Asset::factory()->create();

    $this->actingAsForApi(User::factory()->viewAssets()->create())
        ->getJson(route('api.assets.show', $asset))
        ->assertOk()
        ->assertJsonPath('available_actions.bulk_selectable.audit', false);
});

test('user without delete permission gets delete false', function () {
    $asset = Asset::factory()->create();

    $this->actingAsForApi(User::factory()->viewAssets()->create())
        ->getJson(route('api.assets.show', $asset))
        ->assertOk()
        ->assertJsonPath('available_actions.bulk_selectable.delete', false);
});

test('user without create permission gets restore false', function () {
    $asset = Asset::factory()->create();
    $asset->delete();

    $this->actingAsForApi(User::factory()->viewAssets()->create())
        ->getJson(route('api.assets.show', $asset))
        ->assertOk()
        ->assertJsonPath('available_actions.bulk_selectable.restore', false);
});
