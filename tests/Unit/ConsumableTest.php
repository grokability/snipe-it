<?php

namespace Tests\Unit;

use App\Models\Consumable;
use Tests\TestCase;

class ConsumableTest extends TestCase
{
    public function test_percent_remaining_returns_one_hundred_when_nothing_is_checked_out()
    {
        $consumable = new Consumable([
            'qty' => 25,
        ]);
        $consumable->consumables_users_count = 0;

        $this->assertEquals(100, $consumable->percentRemaining());
    }

    public function test_percent_remaining_returns_expected_value_when_partially_checked_out()
    {
        $consumable = new Consumable([
            'qty' => 20,
        ]);
        $consumable->consumables_users_count = 5;

        $this->assertEquals(75.0, $consumable->percentRemaining());
    }

    public function test_percent_remaining_can_go_negative_when_checked_out_exceeds_quantity()
    {
        $consumable = new Consumable([
            'qty' => 3,
        ]);
        $consumable->consumables_users_count = 5;

        $this->assertEqualsWithDelta(-66.66666666666667, $consumable->percentRemaining(), 0.0000000001);
    }
}
