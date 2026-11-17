<?php

namespace Tests\Feature\LicenseSeats\Api;

use App\Models\License;
use App\Models\LicenseSeat;
use App\Models\User;
use Tests\TestCase;

class LicenseSeatsIndexTest extends TestCase
{
    public function test_seats_with_identical_updated_at_are_ordered_by_id()
    {
        $license = License::factory()->create(['seats' => 5]);

        LicenseSeat::where('license_id', $license->id)->update(['updated_at' => '2024-01-01 00:00:00']);

        $expectedIds = $license->licenseseats()->pluck('id')->sortDesc()->values()->all();

        $response = $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(route('api.licenses.seats.index', $license))
            ->assertOk();

        $this->assertSame($expectedIds, collect($response->json('rows'))->pluck('id')->all());
    }
}
