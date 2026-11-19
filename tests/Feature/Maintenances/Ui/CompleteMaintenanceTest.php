<?php

use App\Models\Actionlog;
use App\Models\Maintenance;
use App\Models\User;
use Carbon\Carbon;

test('requires permission', function () {
    $maintenance = Maintenance::factory()->create();

    $this->actingAs(User::factory()->create())
        ->post(route('maintenances.complete', $maintenance))
        ->assertForbidden();

    $this->assertDatabaseMissing('maintenances', [
        'id' => $maintenance->id,
        'completed_at' => now(),
    ]);
});

test('can mark maintenance complete', function () {
    $actor = User::factory()->superuser()->create();
    $maintenance = Maintenance::factory()->create();

    $this->actingAs($actor)
        ->post(route('maintenances.complete', $maintenance))
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    $maintenance->refresh();
    expect($maintenance->completed_at)->not->toBeNull();
    expect($maintenance->completed_by)->toEqual($actor->id);

    $this->assertHasTheseActionLogs($maintenance, ['create', 'completed']);
});

test('marking complete does not create update log', function () {
    $actor = User::factory()->superuser()->create();
    $maintenance = Maintenance::factory()->create();

    $this->actingAs($actor)
        ->post(route('maintenances.complete', $maintenance));

    $updateLogs = Actionlog::where('item_type', Maintenance::class)
        ->where('item_id', $maintenance->id)
        ->where('action_type', 'update')
        ->count();

    expect($updateLogs)->toEqual(0);
});

test('cannot mark already completed maintenance complete', function () {
    $actor = User::factory()->superuser()->create();
    $maintenance = Maintenance::factory()->create(['completed_at' => now()->subDay(), 'completed_by' => $actor->id]);

    $this->actingAs($actor)
        ->post(route('maintenances.complete', $maintenance))
        ->assertRedirect()
        ->assertSessionHas('warning');

    $this->assertHasTheseActionLogs($maintenance, ['create']);
});

test('completion note is saved in actionlog', function () {
    $actor = User::factory()->superuser()->create();
    $maintenance = Maintenance::factory()->create();

    $this->actingAs($actor)
        ->post(route('maintenances.complete', $maintenance), ['note' => 'Widget replaced']);

    $log = Actionlog::where('item_type', Maintenance::class)
        ->where('item_id', $maintenance->id)
        ->where('action_type', 'completed')
        ->first();

    expect($log)->not->toBeNull();
    expect($log->note)->toEqual('Widget replaced');
});

test('duration is calculated from created at', function () {
    $actor = User::factory()->superuser()->create();

    Carbon::setTestNow(Carbon::create(2026, 1, 1));
    $maintenance = Maintenance::factory()->create(['start_date' => '2025-06-01']);
    Carbon::setTestNow(Carbon::create(2026, 1, 11));

    try {
        $this->actingAs($actor)
            ->post(route('maintenances.complete', $maintenance))
            ->assertRedirect();

        $maintenance->refresh();
        expect($maintenance->asset_maintenance_time)->toEqual(10);
    } finally {
        Carbon::setTestNow();
    }
});
