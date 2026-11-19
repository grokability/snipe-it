<?php

use App\Enums\ActionType;
use App\Models\Actionlog;
use App\Models\Company;
use App\Models\Maintenance;
use App\Models\User;

test('empty selection redirects with warning', function () {
    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('maintenances.bulk'), ['bulk_actions' => 'delete', 'ids' => []])
        ->assertSessionHas('warning')
        ->assertRedirect(route('maintenances.index'));
});

test('bulk delete removes selected records', function () {
    $keeper = Maintenance::factory()->create();
    $doomed = Maintenance::factory()->count(3)->create();

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('maintenances.bulk'), [
            'bulk_actions' => 'delete',
            'ids' => $doomed->pluck('id')->all(),
        ])
        ->assertRedirect(route('maintenances.index'))
        ->assertSessionHas('success');

    $doomed->each(fn ($m) => $this->assertSoftDeleted('maintenances', ['id' => $m->id]));
    $this->assertDatabaseHas('maintenances', ['id' => $keeper->id, 'deleted_at' => null]);
});

test('bulk endpoint requires asset edit permission', function () {
    $maintenance = Maintenance::factory()->create();

    // Top-level authorize('update', Asset::class) in the controller
    // blocks users without any assets.edit rights from even reaching
    // the per-row policy checks — 403 rather than a "0 succeeded"
    // redirect.
    $this->actingAs(User::factory()->create())
        ->post(route('maintenances.bulk'), [
            'bulk_actions' => 'delete',
            'ids' => [$maintenance->id],
        ])
        ->assertForbidden();

    $this->assertDatabaseHas('maintenances', ['id' => $maintenance->id, 'deleted_at' => null]);
});

test('bulk delete cannot reach maintenance in other company when fmcs enabled', function () {
    $this->settings->enableMultipleFullCompanySupport();

    [$companyA, $companyB] = Company::factory()->count(2)->create();

    // Actor is in company A with asset-edit permission (companyA
    // maintenance CRUD is fine, cross-company must be blocked).
    $actor = $companyA->users()->save(User::factory()->editAssets()->make());

    $maintenanceB = Maintenance::factory()->create();
    $maintenanceB->asset->update(['company_id' => $companyB->id]);

    $this->actingAs($actor)
        ->post(route('maintenances.bulk'), [
            'bulk_actions' => 'delete',
            'ids' => [$maintenanceB->id],
        ])
        ->assertRedirect(route('maintenances.index'));

    // Maintenance record from company B must NOT be touched — the
    // CompanyableChildTrait global scope on the whereIn lookup
    // excludes it, so the row never reaches the delete branch.
    $this->assertDatabaseHas('maintenances', ['id' => $maintenanceB->id, 'deleted_at' => null]);
});

test('bulk complete cannot reach maintenance in other company when fmcs enabled', function () {
    $this->settings->enableMultipleFullCompanySupport();

    [$companyA, $companyB] = Company::factory()->count(2)->create();
    $actor = $companyA->users()->save(User::factory()->editAssets()->make());

    $maintenanceB = Maintenance::factory()->create(['completed_at' => null]);
    $maintenanceB->asset->update(['company_id' => $companyB->id]);

    $this->actingAs($actor)
        ->post(route('maintenances.bulk'), [
            'bulk_actions' => 'complete',
            'ids' => [$maintenanceB->id],
        ])
        ->assertRedirect(route('maintenances.index'));

    $maintenanceB->refresh();
    expect($maintenanceB->completed_at)->toBeNull('Cross-company maintenance must not be marked complete');
});

test('bulk complete marks selected and logs', function () {
    $actor = User::factory()->superuser()->create();
    $maintenance = Maintenance::factory()->create();

    $this->actingAs($actor)
        ->post(route('maintenances.bulk'), [
            'bulk_actions' => 'complete',
            'ids' => [$maintenance->id],
        ])
        ->assertRedirect(route('maintenances.index'))
        ->assertSessionHas('success');

    $maintenance->refresh();
    expect($maintenance->completed_at)->not->toBeNull();
    expect($maintenance->completed_by)->toEqual($actor->id);

    $this->assertDatabaseHas('action_logs', [
        'item_type' => Maintenance::class,
        'item_id' => $maintenance->id,
        'action_type' => ActionType::MaintenanceComplete->value,
    ]);
});

test('bulk complete skips already completed', function () {
    $alreadyDone = Maintenance::factory()->create([
        'completed_at' => now()->subDay(),
        'completed_by' => User::factory()->create()->id,
    ]);

    $existingLogs = Actionlog::where('item_type', Maintenance::class)
        ->where('item_id', $alreadyDone->id)
        ->count();

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('maintenances.bulk'), [
            'bulk_actions' => 'complete',
            'ids' => [$alreadyDone->id],
        ])
        ->assertRedirect(route('maintenances.index'));

    // completed_at should stay untouched; no new completion log entry.
    $alreadyDone->refresh();
    expect($alreadyDone->completed_at->isYesterday())->toBeTrue();
    expect(Actionlog::where('item_type', Maintenance::class)
        ->where('item_id', $alreadyDone->id)
        ->count())->toEqual($existingLogs, 'A second completion should not create another action-log entry');
});

test('unknown action returns error', function () {
    $maintenance = Maintenance::factory()->create();

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('maintenances.bulk'), [
            'bulk_actions' => 'nuke-from-orbit',
            'ids' => [$maintenance->id],
        ])
        ->assertRedirect(route('maintenances.index'))
        ->assertSessionHas('error');
});

test('same origin referer is used for post action redirect', function () {
    $maintenance = Maintenance::factory()->create();
    $referer = config('app.url').'/maintenances?completed=true';

    $this->actingAs(User::factory()->superuser()->create())
        ->withHeader('referer', $referer)
        ->post(route('maintenances.bulk'), [
            'bulk_actions' => 'delete',
            'ids' => [$maintenance->id],
        ])
        ->assertRedirect($referer);
});

test('offsite referer is rejected and falls back to index', function () {
    $maintenance = Maintenance::factory()->create();

    // Attacker-controlled referer must not be honored — protects
    // against open-redirect / phishing hand-offs after the POST.
    $this->actingAs(User::factory()->superuser()->create())
        ->withHeader('referer', 'https://evil.example.com/steal-session')
        ->post(route('maintenances.bulk'), [
            'bulk_actions' => 'delete',
            'ids' => [$maintenance->id],
        ])
        ->assertRedirect(route('maintenances.index'));
});
