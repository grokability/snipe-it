<?php

use App\Models\Statuslabel;
use App\Models\User;

test('requires permission', function () {
    $statusLabel = Statuslabel::factory()->create();

    $this->actingAsForApi(User::factory()->create())
        ->deleteJson(route('api.statuslabels.destroy', $statusLabel))
        ->assertForbidden();

    $this->assertNotSoftDeleted($statusLabel);
});

test('cannot delete status label while still associated to assets', function () {
    $statusLabel = Statuslabel::factory()->hasAssets()->create();

    expect($statusLabel->assets->count())->toBeGreaterThan(0, 'Precondition failed: StatusLabel has no assets');

    $this->actingAsForApi(User::factory()->deleteStatusLabels()->create())
        ->deleteJson(route('api.statuslabels.destroy', $statusLabel))
        ->assertStatusMessageIs('error');

    $this->assertNotSoftDeleted($statusLabel);
});

test('can delete status label', function () {
    $statusLabel = Statuslabel::factory()->create();

    $this->actingAsForApi(User::factory()->deleteStatusLabels()->create())
        ->deleteJson(route('api.statuslabels.destroy', $statusLabel))
        ->assertOk()
        ->assertStatusMessageIs('success');

    $this->assertSoftDeleted($statusLabel);
});
