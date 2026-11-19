<?php

use App\Models\Actionlog;
use App\Models\Maintenance;
use App\Models\User;
use Carbon\Carbon;

test('requires permission', function () {
    $maintenance = Maintenance::factory()->create();

    $this->actingAsForApi(User::factory()->create())
        ->postJson(route('api.maintenances.complete', $maintenance))
        ->assertForbidden();
});

test('can mark maintenance complete', function () {
    $actor = User::factory()->superuser()->create();
    $maintenance = Maintenance::factory()->create();

    $this->actingAsForApi($actor)
        ->postJson(route('api.maintenances.complete', $maintenance))
        ->assertOk()
        ->assertStatusMessageIs('success');

    $maintenance->refresh();
    expect($maintenance->completed_at)->not->toBeNull();
    expect($maintenance->completed_by)->toEqual($actor->id);

    $this->assertHasTheseActionLogs($maintenance, ['create', 'completed']);
});

test('marking complete does not create update log', function () {
    $actor = User::factory()->superuser()->create();
    $maintenance = Maintenance::factory()->create();

    $this->actingAsForApi($actor)
        ->postJson(route('api.maintenances.complete', $maintenance));

    $updateLogs = Actionlog::where('item_type', Maintenance::class)
        ->where('item_id', $maintenance->id)
        ->where('action_type', 'update')
        ->count();

    expect($updateLogs)->toEqual(0);
});

test('cannot mark already completed maintenance complete', function () {
    $actor = User::factory()->superuser()->create();
    $maintenance = Maintenance::factory()->create(['completed_at' => now()->subDay(), 'completed_by' => $actor->id]);

    $this->actingAsForApi($actor)
        ->postJson(route('api.maintenances.complete', $maintenance))
        ->assertStatusMessageIs('error');

    $this->assertHasTheseActionLogs($maintenance, ['create']);
});

test('completion note is saved in actionlog', function () {
    $actor = User::factory()->superuser()->create();
    $maintenance = Maintenance::factory()->create();

    $this->actingAsForApi($actor)
        ->postJson(route('api.maintenances.complete', $maintenance), ['note' => 'Fixed the thing'])
        ->assertOk();

    $log = Actionlog::where('item_type', Maintenance::class)
        ->where('item_id', $maintenance->id)
        ->where('action_type', 'completed')
        ->first();

    expect($log)->not->toBeNull();
    expect($log->note)->toEqual('Fixed the thing');
});

test('duration is calculated from created at', function () {
    $actor = User::factory()->superuser()->create();

    Carbon::setTestNow(Carbon::create(2026, 1, 1));
    $maintenance = Maintenance::factory()->create(['start_date' => '2025-06-01']);
    Carbon::setTestNow(Carbon::create(2026, 1, 11));

    try {
        $this->actingAsForApi($actor)
            ->postJson(route('api.maintenances.complete', $maintenance))
            ->assertOk();

        $maintenance->refresh();
        expect($maintenance->asset_maintenance_time)->toEqual(10);
    } finally {
        Carbon::setTestNow();
    }
});
