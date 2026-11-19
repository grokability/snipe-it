<?php

use App\Models\Asset;
use App\Models\Statuslabel;
use App\Models\User;

test('clean status label is bulk selectable', function () {
    $statusLabel = Statuslabel::factory()->create();

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(route('api.statuslabels.show', $statusLabel))
        ->assertOk()
        ->assertJsonPath('available_actions.bulk_selectable.delete', true);
});

test('status label with assets is not bulk selectable', function () {
    $statusLabel = Statuslabel::factory()->create();
    Asset::factory()->create(['status_id' => $statusLabel->id]);

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(route('api.statuslabels.show', $statusLabel))
        ->assertOk()
        ->assertJsonPath('available_actions.bulk_selectable.delete', false);
});
