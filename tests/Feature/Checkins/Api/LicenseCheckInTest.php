<?php
namespace Tests\Feature\Checkins\Api;

use App\Models\License;
use App\Models\LicenseSeat;
use App\Models\User;
use Tests\TestCase;

class LicenseCheckInTest extends TestCase {
    public function testLicenseCheckin()
    {
        $authUser = User::factory()->superuser()->create();
        $this->actingAsForApi($authUser);

        $license = License::factory()->create();
        $oldUser = User::factory()->create();

        $licenseSeat = LicenseSeat::factory()->for($license)->create([
            'assigned_to' => $oldUser->id,
            'notes'       => 'Previously checked out',
        ]);

        $payload = [
            'assigned_to' => null,
            'asset_id'  => null,
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

        $this->assertNull($licenseSeat->assigned_to);
        $this->assertNull($licenseSeat->asset_id);

        $this->assertEquals('Checking in the seat', $licenseSeat->notes);
        $this->assertHasTheseActionLogs($license, ['add seats', 'create', 'checkin from']); //FIXME - bad order!
    }
}