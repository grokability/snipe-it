<?php

namespace Tests\Feature\Accessories\Api;

use App\Models\Accessory;
use Tests\TestCase;

class AccessoryBusinessRulesTest extends TestCase
{
    public function test_percent_remaining_returns_zero_when_quantity_is_zero()
    {
        // Arrange
        $accessory = new Accessory();
        $accessory->qty = 0;
        $accessory->checkouts_count = 0;

        // Act
        $result = $accessory->percentRemaining();

        // Assert
        $this->assertSame(0, $result);
    }

    public function test_percent_remaining_returns_one_hundred_when_nothing_is_checked_out()
    {
        // Arrange
        $accessory = new Accessory();
        $accessory->qty = 10;
        $accessory->checkouts_count = 0;

        // Act
        $result = $accessory->percentRemaining();

        // Assert
        $this->assertSame(100, $result);
    }

    public function test_percent_remaining_calculates_percentage_correctly()
    {
        // Arrange
        $accessory = new Accessory();
        $accessory->qty = 20;
        $accessory->checkouts_count = 5;

        // Act
        $result = $accessory->percentRemaining();

        // Assert
        $this->assertEquals(75, $result);
    }

    public function test_percent_remaining_returns_zero_when_all_items_are_checked_out()
    {
        // Arrange
        $accessory = new Accessory();
        $accessory->qty = 10;
        $accessory->checkouts_count = 10;

        // Act
        $result = $accessory->percentRemaining();

        // Assert
        $this->assertEquals(0, $result);
    }
}