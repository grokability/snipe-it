<?php

namespace Tests\Feature\Integracion;

use App\Events\CheckoutableCheckedOut;
use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Statuslabel;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

/**
 * INT-13 — Prueba de integración: StatusLabel ↔ disponibilidad de activos.
 *
 * Verifica que el tipo de etiqueta de estado (Statuslabel) controla correctamente
 * si un activo está disponible para checkout, según la lógica de negocio del método
 * Asset::availableForCheckout() (RF-08).
 *
 * Autor: Jhastyn (aporte propio del grupo — no pertenece a la suite original de Snipe-IT).
 *
 * Técnica: caja blanca/gris (Feature Test). Se crean activos con distintos tipos
 * de StatusLabel (deployable, pending, archived, undeployable) y se verifica:
 *   1. El resultado del método availableForCheckout() a nivel de modelo.
 *   2. Que la capa de control (API y Web) respete esa disponibilidad al intentar
 *      un checkout real, rechazando activos no disponibles.
 *   3. Que no se dispare el evento CheckoutableCheckedOut cuando el checkout
 *      es rechazado (stub con Event::fake).
 *   4. Que el estado persistido del activo no cambie tras un intento rechazado.
 *
 * Nivel: Integración — ejercita la interacción entre StatusLabel (modelo),
 * Asset::availableForCheckout() (lógica de dominio), AssetCheckoutController/
 * AssetsController (controladores), y el sistema de eventos de Laravel.
 *
 * Diferencia con la suite heredada:
 * - La suite original prueba checkout exitoso con status deployable.
 * - Este test cubre explícitamente los cuatro tipos de StatusLabel y verifica
 *   que solo el tipo deployable permite checkout, los demás son rechazados.
 *
 * Casos (Plan de Pruebas de Integración §4.2):
 *  - CP-INT-13a: Status deployable → availableForCheckout() = true.
 *  - CP-INT-13b: Status pending → availableForCheckout() = false.
 *  - CP-INT-13c: Status archived → availableForCheckout() = false.
 *  - CP-INT-13d: Status undeployable → availableForCheckout() = false.
 *  - CP-INT-13e: API rechaza checkout de activo con status pending.
 *  - CP-INT-13f: API rechaza checkout de activo con status archived.
 *  - CP-INT-13g: Activo con status deployable se puede hacer checkout vía API.
 *  - CP-INT-13h: Cambio de status de deployable a archived invalida disponibilidad.
 */
class StatusLabelDisponibilidadTest extends TestCase
{
    // ===================================================================
    //  Bloque 1 — Verificación del modelo: availableForCheckout()
    // ===================================================================

    /**
     * CP-INT-13a — Un activo no asignado, no eliminado, con status deployable
     * debe estar disponible para checkout.
     */
    public function test_int_13a_activo_con_status_deployable_esta_disponible()
    {
        $status = Statuslabel::factory()->readyToDeploy()->create();
        $asset  = Asset::factory()->create(['status_id' => $status->id]);

        $this->assertTrue(
            $asset->availableForCheckout(),
            'Un activo con status deployable debe estar disponible para checkout.'
        );
    }

    /**
     * CP-INT-13b — Un activo con status pending NO debe estar disponible.
     */
    public function test_int_13b_activo_con_status_pending_no_esta_disponible()
    {
        $status = Statuslabel::factory()->pending()->create();
        $asset  = Asset::factory()->create(['status_id' => $status->id]);

        $this->assertFalse(
            $asset->availableForCheckout(),
            'Un activo con status pending no debe estar disponible para checkout.'
        );
    }

    /**
     * CP-INT-13c — Un activo con status archived NO debe estar disponible.
     */
    public function test_int_13c_activo_con_status_archived_no_esta_disponible()
    {
        $status = Statuslabel::factory()->archived()->create();
        $asset  = Asset::factory()->create(['status_id' => $status->id]);

        $this->assertFalse(
            $asset->availableForCheckout(),
            'Un activo con status archived no debe estar disponible para checkout.'
        );
    }

    /**
     * CP-INT-13d — Un activo con status undeployable (pending=0, archived=0,
     * deployable=0) NO debe estar disponible.
     */
    public function test_int_13d_activo_con_status_undeployable_no_esta_disponible()
    {
        // El factory base tiene deployable=0, pending=0, archived=0 → undeployable
        $status = Statuslabel::factory()->create();
        $asset  = Asset::factory()->create(['status_id' => $status->id]);

        $this->assertFalse(
            $asset->availableForCheckout(),
            'Un activo con status undeployable no debe estar disponible para checkout.'
        );
    }

