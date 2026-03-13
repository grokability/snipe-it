<?php

namespace Tests\Unit\Models;

use App\Enums\ActionType;
use App\Models\License;
use App\Models\User;
use Tests\TestCase;

class LicenseTest extends TestCase
{
    public function test_adding_seats_is_logged_when_updating()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $license = License::factory()->create(['seats' => 2]);

        $license->update(['seats' => 6]);

        $this->assertDatabaseHas('action_logs', [
            'created_by' => $user->id,
            'action_type' => ActionType::AddSeats,
            'item_type' => License::class,
            'item_id' => $license->id,
            'deleted_at' => null,
            // relevant for this test:
            'quantity' => 4,
            'note' => 'added 4 seats',
        ]);
    }

    public function test_removing_seats_is_logged_when_updating()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $license = License::factory()->create(['seats' => 6]);

        $license->update(['seats' => 3]);

        $this->assertDatabaseHas('action_logs', [
            'created_by' => $user->id,
            'action_type' => ActionType::DeleteSeats,
            'item_type' => License::class,
            'item_id' => $license->id,
            'deleted_at' => null,
            // relevant for this test:
            'quantity' => 3,
            'note' => 'deleted 3 seats',
        ]);
    }
}
