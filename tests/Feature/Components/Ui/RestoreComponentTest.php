<?php

use App\Models\Company;
use App\Models\Component;
use App\Models\User;

test('single restore requires delete permission', function () {
    $component = Component::factory()->create();
    $component->delete();

    $this->actingAs(User::factory()->viewComponents()->create())
        ->post(route('components.restore', ['componentID' => $component->id]))
        ->assertForbidden();

    expect($component->fresh()->deleted_at)->not->toBeNull();
});

test('single restore brings component back', function () {
    $component = Component::factory()->create();
    $component->delete();

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('components.restore', ['componentID' => $component->id]))
        ->assertRedirect(route('components.show', $component));

    expect($component->fresh()->deleted_at)->toBeNull();
});

test('single restore rejects already live component', function () {
    $component = Component::factory()->create();

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('components.restore', ['componentID' => $component->id]))
        ->assertRedirect()
        ->assertSessionHas('error');

    expect($component->fresh()->deleted_at)->toBeNull();
});

test('single restore rejects unknown id', function () {
    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('components.restore', ['componentID' => 999999]))
        ->assertRedirect(route('components.index'))
        ->assertSessionHas('error');
});

test('single restore blocks cross company under fmcs', function () {
    // postRestore now authorizes against the instance, so the
    // policy's before() hook runs the FMCS cross-tenant check.
    // Pin against the pre-fix behavior where class-level authorize
    // skipped that guard.
    $this->settings->enableMultipleFullCompanySupport();

    [$companyA, $companyB] = Company::factory()->count(2)->create();
    $actor = $companyA->users()->save(User::factory()->deleteComponents()->viewComponents()->make());
    $foreign = Component::factory()->for($companyB)->create();
    $foreign->delete();

    $this->actingAs($actor)
        ->post(route('components.restore', ['componentID' => $foreign->id]))
        ->assertRedirect();

    expect($foreign->fresh()->deleted_at)->not->toBeNull();
});

test('bulk restore requires delete permission', function () {
    $component = Component::factory()->create();
    $component->delete();

    $this->actingAs(User::factory()->viewComponents()->create())
        ->post(route('components.bulkedit.show'), [
            'ids' => [$component->id],
            'bulk_actions' => 'restore',
        ])
        ->assertForbidden();

    expect($component->fresh()->deleted_at)->not->toBeNull();
});

test('bulk restore brings selected components back', function () {
    $one = Component::factory()->create();
    $two = Component::factory()->create();
    $one->delete();
    $two->delete();

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('components.bulkedit.show'), [
            'ids' => [$one->id, $two->id],
            'bulk_actions' => 'restore',
        ])
        ->assertRedirect(route('components.index', ['status' => 'Deleted']))
        ->assertSessionHas('success');

    expect($one->fresh()->deleted_at)->toBeNull();
    expect($two->fresh()->deleted_at)->toBeNull();
});

test('bulk restore skips live rows without error', function () {
    // Stale selection: one row is already live. The restore
    // dispatcher silently skips live rows so the deleted ones
    // still succeed. Pin so a future refactor can't tighten this
    // into a hard failure that blocks the batch.
    $deleted = Component::factory()->create();
    $deleted->delete();

    $live = Component::factory()->create();

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('components.bulkedit.show'), [
            'ids' => [$deleted->id, $live->id],
            'bulk_actions' => 'restore',
        ])
        ->assertRedirect(route('components.index', ['status' => 'Deleted']))
        ->assertSessionHas('success');

    expect($deleted->fresh()->deleted_at)->toBeNull();
    expect($live->fresh()->deleted_at)->toBeNull();
});

test('bulk restore blocks cross company under fmcs', function () {
    $this->settings->enableMultipleFullCompanySupport();

    [$companyA, $companyB] = Company::factory()->count(2)->create();
    $actor = $companyA->users()->save(User::factory()->editComponents()->deleteComponents()->viewComponents()->make());
    $foreign = Component::factory()->for($companyB)->create();
    $foreign->delete();

    $this->actingAs($actor)
        ->post(route('components.bulkedit.show'), [
            'ids' => [$foreign->id],
            'bulk_actions' => 'restore',
        ])
        ->assertRedirect();

    expect($foreign->fresh()->deleted_at)->not->toBeNull();
});
