<?php

use App\Models\AssetModel;
use App\Models\Statuslabel;
use App\Models\User;

test('permission required to view create asset page', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('hardware.create'))
        ->assertForbidden();
});

test('permission required to store asset', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('hardware.store'), [
            'model_id' => AssetModel::factory()->create()->id,
            'status_id' => Statuslabel::factory()->create()->id,
        ])
        ->assertForbidden();
});

test('create asset page renders', function () {
    $this->actingAs(User::factory()->superuser()->create())
        ->get(route('hardware.create'))
        ->assertOk();
});

test('asset can be stored with serial required and serial provided', function () {
    $user = User::factory()->superuser()->create();
    $this->actingAs($user);

    $model = AssetModel::factory()->create([
        'require_serial' => 1,
    ]);

    $response = $this->post(route('hardware.store'), [
        'model_id' => $model->id,
        'serials' => [1 => 'ABC123'],
        'asset_tags' => [1 => '1234'],
        'status_id' => 1,
        // other required fields...
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success-unescaped');
    $this->assertNotEquals(
        trans('admin/hardware/form.serial_required'),
        session('error')
    );
    $this->assertDatabaseHas('assets', [
        'model_id' => $model->id,
        'serial' => 'ABC123',
        'asset_tag' => '1234',
    ]);
});

test('asset cannot be stored if serial required and missing', function () {
    $user = User::factory()->superuser()->create();
    $this->actingAs($user);

    $model = AssetModel::factory()->create([
        'require_serial' => 1,
    ]);

    $response = $this->post(route('hardware.store'), [
        'model_id' => $model->id,
        'serials' => [], // ← serial missing
        'asset_tags' => [1 => '1234'],
        'status_id' => 1,
    ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors(['serials.1']);

    $this->assertDatabaseMissing('assets', [
        'model_id' => $model->id,
        'asset_tag' => '1234',
    ]);

    $response->assertSessionMissing('success-unescaped');
});
