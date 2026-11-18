<?php

use App\Models\AssetModel;
use App\Models\Statuslabel;
use App\Models\User;

test('web create with assigned user without checkout permission creates asset and flags skipped checkout', function () {
    $creator = User::factory()->createAssets()->create();
    $victim = User::factory()->create();
    $model = AssetModel::factory()->create();
    $status = Statuslabel::factory()->readyToDeploy()->create();

    $tag = 'CHECKOUT-GATE-UI-U-'.uniqid();
    $this->actingAs($creator)
        ->post(route('hardware.store'), [
            'asset_tags' => [1 => $tag],
            'model_id' => $model->id,
            'status_id' => $status->id,
            'assigned_user' => $victim->id,
        ])
        ->assertRedirect()
        ->assertSessionHas('warning', trans('admin/hardware/message.create.checkout_skipped_no_permission'));

    $this->assertDatabaseHas('assets', [
        'asset_tag' => $tag,
        'assigned_to' => null,
    ]);
    $this->assertDatabaseMissing('action_logs', [
        'target_id' => $victim->id,
        'target_type' => User::class,
        'action_type' => 'checkout',
    ]);
});

test('web create without assignment does not flag skipped checkout', function () {
    $creator = User::factory()->createAssets()->create();
    $model = AssetModel::factory()->create();
    $status = Statuslabel::factory()->readyToDeploy()->create();

    $tag = 'CREATE-ONLY-UI-'.uniqid();
    $response = $this->actingAs($creator)
        ->post(route('hardware.store'), [
            'model_id' => $model->id,
            'status_id' => $status->id,
            'asset_tags' => [1 => $tag],
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('assets', ['asset_tag' => $tag]);
    $this->assertNotSame(
        trans('admin/hardware/message.create.checkout_skipped_no_permission'),
        session('warning')
    );
});

test('web create with assignment succeeds when checkout permission granted', function () {
    $creator = User::factory()->createAssets()->checkoutAssets()->create();
    $victim = User::factory()->create();
    $model = AssetModel::factory()->create();
    $status = Statuslabel::factory()->readyToDeploy()->create();

    $tag = 'CHECKOUT-OK-UI-'.uniqid();
    $this->actingAs($creator)
        ->post(route('hardware.store'), [
            'model_id' => $model->id,
            'status_id' => $status->id,
            'asset_tags' => [1 => $tag],
            'assigned_user' => $victim->id,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('assets', [
        'asset_tag' => $tag,
        'assigned_to' => $victim->id,
    ]);
});
