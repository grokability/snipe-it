<?php

use App\Models\Asset;
use App\Models\User;

test('user with permissions can access page', function () {
    $assets = Asset::factory()->count(20)->create();
    $id_array = $assets->pluck('id')->toArray();

    $this->actingAs(User::factory()->viewAssets()->create())->post('/hardware/bulkedit', [
        'ids' => $id_array,
        'bulk_actions' => 'labels',
    ])->assertStatus(200);
});

test('redirect of no assets selected', function () {
    $id_array = [];
    $this->actingAs(User::factory()->viewAssets()->create())
        ->from(route('hardware.index'))
        ->post('/hardware/bulkedit', [
            'ids' => $id_array,
            'bulk_actions' => 'Labels',
        ])->assertStatus(302)
        ->assertRedirect(route('hardware.index'));
});
