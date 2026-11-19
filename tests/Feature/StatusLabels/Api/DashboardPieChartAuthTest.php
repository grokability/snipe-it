<?php

use App\Models\User;

test('asset viewer can reach pie chart by name endpoint', function () {
    $this->actingAsForApi(User::factory()->viewAssets()->create())
        ->getJson(route('api.statuslabels.assets.byname'))
        ->assertOk();
});

test('asset viewer can reach pie chart by meta status endpoint', function () {
    $this->actingAsForApi(User::factory()->viewAssets()->create())
        ->getJson(route('api.statuslabels.assets.bytype'))
        ->assertOk();
});

test('status label viewer without asset view can still reach pie chart by name', function () {
    $this->actingAsForApi(User::factory()->create([
        'permissions' => json_encode(['statuslabels.view' => '1']),
    ]))
        ->getJson(route('api.statuslabels.assets.byname'))
        ->assertOk();
});

test('status label viewer without asset view can still reach pie chart by meta status', function () {
    $this->actingAsForApi(User::factory()->create([
        'permissions' => json_encode(['statuslabels.view' => '1']),
    ]))
        ->getJson(route('api.statuslabels.assets.bytype'))
        ->assertOk();
});

test('user with no relevant permissions is forbidden from pie chart by name', function () {
    $this->actingAsForApi(User::factory()->create())
        ->getJson(route('api.statuslabels.assets.byname'))
        ->assertForbidden();
});

test('user with no relevant permissions is forbidden from pie chart by meta status', function () {
    $this->actingAsForApi(User::factory()->create())
        ->getJson(route('api.statuslabels.assets.bytype'))
        ->assertForbidden();
});
