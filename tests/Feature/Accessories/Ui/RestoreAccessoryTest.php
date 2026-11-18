<?php

use App\Models\Accessory;
use App\Models\Company;
use App\Models\User;

test('single restore requires delete permission', function () {
    $accessory = Accessory::factory()->create();
    $accessory->delete();

    $this->actingAs(User::factory()->viewAccessories()->create())
        ->post(route('accessories.restore', ['accessoryId' => $accessory->id]))
        ->assertForbidden();

    expect($accessory->fresh()->deleted_at)->not->toBeNull();
});

test('single restore brings accessory back', function () {
    $accessory = Accessory::factory()->create();
    $accessory->delete();

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('accessories.restore', ['accessoryId' => $accessory->id]))
        ->assertRedirect(route('accessories.show', $accessory));

    expect($accessory->fresh()->deleted_at)->toBeNull();
});

test('single restore rejects already live accessory', function () {
    $accessory = Accessory::factory()->create();

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('accessories.restore', ['accessoryId' => $accessory->id]))
        ->assertRedirect()
        ->assertSessionHas('error');

    expect($accessory->fresh()->deleted_at)->toBeNull();
});

test('bulk restore brings selected accessories back', function () {
    $one = Accessory::factory()->create();
    $two = Accessory::factory()->create();
    $one->delete();
    $two->delete();

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('accessories.bulkedit.show'), [
            'ids' => [$one->id, $two->id],
            'bulk_actions' => 'restore',
        ])
        ->assertRedirect(route('accessories.index', ['status' => 'Deleted']))
        ->assertSessionHas('success');

    expect($one->fresh()->deleted_at)->toBeNull();
    expect($two->fresh()->deleted_at)->toBeNull();
});

test('bulk restore blocks cross company under fmcs', function () {
    $this->settings->enableMultipleFullCompanySupport();

    [$companyA, $companyB] = Company::factory()->count(2)->create();
    $actor = $companyA->users()->save(User::factory()->editAccessories()->deleteAccessories()->viewAccessories()->make());
    $foreign = Accessory::factory()->for($companyB)->create();
    $foreign->delete();

    $this->actingAs($actor)
        ->post(route('accessories.bulkedit.show'), [
            'ids' => [$foreign->id],
            'bulk_actions' => 'restore',
        ])
        ->assertRedirect();

    expect($foreign->fresh()->deleted_at)->not->toBeNull();
});
