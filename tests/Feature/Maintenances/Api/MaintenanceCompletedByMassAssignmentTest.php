<?php

use App\Models\Asset;
use App\Models\Maintenance;
use App\Models\User;

test('patch cannot set completed by to another user', function () {
    $editor = User::factory()->editAssets()->create();
    $otherAdmin = User::factory()->superuser()->create();

    $asset = Asset::factory()->create();
    $maintenance = Maintenance::factory()->create([
        'asset_id' => $asset->id,
        'completed_by' => null,
    ]);

    $this->actingAsForApi($editor)
        ->patchJson(route('api.maintenances.update', $maintenance->id), [
            'completed_by' => $otherAdmin->id,
        ]);

    $maintenance->refresh();

    expect($maintenance->completed_by)->toBeNull('completed_by must stay null when it was null before. The update endpoint must not accept a client-supplied value for this attribution column.');
});

test('patch does not overwrite existing completed by', function () {
    $editor = User::factory()->editAssets()->create();
    $existingCompleter = User::factory()->superuser()->create();
    $otherAdmin = User::factory()->superuser()->create();

    $asset = Asset::factory()->create();
    $maintenance = Maintenance::factory()->create([
        'asset_id' => $asset->id,
        'completed_by' => $existingCompleter->id,
    ]);

    $this->actingAsForApi($editor)
        ->patchJson(route('api.maintenances.update', $maintenance->id), [
            'completed_by' => $otherAdmin->id,
            'notes' => 'editing other legitimate fields',
        ]);

    $maintenance->refresh();

    expect($maintenance->completed_by)->toBe($existingCompleter->id, 'An existing completed_by must survive a PATCH that attempts to overwrite it. The dedicated complete() action is the only legitimate writer.');
});
