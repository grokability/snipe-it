<?php

use App\Models\Asset;
use App\Models\User;

test('user with permissions can access page', function () {
    $user = User::factory()->viewAssets()->deleteAssets()->editAssets()->create();
    $assets = Asset::factory()->count(2)->create();

    $id_array = $assets->pluck('id')->toArray();

    $this->actingAs($user)->post('/hardware/bulkedit', [
        'ids' => $id_array,
        'order' => 'asc',
        'bulk_actions' => 'delete',
        'sort' => 'id',
    ])->assertStatus(200);
});

test('standard user cannot access page', function () {
    $user = User::factory()->create();
    $assets = Asset::factory()->count(2)->create();

    $id_array = $assets->pluck('id')->toArray();

    $this->actingAs($user)->post('/hardware/bulkdelete', [
        'ids' => $id_array,
        'bulk_actions' => 'delete',
    ])->assertStatus(403);
});

test('page redirect from interstitial if no assets selected to delete', function () {
    $user = User::factory()->viewAssets()->deleteAssets()->editAssets()->create();
    $response = $this->actingAs($user)
        ->post('/hardware/bulkdelete', [
            'ids' => null,
            'bulk_actions' => 'delete',
        ])
        ->assertStatus(302)
        ->assertRedirect(route('hardware.index'));

    $this->followRedirects($response)->assertSee('alert-danger');
});

test('page redirect from interstitial if no assets selected to restore', function () {
    $user = User::factory()->viewAssets()->deleteAssets()->editAssets()->create();
    $response = $this->actingAs($user)
        ->from(route('hardware.index'))
        ->post('/hardware/bulkrestore', [
            'ids' => null,
            'bulk_actions' => 'delete',
        ])
        ->assertStatus(302)
        ->assertRedirect(route('hardware.index'));

    $this->followRedirects($response)->assertSee('alert-danger');
});

test('bulk delete selected assets from interstitial', function () {
    $user = User::factory()->viewAssets()->deleteAssets()->editAssets()->create();
    $assets = Asset::factory()->count(2)->create();

    $id_array = $assets->pluck('id')->toArray();

    $response = $this->actingAs($user)
        ->from(route('hardware.bulkdelete.store'))
        ->post('/hardware/bulkdelete', [
            'ids' => $id_array,
            'bulk_actions' => 'delete',
        ])->assertStatus(302);

    Asset::findMany($id_array)->each(function (Asset $asset) {
        expect($asset->deleted_at)->not->toBeNull();
        $this->assertHasTheseActionLogs($asset, ['create', 'delete']);
    });

    $this->followRedirects($response)->assertSee('alert-success');
});

test('bulk restore selected assets from interstitial', function () {
    $user = User::factory()->viewAssets()->deleteAssets()->editAssets()->create();
    $asset = Asset::factory()->deleted()->create();
    expect($asset)->not->toBeNull();

    $asset->refresh();
    $id_array = [$asset->id];
    expect(count($id_array))->toEqual(1);

    $test_ran = false;

    // Check that the assets are deleted
    Asset::whereIn('id', $id_array)->withTrashed()->each(function (Asset $asset) use (&$test_ran) {
        $test_ran = true;
        expect($asset->deleted_at)->not->toBeNull();
    });
    expect($test_ran)->toBeTrue('Test never actually ran!');

    $response = $this->actingAs($user)
        ->from(route('hardware.bulkdelete.store'))
        ->post(route('hardware/bulkrestore'), [
            'ids' => [$asset->id],
        ])->assertStatus(302);

    $this->followRedirects($response)->assertSee('alert-success');

    $test_ran = false;
    Asset::findMany($id_array)->each(function (Asset $asset) use (&$test_ran) {
        $test_ran = true;
        expect($asset->deleted_at)->toBeNull();
        $this->assertHasTheseActionLogs($asset, ['create', /* 'delete', */ 'restore'/* , 'fart' */]); // SHIT
    });
    expect($test_ran)->toBeTrue('Test never actually ran!');
});

test('action log created upon bulk delete', function () {
    $user = User::factory()->viewAssets()->deleteAssets()->editAssets()->create();
    $asset = Asset::factory()->create();

    $this->actingAs($user)
        ->from(route('hardware.bulkdelete.store'))
        ->post('/hardware/bulkdelete', [
            'ids' => [$asset->id],
            'bulk_actions' => 'delete',
        ]);

    $this->assertDatabaseHas('action_logs',
        [
            'action_type' => 'delete',
            'target_id' => null,
            'target_type' => null,
            'item_id' => $asset->id,
            'item_type' => Asset::class,
        ]
    );

    $asset->refresh();
    expect($asset->assigned_to)->toBeNull();
    expect($asset->assigned_type)->toBeNull();
});

test('action log created upon bulk restore', function () {
    $user = User::factory()->viewAssets()->deleteAssets()->editAssets()->create();
    $asset = Asset::factory()->deleted()->create();

    $this->actingAs($user)
        ->from(route('hardware.bulkdelete.store'))
        ->post(route('hardware/bulkrestore'), [
            'ids' => [$asset->id],
            'bulk_actions' => 'restore',
        ]);

    $this->assertDatabaseHas('action_logs',
        [
            'action_type' => 'restore',
            'target_id' => null,
            'target_type' => null,
            'item_id' => $asset->id,
            'item_type' => Asset::class,
        ]
    );
});

test('bulk delete assigned asset triggers error', function () {
    $user = User::factory()->viewAssets()->deleteAssets()->editAssets()->create();
    $asset = Asset::factory()->create([
        'id' => 5,
        'assigned_to' => $user->id,
        'assigned_type' => User::class,
        'asset_tag' => '12345',
    ]);

    $response = $this->actingAs($user)
        ->from(route('hardware.bulkdelete.store'))
        ->post('/hardware/bulkdelete', [
            'ids' => [$asset->id],
            'bulk_actions' => 'delete',
        ]);

    expect($response->getStatusCode())->toEqual(302);
    expect($response->headers->get('Location'))->toEqual(route('hardware.index'));

    $errorMessage = session('error');
    $expectedMessage = trans_choice('admin/hardware/message.delete.assigned_to_error', 1, ['asset_tag' => $asset->asset_tag]);
    expect($errorMessage)->toEqual($expectedMessage);
});

test('bulk delete redirects to stashed same origin url', function () {
    $user = User::factory()->viewAssets()->deleteAssets()->create();
    $asset = Asset::factory()->create();
    $originUrl = route('hardware.index').'?status_type=Deployed';

    $this->actingAs($user)
        ->withSession(['bulk_back_url' => $originUrl])
        ->post('/hardware/bulkdelete', [
            'ids' => [$asset->id],
            'bulk_actions' => 'delete',
        ])
        ->assertRedirect($originUrl);
});

test('bulk delete ignores poisoned stashed url', function () {
    // Defense-in-depth: even if BulkAssetsController::edit()'s
    // write-side gate were bypassed, the read here must still keep
    // the redirect on-domain.
    $user = User::factory()->viewAssets()->deleteAssets()->create();
    $asset = Asset::factory()->create();

    $this->actingAs($user)
        ->withSession(['bulk_back_url' => 'https://evil.example.com/steal-session'])
        ->post('/hardware/bulkdelete', [
            'ids' => [$asset->id],
            'bulk_actions' => 'delete',
        ])
        ->assertRedirect(route('hardware.index'));
});
