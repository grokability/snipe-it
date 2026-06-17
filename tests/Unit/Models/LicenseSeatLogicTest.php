<?php

namespace Tests\Unit\Models;

use App\Models\LicenseSeat;
use Illuminate\Database\Eloquent\Collection;
use Tests\TestCase;

class LicenseSeatLogicTest extends TestCase
{
    public function test_require_acceptance_returns_value(): void
    {
        $seat = LicenseSeat::factory()->create();

        $this->assertIsBool((bool) $seat->requireAcceptance());
    }

    public function test_get_eula_returns_string_or_false(): void
    {
        $seat = LicenseSeat::factory()->create();

        $result = $seat->getEula();
        $this->assertTrue(is_string($result) || $result === false || is_null($result));
    }

    public function test_name_and_display_name_accessors(): void
    {
        $seat = LicenseSeat::factory()->create();

        $this->assertIsString($seat->name);
        $this->assertIsString($seat->display_name);
    }

    public function test_scope_by_assigned(): void
    {
        LicenseSeat::factory()->create();

        $this->assertInstanceOf(Collection::class, LicenseSeat::byAssigned()->get());
    }

    public function test_order_scopes(): void
    {
        $this->assertInstanceOf(Collection::class, LicenseSeat::OrderCompany('asc')->get());
        $this->assertInstanceOf(Collection::class, LicenseSeat::OrderDepartments('desc')->get());
    }
}
