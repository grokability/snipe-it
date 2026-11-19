<?php

use App\Models\Actionlog;
use App\Models\Component;
use App\Models\User;

test('soft delete writes only a single delete log entry', function () {
    $this->actingAs(User::factory()->superuser()->create());

    $component = Component::factory()->create();

    $logsBefore = Actionlog::where('item_type', Component::class)
        ->where('item_id', $component->id)
        ->count();

    $component->delete();

    $logsAfter = Actionlog::where('item_type', Component::class)
        ->where('item_id', $component->id)
        ->orderBy('id')
        ->get();

    expect($logsAfter)->toHaveCount($logsBefore + 1, 'soft-delete should write exactly one new action_log row');
    expect($logsAfter->last()->action_type)->toBe('delete');
});

test('regular update still logs an update entry', function () {
    // Pin that stripping deleted_at from the diff didn't accidentally
    // disable all update logging.
    $this->actingAs(User::factory()->superuser()->create());

    $component = Component::factory()->create();
    $component->name = 'Renamed';
    $component->save();

    expect(Actionlog::where('item_type', Component::class)
        ->where('item_id', $component->id)
        ->where('action_type', 'update')
        ->exists())->toBeTrue('a plain update should still log an "update" row');
});
