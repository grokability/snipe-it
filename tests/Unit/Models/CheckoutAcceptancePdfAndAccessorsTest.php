<?php

namespace Tests\Unit\Models;

use App\Models\Accessory;
use App\Models\Asset;
use App\Models\CheckoutAcceptance;
use App\Models\Component;
use App\Models\Consumable;
use App\Models\LicenseSeat;
use App\Models\User;
use Tests\TestCase;

/**
 * Cubre las partes densas no testeadas de CheckoutAcceptance:
 * generateAcceptancePdf (TCPDF), accesores y scopes/route helpers.
 */
class CheckoutAcceptancePdfAndAccessorsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->superuser()->create());
    }

    public function test_generate_acceptance_pdf_returns_string(): void
    {
        $acceptance = CheckoutAcceptance::factory()->create();

        $data = [
            'assigned_to' => 'Juan Perez',
            'item_tag' => 'A-1001',
            'logo' => null,
            'site_name' => 'Mi Empresa',
            'company_name' => 'Acme',
            'item_name' => 'Laptop',
            'item_model' => 'XPS 15',
            'item_serial' => 'SN-123',
            'custom_fields' => [
                ['label' => 'Color', 'value' => 'Negro'],
                ['label' => 'Vacio', 'value' => ''],
            ],
            'qty' => 2,
            'employee_num' => 'E-99',
            'email' => 'juan@example.com',
            'eula' => "Linea 1 de EULA\nLinea 2 de EULA",
            'signature' => null,
            'note' => 'Nota de prueba',
            'check_out_date' => '2026-01-01',
            'accepted_date' => '2026-01-02',
        ];

        $pdf = $acceptance->generateAcceptancePdf($data, 'acceptance.pdf');

        $this->assertIsString($pdf);
        $this->assertNotEmpty($pdf);
        $this->assertStringStartsWith('%PDF', $pdf);
    }

    public function test_generate_acceptance_pdf_with_null_optionals(): void
    {
        $acceptance = CheckoutAcceptance::factory()->create();

        // Rama con muchos campos en null y qty=1 (no imprime qty).
        $data = [
            'assigned_to' => 'Ana',
            'item_tag' => null,
            'logo' => null,
            'site_name' => 'Empresa',
            'company_name' => null,
            'item_name' => null,
            'item_model' => null,
            'item_serial' => null,
            'custom_fields' => null,
            'qty' => 1,
            'employee_num' => null,
            'email' => null,
            'eula' => 'EULA simple',
            'signature' => null,
            'note' => null,
            'check_out_date' => '2026-01-01',
            'accepted_date' => '2026-01-02',
        ];

        $pdf = $acceptance->generateAcceptancePdf($data, 'acceptance2.pdf');

        $this->assertStringStartsWith('%PDF', $pdf);
    }

    public function test_route_notification_for_mail_parses_alert_email(): void
    {
        $this->settings->set(['alert_email' => 'a@example.com, b@example.com']);
        $acceptance = CheckoutAcceptance::factory()->create();

        $recipients = $acceptance->routeNotificationForMail();

        $this->assertContains('a@example.com', $recipients);
        $this->assertContains('b@example.com', $recipients);
    }

    public function test_checkoutable_category_name_for_asset(): void
    {
        $asset = Asset::factory()->create();
        $acceptance = CheckoutAcceptance::factory()->create([
            'checkoutable_type' => Asset::class,
            'checkoutable_id' => $asset->id,
        ]);

        // El accesor devuelve el nombre de categoria del modelo del asset (o null).
        $name = $acceptance->checkoutable_category_name;
        $this->assertTrue($name === null || is_string($name));
    }

    public function test_checkoutable_category_name_for_license_seat(): void
    {
        $seat = LicenseSeat::factory()->create();
        $acceptance = CheckoutAcceptance::factory()->create([
            'checkoutable_type' => LicenseSeat::class,
            'checkoutable_id' => $seat->id,
        ]);

        $name = $acceptance->checkoutable_category_name;
        $this->assertTrue($name === null || is_string($name));
    }

    public function test_checkoutable_category_name_for_accessory(): void
    {
        $accessory = Accessory::factory()->create();
        $acceptance = CheckoutAcceptance::factory()->create([
            'checkoutable_type' => Accessory::class,
            'checkoutable_id' => $accessory->id,
        ]);

        $name = $acceptance->checkoutable_category_name;
        $this->assertTrue($name === null || is_string($name));
    }

    public function test_display_checkoutable_type_accessor(): void
    {
        $acceptance = CheckoutAcceptance::factory()->create([
            'checkoutable_type' => Asset::class,
        ]);

        $this->assertSame('asset', $acceptance->display_checkoutable_type);
    }

    public function test_item_type_attribute_for_various_types(): void
    {
        $cases = [
            [LicenseSeat::class, LicenseSeat::factory()],
            [Component::class, Component::factory()],
            [Consumable::class, Consumable::factory()],
        ];

        foreach ($cases as [$type, $factory]) {
            $acceptance = CheckoutAcceptance::factory()->make([
                'checkoutable_type' => $type,
            ]);
            $this->assertIsString($acceptance->checkoutable_item_type);
        }
    }

    public function test_scope_has_files(): void
    {
        CheckoutAcceptance::factory()->create(['signature_filename' => 'sig.png']);
        CheckoutAcceptance::factory()->create(['signature_filename' => null]);

        $withFiles = CheckoutAcceptance::hasFiles()->get();

        $this->assertGreaterThanOrEqual(1, $withFiles->count());
    }
}
