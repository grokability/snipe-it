<?php

use App\Models\License;
use App\Models\LicenseSeat;
use App\Models\User;

test('license checkin', function () {
    $authUser = User::factory()->superuser()->create();
    $this->actingAsForApi($authUser);

    $license = License::factory()->create();
    $oldUser = User::factory()->create();

    $licenseSeat = LicenseSeat::factory()->for($license)->create([
        'assigned_to' => $oldUser->id,
        'notes' => 'Previously checked out',
    ]);

    $payload = [
        'assigned_to' => null,
        'asset_id' => null,
        'notes' => 'Checking in the seat',
    ];

    $response = $this->patchJson(
        route('api.licenses.seats.update', [$license->id, $licenseSeat->id]),
        $payload);

    $response->assertStatus(200)
        ->assertJsonFragment([
            'status' => 'success',
        ]);

    $licenseSeat->refresh();

    expect($licenseSeat->assigned_to)->toBeNull();
    expect($licenseSeat->asset_id)->toBeNull();

    expect($licenseSeat->notes)->toEqual('Checking in the seat');
    $this->assertHasTheseActionLogs($license, ['add seats', 'create', 'checkin from']); // FIXME - bad order!
});
