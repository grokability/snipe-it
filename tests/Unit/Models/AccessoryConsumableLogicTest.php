<?php

namespace Tests\Unit\Models;

use App\Models\Accessory;
use App\Models\Consumable;
use App\Models\User;
use Tests\TestCase;

class AccessoryConsumableLogicTest extends TestCase
{
    public function test_accessory_counts_and_percent(): void
    {
        $accessory = Accessory::factory()->create(['qty' => 10]);

        $this->assertSame(0, $accessory->numCheckedOut());
        $this->assertSame(10, $accessory->numRemaining());
        $this->assertEquals(100, $accessory->percentRemaining());
    }

    public function test_accessory_has_users_false_when_none(): void
    {
        $accessory = Accessory::factory()->create();

        $this->assertFalse((bool) $accessory->hasUsers());
    }

    public function test_accessory_require_acceptance_and_checkin_email_return_bool(): void
    {
        $accessory = Accessory::factory()->create();

        $this->assertIsBool((bool) $accessory->requireAcceptance());
        $this->assertIsBool((bool) $accessory->checkin_email());
    }

    public function test_accessory_is_deletable(): void
    {
        $this->actingAs(User::factory()->superuser()->create());
        $accessory = Accessory::factory()->create();

        $this->assertIsBool($accessory->isDeletable());
    }

    public function test_accessory_set_requestable_attribute(): void
    {
        $accessory = Accessory::factory()->make();
        $accessory->requestable = '1';

        $this->assertEquals(1, $accessory->getAttributes()['requestable']);
    }

    public function test_consumable_counts_and_percent(): void
    {
        $consumable = Consumable::factory()->create(['qty' => 8]);

        $this->assertSame(0, $consumable->numCheckedOut());
        $this->assertSame(8, $consumable->numRemaining());
        $this->assertEquals(100, $consumable->percentRemaining());
    }

    public function test_consumable_total_cost_sum(): void
    {
        $consumable = Consumable::factory()->create(['qty' => 5, 'purchase_cost' => 2.50]);

        $this->assertEquals(12.5, $consumable->totalCostSum());
    }

    public function test_consumable_is_deletable_and_flags(): void
    {
        $this->actingAs(User::factory()->superuser()->create());
        $consumable = Consumable::factory()->create();

        $this->assertIsBool($consumable->isDeletable());
        $this->assertIsBool((bool) $consumable->requireAcceptance());
        $this->assertIsBool((bool) $consumable->checkin_email());
    }
}
