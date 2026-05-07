<?php

namespace Tests\Unit\Models;

use App\Enums\ActionType;
use App\Models\License;
use App\Models\User;
use Carbon\Carbon;
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

    public function test_percent_remaining_returns_zero_when_seats_are_zero()
    {
        $license = new class extends License
        {
            public int $remaining = 8;

            public function remaincount(): int
            {
                return $this->remaining;
            }
        };
        $license->seats = 0;

        $this->assertEquals(0, $license->percentRemaining());
    }

    public function test_percent_remaining_returns_expected_available_ratio()
    {
        $license = new class extends License
        {
            public int $remaining = 6;

            public function remaincount(): int
            {
                return $this->remaining;
            }
        };
        $license->seats = 12;

        $this->assertEquals(50.0, $license->percentRemaining());
    }

    public function test_percent_remaining_clamps_remaining_to_valid_bounds()
    {
        $license = new class extends License
        {
            public int $remaining = -3;

            public function remaincount(): int
            {
                return $this->remaining;
            }
        };
        $license->seats = 10;
        $this->assertEquals(0.0, $license->percentRemaining());

        $license->remaining = 99;
        $this->assertEquals(100.0, $license->percentRemaining());
    }

    public function test_depreciation_progress_percent_is_available_for_license(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 1, 1, 0, 0, 0));

        try {
            $license = new class extends License
            {
                public function depreciated_date()
                {
                    return date_create('2027-01-01');
                }
            };

            $license->purchase_date = '2025-01-01';

            $this->assertSame(50.0, $license->depreciationProgressPercent());
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_depreciation_progress_percent_returns_zero_when_dates_are_missing(): void
    {
        $license = new class extends License
        {
            public function depreciated_date()
            {
                return null;
            }
        };

        $license->purchase_date = null;

        $this->assertSame(0.0, $license->depreciationProgressPercent());
    }
}
