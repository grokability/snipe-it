<?php

use App\Models\Maintenance;
use App\Models\Setting;

test('active scope excludes completed maintenances', function () {
    $active = Maintenance::factory()->create(['completed_at' => null]);
    $done = Maintenance::factory()->create(['completed_at' => now()]);

    $ids = Maintenance::active()->pluck('id');

    expect($ids)->toContain($active->id);
    expect($ids)->not->toContain($done->id);
});

test('completed scope excludes active maintenances', function () {
    $active = Maintenance::factory()->create(['completed_at' => null]);
    $done = Maintenance::factory()->create(['completed_at' => now()]);

    $ids = Maintenance::completed()->pluck('id');

    expect($ids)->not->toContain($active->id);
    expect($ids)->toContain($done->id);
});

test('due for completion returns items in warning window', function () {
    $settings = Setting::factory()->create(['audit_warning_days' => 7]);

    $due = Maintenance::factory()->create([
        'completion_date' => now()->addDays(3)->format('Y-m-d'),
        'completed_at' => null,
    ]);

    $notDueYet = Maintenance::factory()->create([
        'completion_date' => now()->addDays(30)->format('Y-m-d'),
        'completed_at' => null,
    ]);

    $alreadyDone = Maintenance::factory()->create([
        'completion_date' => now()->addDays(3)->format('Y-m-d'),
        'completed_at' => now(),
    ]);

    $ids = Maintenance::dueForCompletion($settings)->pluck('id');

    expect($ids)->toContain($due->id);
    expect($ids)->not->toContain($notDueYet->id);
    expect($ids)->not->toContain($alreadyDone->id);
});

test('overdue for completion returns past due items', function () {
    $overdue = Maintenance::factory()->create([
        'completion_date' => now()->subDay()->format('Y-m-d'),
        'completed_at' => null,
    ]);

    $futureDate = Maintenance::factory()->create([
        'completion_date' => now()->addDays(5)->format('Y-m-d'),
        'completed_at' => null,
    ]);

    $alreadyDone = Maintenance::factory()->create([
        'completion_date' => now()->subDay()->format('Y-m-d'),
        'completed_at' => now(),
    ]);

    $ids = Maintenance::overdueForCompletion()->pluck('id');

    expect($ids)->toContain($overdue->id);
    expect($ids)->not->toContain($futureDate->id);
    expect($ids)->not->toContain($alreadyDone->id);
});

test('overdue for completion excludes items with no completion date', function () {
    $noDate = Maintenance::factory()->create([
        'completion_date' => null,
        'completed_at' => null,
    ]);

    $ids = Maintenance::overdueForCompletion()->pluck('id');

    expect($ids)->not->toContain($noDate->id);
});

test('due or overdue returns both overdue and due', function () {
    $settings = Setting::factory()->create(['audit_warning_days' => 7]);

    $overdue = Maintenance::factory()->create([
        'completion_date' => now()->subDay()->format('Y-m-d'),
        'completed_at' => null,
    ]);

    $due = Maintenance::factory()->create([
        'completion_date' => now()->addDays(3)->format('Y-m-d'),
        'completed_at' => null,
    ]);

    $fine = Maintenance::factory()->create([
        'completion_date' => now()->addDays(30)->format('Y-m-d'),
        'completed_at' => null,
    ]);

    $done = Maintenance::factory()->create([
        'completion_date' => now()->subDay()->format('Y-m-d'),
        'completed_at' => now(),
    ]);

    $ids = Maintenance::dueOrOverdueForCompletion($settings)->pluck('id');

    expect($ids)->toContain($overdue->id);
    expect($ids)->toContain($due->id);
    expect($ids)->not->toContain($fine->id);
    expect($ids)->not->toContain($done->id);
});