    // ===================================================================
    //  Bloque 2 — Verificación de integración: API REST + evento + BD
    // ===================================================================

    /**
     * CP-INT-13e — La API debe rechazar el checkout de un activo con status
     * pending y NO disparar el evento de dominio.
     */
    public function test_int_13e_api_rechaza_checkout_de_activo_con_status_pending()
    {
        Event::fake([CheckoutableCheckedOut::class]);

        $status = Statuslabel::factory()->pending()->create();
        $asset  = Asset::factory()->create(['status_id' => $status->id]);
        $target = User::factory()->create();

        $this->actingAsForApi(User::factory()->checkoutAssets()->create())
            ->postJson(route('api.asset.checkout', $asset), [
                'checkout_to_type' => 'user',
                'assigned_user'    => $target->id,
            ])
            ->assertOk()
            ->assertStatusMessageIs('error');

        Event::assertNotDispatched(CheckoutableCheckedOut::class);

        $asset->refresh();
        $this->assertNull(
            $asset->assigned_to,
            'El activo con status pending no debe quedar asignado tras un intento de checkout.'
        );
    }

    /**
     * CP-INT-13f — La API debe rechazar el checkout de un activo con status
     * archived y NO disparar el evento de dominio.
     */
    public function test_int_13f_api_rechaza_checkout_de_activo_con_status_archived()
    {
        Event::fake([CheckoutableCheckedOut::class]);

        $status = Statuslabel::factory()->archived()->create();
        $asset  = Asset::factory()->create(['status_id' => $status->id]);
        $target = User::factory()->create();

        $this->actingAsForApi(User::factory()->checkoutAssets()->create())
            ->postJson(route('api.asset.checkout', $asset), [
                'checkout_to_type' => 'user',
                'assigned_user'    => $target->id,
            ])
            ->assertOk()
            ->assertStatusMessageIs('error');

        Event::assertNotDispatched(CheckoutableCheckedOut::class);

        $asset->refresh();
        $this->assertNull(
            $asset->assigned_to,
            'El activo con status archived no debe quedar asignado tras un intento de checkout.'
        );
    }

    /**
     * CP-INT-13g — La API debe permitir el checkout de un activo con status
     * deployable, disparar el evento CheckoutableCheckedOut y persistir la
     * asignación en la base de datos.
     */
    public function test_int_13g_api_permite_checkout_de_activo_con_status_deployable()
    {
        Event::fake([CheckoutableCheckedOut::class]);

        $status = Statuslabel::factory()->readyToDeploy()->create();
        $asset  = Asset::factory()->create(['status_id' => $status->id]);
        $target = User::factory()->create();

        $this->actingAsForApi(User::factory()->checkoutAssets()->create())
            ->postJson(route('api.asset.checkout', $asset), [
                'checkout_to_type' => 'user',
                'assigned_user'    => $target->id,
            ])
            ->assertOk()
            ->assertStatusMessageIs('success');

        Event::assertDispatched(CheckoutableCheckedOut::class);

        $asset->refresh();
        $this->assertEquals(
            $target->id,
            $asset->assigned_to,
            'El activo con status deployable debe quedar asignado al usuario destino.'
        );
    }

    // ===================================================================
    //  Bloque 3 — Transición de estado: cambio dinámico de disponibilidad
    // ===================================================================

    /**
     * CP-INT-13h — Si un activo tenía status deployable y se cambia a archived,
     * availableForCheckout() debe pasar de true a false. Verifica que la
     * disponibilidad se recalcula dinámicamente según la relación con StatusLabel.
     */
    public function test_int_13h_cambio_de_status_deployable_a_archived_invalida_disponibilidad()
    {
        $statusDeployable = Statuslabel::factory()->readyToDeploy()->create();
        $statusArchived   = Statuslabel::factory()->archived()->create();

        $asset = Asset::factory()->create(['status_id' => $statusDeployable->id]);

        // Pre-condición: el activo está disponible.
        $this->assertTrue(
            $asset->availableForCheckout(),
            'Pre-condición: el activo con status deployable debe estar disponible.'
        );

        // Acción: cambiar el status a archived.
        $asset->status_id = $statusArchived->id;
        $asset->save();

        // Recargar la relación para que refleje el nuevo status.
        $asset->refresh();
        $asset->load('status');

        // Post-condición: ya NO está disponible.
        $this->assertFalse(
            $asset->availableForCheckout(),
            'Tras cambiar el status a archived, el activo ya no debe estar disponible.'
        );
    }
}
