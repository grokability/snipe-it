<?php

namespace Tests\Unit\Models;

use App\Models\Accessory;
use App\Models\Asset;
use App\Models\Checkoutable;
use App\Models\CheckoutAcceptance;
use App\Models\Component;
use App\Models\Consumable;
use App\Models\License;
use App\Models\LicenseSeat;
use App\Models\User;
use Tests\TestCase;

/**
 * Cubre Checkoutable::fromAcceptance (antes 0%) para cada tipo de checkoutable.
 */
class CheckoutableFromAcceptanceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->superuser()->create());
    }

    private function acceptanceFor(string $type, int $id): CheckoutAcceptance
    {
        return CheckoutAcceptance::factory()->create([
            'checkoutable_type' => $type,
            'checkoutable_id' => $id,
            'assigned_to_id' => User::factory()->create()->id,
        ]);
    }

    public function test_from_acceptance_for_asset(): void
    {
        $acc = $this->acceptanceFor(Asset::class, Asset::factory()->create()->id);

        $result = Checkoutable::fromAcceptance($acc);

        $this->assertInstanceOf(Checkoutable::class, $result);
        $this->assertSame($acc->id, $result->acceptance_id);
    }

    public function test_from_acceptance_for_accessory(): void
    {
        $acc = $this->acceptanceFor(Accessory::class, Accessory::factory()->create()->id);
        $this->assertInstanceOf(Checkoutable::class, Checkoutable::fromAcceptance($acc));
    }

    public function test_from_acceptance_for_consumable(): void
    {
        $acc = $this->acceptanceFor(Consumable::class, Consumable::factory()->create()->id);
        $this->assertInstanceOf(Checkoutable::class, Checkoutable::fromAcceptance($acc));
    }

    public function test_from_acceptance_for_component(): void
    {
        $acc = $this->acceptanceFor(Component::class, Component::factory()->create()->id);
        $this->assertInstanceOf(Checkoutable::class, Checkoutable::fromAcceptance($acc));
    }

    public function test_from_acceptance_for_license_seat(): void
    {
        $license = License::factory()->create();
        $seat = LicenseSeat::factory()->create(['license_id' => $license->id]);
        $acc = $this->acceptanceFor(LicenseSeat::class, $seat->id);

        $this->assertInstanceOf(Checkoutable::class, Checkoutable::fromAcceptance($acc));
    }
}
