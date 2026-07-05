<?php

namespace Tests\Feature\Integracion;

use App\Events\CheckoutableCheckedOut;
use App\Models\Asset;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

/**
 * Sprint 3 — Pruebas de Integración (ISO/IEC/IEEE 29119).
 * Inyección de fallas de interfaz sobre la capa de control del checkout de activos.
 *
 * Autor: Jeanpiero (aporte propio del grupo — no pertenece a la suite original de Snipe-IT).
 *
 * Técnica: caja blanca/gris. Se inyecta una única falla por caso (single-fault injection)
 * en una petición por lo demás válida, y se verifica en tres niveles:
 *   1. Respuesta de la interfaz (errores de validación en sesión / respuesta API).
 *   2. Que NO se dispare el evento de dominio CheckoutableCheckedOut (stub con Event::fake).
 *   3. Que el estado persistido del activo no haya cambiado (caja gris: se inspecciona la BD).
 *
 * Diferencia con la suite heredada (tests/Feature/Checkouts/Ui/AssetCheckoutTest.php,
 * método test_validation_when_checking_out_asset): aquella envía un payload globalmente
 * inválido y solo verifica los mensajes de error; estos casos aíslan UNA falla por vez con
 * el resto del payload válido y además verifican el estado interno del sistema.
 *
 * Casos (Plan de Pruebas de Integración §4.2):
 *  - FI-01 (falla sintáctica): checkout sin destino / status_id no numérico.
 *  - FI-02 (falla semántica): expected_checkin cronológicamente anterior a checkout_at.
 *
 * Incidente asociado: INC-02 — la versión original de AssetCheckoutRequest no comparaba
 * expected_checkin contra checkout_at (regla solo 'nullable|date'), por lo que el sistema
 * aceptaba fechas de devolución anteriores a la entrega. Se corrigió agregando la regla
 * condicional after_or_equal:checkout_at en app/Http/Requests/AssetCheckoutRequest.php.
 */
class AssetCheckoutInterfaceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Event::fake([CheckoutableCheckedOut::class]);
    }

    /**
     * FI-01a — Falla sintáctica: la petición declara checkout_to_type=user pero
     * omite por completo el destino (assigned_user).
     * Esperado: rechazo por validación y ningún cambio de estado.
     */
    public function test_fi_01_sintactica_checkout_sin_destino_es_rechazado()
    {
        $asset = Asset::factory()->create();

        $this->actingAs(User::factory()->checkoutAssets()->create())
            ->post(route('hardware.checkout.store', $asset), [
                'checkout_to_type' => 'user',
                // Falla inyectada: se omite assigned_user (el destino del checkout).
            ])
            ->assertSessionHasErrors(['assigned_user']);

        Event::assertNotDispatched(CheckoutableCheckedOut::class);

        $asset->refresh();
        $this->assertNull($asset->assigned_to, 'El activo no debe quedar asignado si falta el destino.');
        $this->assertNull($asset->last_checkout, 'No debe registrarse fecha de checkout.');
    }

    /**
     * FI-01b — Falla sintáctica: el payload es válido salvo por un status_id
     * no numérico ("no-numerico-<script>"), simulando una petición manipulada.
     * Esperado: rechazo por validación, sin asignación y sin cambio de estado del activo.
     */
    public function test_fi_01_sintactica_status_id_no_numerico_es_rechazado()
    {
        $asset = Asset::factory()->create();
        $target = User::factory()->create();
        $statusOriginal = $asset->status_id;

        $this->actingAs(User::factory()->checkoutAssets()->create())
            ->post(route('hardware.checkout.store', $asset), [
                'checkout_to_type' => 'user',
                'assigned_user' => $target->id,
                // Falla inyectada: status_id no numérico (petición malintencionada).
                'status_id' => 'no-numerico-<script>',
            ])
            ->assertSessionHasErrors(['status_id']);

        Event::assertNotDispatched(CheckoutableCheckedOut::class);

        $asset->refresh();
        $this->assertNull($asset->assigned_to, 'El activo no debe quedar asignado con un status_id inválido.');
        $this->assertEquals($statusOriginal, $asset->status_id, 'El estado del activo no debe cambiar.');
    }

    /**
     * FI-02 (UI) — Falla semántica: ambas fechas son sintácticamente válidas,
     * pero expected_checkin (devolución esperada) es anterior a checkout_at (entrega).
     * Esperado: rechazo por validación y ningún cambio de estado.
     */
    public function test_fi_02_semantica_expected_checkin_anterior_a_checkout_es_rechazado()
    {
        $asset = Asset::factory()->create();
        $target = User::factory()->create();

        $this->actingAs(User::factory()->checkoutAssets()->create())
            ->post(route('hardware.checkout.store', $asset), [
                'checkout_to_type' => 'user',
                'assigned_user' => $target->id,
                'checkout_at' => '2026-07-10',
                // Falla inyectada: devolución esperada ANTES de la entrega.
                'expected_checkin' => '2026-07-01',
            ])
            ->assertSessionHasErrors(['expected_checkin']);

        Event::assertNotDispatched(CheckoutableCheckedOut::class);

        $asset->refresh();
        $this->assertNull($asset->assigned_to, 'El activo no debe quedar asignado con fechas incoherentes.');
        $this->assertNull($asset->expected_checkin, 'No debe persistirse una fecha de devolución incoherente.');
    }

    /**
     * FI-02 (API) — La misma falla semántica inyectada por la capa de control REST
     * (api.asset.checkout usa el mismo FormRequest), verificando que la regla
     * protege ambas interfaces.
     * Esperado: respuesta con estado "error" y ningún cambio de estado.
     */
    public function test_fi_02_semantica_api_rechaza_expected_checkin_anterior_a_checkout()
    {
        $asset = Asset::factory()->create();
        $target = User::factory()->create();

        $this->actingAsForApi(User::factory()->checkoutAssets()->create())
            ->postJson(route('api.asset.checkout', $asset), [
                'checkout_to_type' => 'user',
                'assigned_user' => $target->id,
                'checkout_at' => '2026-07-10',
                // Falla inyectada: devolución esperada ANTES de la entrega.
                'expected_checkin' => '2026-07-01',
            ])
            ->assertStatusMessageIs('error');

        Event::assertNotDispatched(CheckoutableCheckedOut::class);

        $asset->refresh();
        $this->assertNull($asset->assigned_to, 'La API no debe asignar el activo con fechas incoherentes.');
    }
}
