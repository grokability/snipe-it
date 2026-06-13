<?php

namespace Tests\Feature\Consumables\Api;

use App\Models\Consumable;
use Tests\TestCase;

class ConsumableBusinessRulesTest extends TestCase
{
    public function test_num_remaining_returns_correct_value()
    {
        // Arrange
        $consumable = new Consumable();

        $consumable->qty = 10;
        $consumable->consumables_users_count = 3;

        // Act
        $result = $consumable->numRemaining();

        // Assert
        $this->assertSame(7, $result);
    }

    public function test_num_remaining_returns_zero_when_all_consumables_are_checked_out()
    {
        // Arrange
        $consumable = new Consumable();

        $consumable->qty = 10;
        $consumable->consumables_users_count = 10;

        // Act
        $result = $consumable->numRemaining();

        // Assert
        $this->assertSame(0, $result);
    }

    public function test_quantity_defaults_to_zero_when_null_is_assigned()
    {
        // Arrange
        $consumable = new Consumable();

        // Act
        $consumable->qty = null;

        // Assert
        $this->assertSame(0, $consumable->qty);
    }

    public function test_can_create_consumable_with_minimum_amount_zero()
    {
        // Arrange
        $consumable = new Consumable();

        // Act
        $consumable->min_amt = 0;

        // Assert
        $this->assertSame(0, $consumable->min_amt);
    }

    public function test_can_create_consumable_with_minimum_amount_greater_than_zero()
    {
        // Arrange
        $consumable = new Consumable();

        // Act
        $consumable->min_amt = 5;

        // Assert
        $this->assertSame(5, $consumable->min_amt);
    }
}