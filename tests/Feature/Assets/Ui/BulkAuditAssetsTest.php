<?php

use App\Models\User;
use Carbon\Carbon;

pest()->group('auditing');

beforeEach(function () {
    $this->actor = User::factory()->auditAssets()->create();
});

test('permission required to view page', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('assets.bulkaudit'))
        ->assertStatus(403);
});

test('can view audit page', function () {
    $this->actingAs($this->actor)
        ->get(route('assets.bulkaudit'))
        ->assertViewIs('hardware.quickscan');
});

test('bulk audit page is given todays date when audit interval is null', function () {
    $this->settings->setAuditInterval(null);

    $this->actingAs($this->actor)
        ->get(route('assets.bulkaudit'))
        ->assertViewIs('hardware.quickscan')
        ->assertViewHas('next_audit_date', Carbon::now()->toDateString());
});

test('bulk audit page is given correct date when audit interval is set', function () {
    $this->settings->setAuditInterval(5);

    $this->actingAs($this->actor)
        ->get(route('assets.bulkaudit'))
        ->assertViewIs('hardware.quickscan')
        ->assertViewHas('next_audit_date', Carbon::now()->addMonths(5)->toDateString());
});
