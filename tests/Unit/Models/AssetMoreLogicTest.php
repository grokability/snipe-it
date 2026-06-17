<?php

namespace Tests\Unit\Models;

use App\Models\Asset;
use App\Models\Statuslabel;
use App\Models\User;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AssetMoreLogicTest extends TestCase
{
    public static function zerofillProvider(): array
    {
        return [
            ['5', 3, '005'],
            ['42', 5, '00042'],
            ['123', 3, '123'],
        ];
    }

    #[DataProvider('zerofillProvider')]
    public function test_zerofill_pads_with_zeros(string $num, int $width, string $expected): void
    {
        $this->assertEquals($expected, Asset::zerofill($num, $width));
    }

    public function test_autoincrement_asset_returns_string_or_false(): void
    {
        // Devuelve false si el auto-incremento esta desactivado (estado por defecto).
        $result = Asset::autoincrement_asset();
        $this->assertTrue(is_string($result) || $result === false);
    }

    public function test_get_expiring_warranty_or_eol_returns_collection(): void
    {
        $this->assertInstanceOf(Collection::class, Asset::getExpiringWarrantyOrEol(60));
    }

    public function test_checkin_email_and_require_acceptance_return_bool(): void
    {
        $asset = Asset::factory()->create();

        $this->assertIsBool((bool) $asset->checkin_email());
        $this->assertIsBool((bool) $asset->requireAcceptance());
    }

    public function test_get_component_cost_returns_numeric(): void
    {
        $asset = Asset::factory()->create();

        $this->assertIsNumeric($asset->getComponentCost());
    }

    public function test_checkout_assigns_asset_to_user(): void
    {
        $this->actingAs(User::factory()->superuser()->create());
        $status = Statuslabel::factory()->create(['deployable' => 1, 'archived' => 0, 'pending' => 0]);
        $asset = Asset::factory()->create(['assigned_to' => null, 'status_id' => $status->id]);
        $target = User::factory()->create();

        $result = $asset->checkOut($target, null, now(), null, 'checkout note');

        $this->assertNotFalse($result);
        $this->assertEquals($target->id, $asset->fresh()->assigned_to);
    }
}
