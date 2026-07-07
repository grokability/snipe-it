<?php

namespace Tests\Feature\Integracion;

use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\CustomField;
use App\Models\CustomFieldset;
use App\Models\Statuslabel;
use App\Models\User;
use Tests\TestCase;

/**
 * INT-11 — Prueba de integración: CustomFields ↔ Asset.
 *
 * Verifica que la cadena de integración  CustomField → CustomFieldset → AssetModel → Asset
 * aplique correctamente las reglas de validación dinámicas definidas por los campos
 * personalizados al crear un activo a través de la API REST.
 *
 * Autor: Jhastyn (aporte propio del grupo — no pertenece a la suite original de Snipe-IT).
 *
 * Técnica: caja blanca/gris (Feature Test). Se crean campos personalizados con formatos
 * específicos (NUMERIC, EMAIL) mediante factories, se asocian a un fieldset, se vincula
 * el fieldset a un AssetModel, y se intenta crear un activo vía API con valores válidos
 * e inválidos para cada campo personalizado.
 *
 * Nivel: Integración — ejercita la interacción entre 4 capas: modelo de dominio
 * (CustomField/CustomFieldset), validación dinámica (CustomFieldset::validation_rules()),
 * controlador API (AssetsController::store), y persistencia (tabla assets con columnas
 * dinámicas _snipeit_*).
 *
 * Diferencia con la suite heredada:
 * - tests/Feature/Assets/Api/StoreAssetTest::test_encrypted_custom_field_validation_*
 *   se concentra en campos ENCRIPTADOS con formatos alpha/numeric/email.
 * - Este test cubre campos NO encriptados con formatos NUMERIC y EMAIL, verifica la
 *   persistencia del valor en la columna dinámica, y valida el rechazo por formato
 *   inválido, todo dentro de un solo flujo de integración completo.
 *
 * Casos (Plan de Pruebas de Integración §4.2):
 *  - CP-INT-11a: Crear activo con campo numérico válido → se persiste.
 *  - CP-INT-11b: Crear activo con campo numérico inválido (texto) → rechazo.
 *  - CP-INT-11c: Crear activo con campo email válido → se persiste.
 *  - CP-INT-11d: Crear activo con campo email inválido (sin @) → rechazo.
 *  - CP-INT-11e: Crear activo con campo requerido omitido → rechazo.
 */
class CustomFieldAssetTest extends TestCase
{
    /**
     * Crea la infraestructura de integración: CustomField → Fieldset → AssetModel.
     *
     * NOTA: Los tests de custom fields NO funcionan sobre MySQL/MariaDB en el
     * runner Docker porque la creación dinámica de columnas ALTER TABLE choca con
     * la transacción de RefreshDatabase. Se usa markIncompleteIfMySQL() como hace
     * la suite oficial (StoreAssetTest L763).
     *
     * @return array{field: CustomField, model: AssetModel, status: Statuslabel}
     */
    private function crearCampoYModelo(string $factoryState, bool $requerido = false): array
    {
        $field = CustomField::factory()->{$factoryState}()->create();

        $fieldset = CustomFieldset::factory()->create();
        $fieldset->fields()->attach($field, [
            'order'    => 1,
            'required' => $requerido ? '1' : '0',
        ]);

        $model = AssetModel::factory()->create(['fieldset_id' => $fieldset->id]);
        $status = Statuslabel::factory()->readyToDeploy()->create();

        return compact('field', 'model', 'status');
    }

    // -----------------------------------------------------------------------
    //  CP-INT-11a — Campo numérico válido: creación exitosa + persistencia
    // -----------------------------------------------------------------------

    /**
     * Un activo creado con un campo personalizado de formato NUMERIC y un valor
     * numérico válido debe persistirse correctamente; el valor debe quedar guardado
     * en la columna dinámica _snipeit_* de la tabla assets.
     */
    public function test_int_11a_campo_numerico_valido_se_persiste_al_crear_activo()
    {
        $this->markIncompleteIfMySQL('Custom Fields tests do not work on MySQL');

        ['field' => $field, 'model' => $model, 'status' => $status] = $this->crearCampoYModelo('numeric');

        $response = $this->actingAsForApi(User::factory()->superuser()->create())
            ->postJson(route('api.assets.store'), [
                'asset_tag'                => 'INT11A-001',
                'model_id'                 => $model->id,
                'status_id'                => $status->id,
                $field->db_column_name()   => '42',
            ])
            ->assertOk()
            ->assertStatusMessageIs('success')
            ->json();

        $asset = Asset::findOrFail($response['payload']['id']);

        $this->assertEquals(
            '42',
            $asset->{$field->db_column_name()},
            'El valor numérico debe persistirse en la columna dinámica del campo personalizado.'
        );
    }

    // -----------------------------------------------------------------------
    //  CP-INT-11b — Campo numérico inválido: rechazo por validación dinámica
    // -----------------------------------------------------------------------

    /**
     * Si se envía un valor no numérico (texto libre) en un campo con formato NUMERIC,
     * la validación dinámica generada por CustomFieldset::validation_rules() debe
     * rechazar la petición; el activo NO debe crearse.
     */
    public function test_int_11b_campo_numerico_invalido_es_rechazado()
    {
        $this->markIncompleteIfMySQL('Custom Fields tests do not work on MySQL');

        ['field' => $field, 'model' => $model, 'status' => $status] = $this->crearCampoYModelo('numeric');

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->postJson(route('api.assets.store'), [
                'asset_tag'                => 'INT11B-001',
                'model_id'                 => $model->id,
                'status_id'                => $status->id,
                $field->db_column_name()   => 'no-es-un-numero',
            ])
            ->assertOk()
            ->assertStatusMessageIs('error');

        $this->assertDatabaseMissing('assets', [
            'asset_tag' => 'INT11B-001',
        ]);
    }

    // -----------------------------------------------------------------------
    //  CP-INT-11c — Campo email válido: creación exitosa + persistencia
    // -----------------------------------------------------------------------

    /**
     * Un campo personalizado con formato EMAIL debe aceptar una dirección válida
     * y persistirla en la columna dinámica correspondiente.
     */
    public function test_int_11c_campo_email_valido_se_persiste_al_crear_activo()
    {
        $this->markIncompleteIfMySQL('Custom Fields tests do not work on MySQL');

        ['field' => $field, 'model' => $model, 'status' => $status] = $this->crearCampoYModelo('email');

        $response = $this->actingAsForApi(User::factory()->superuser()->create())
            ->postJson(route('api.assets.store'), [
                'asset_tag'                => 'INT11C-001',
                'model_id'                 => $model->id,
                'status_id'                => $status->id,
                $field->db_column_name()   => 'admin@snipeit.test',
            ])
            ->assertOk()
            ->assertStatusMessageIs('success')
            ->json();

        $asset = Asset::findOrFail($response['payload']['id']);

        $this->assertEquals(
            'admin@snipeit.test',
            $asset->{$field->db_column_name()},
            'El valor email debe persistirse en la columna dinámica del campo personalizado.'
        );
    }

    // -----------------------------------------------------------------------
    //  CP-INT-11d — Campo email inválido: rechazo por validación dinámica
    // -----------------------------------------------------------------------

    /**
     * Un valor que no cumple el formato email (sin arroba) debe ser rechazado por
     * la validación dinámica; el activo NO debe crearse.
     */
    public function test_int_11d_campo_email_invalido_es_rechazado()
    {
        $this->markIncompleteIfMySQL('Custom Fields tests do not work on MySQL');

        ['field' => $field, 'model' => $model, 'status' => $status] = $this->crearCampoYModelo('email');

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->postJson(route('api.assets.store'), [
                'asset_tag'                => 'INT11D-001',
                'model_id'                 => $model->id,
                'status_id'                => $status->id,
                $field->db_column_name()   => 'esto-no-es-email',
            ])
            ->assertOk()
            ->assertStatusMessageIs('error');

        $this->assertDatabaseMissing('assets', [
            'asset_tag' => 'INT11D-001',
        ]);
    }

    // -----------------------------------------------------------------------
    //  CP-INT-11e — Campo requerido omitido: rechazo por validación dinámica
    // -----------------------------------------------------------------------

    /**
     * Si un campo personalizado está marcado como "required" en el pivot del fieldset
     * y se omite en la petición, la validación dinámica debe rechazar la creación
     * del activo.
     */
    public function test_int_11e_campo_requerido_omitido_es_rechazado()
    {
        $this->markIncompleteIfMySQL('Custom Fields tests do not work on MySQL');

        ['field' => $field, 'model' => $model, 'status' => $status] = $this->crearCampoYModelo('numeric', requerido: true);

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->postJson(route('api.assets.store'), [
                'asset_tag'  => 'INT11E-001',
                'model_id'   => $model->id,
                'status_id'  => $status->id,
                // Se omite intencionalmente el campo personalizado requerido.
            ])
            ->assertOk()
            ->assertStatusMessageIs('error');

        $this->assertDatabaseMissing('assets', [
            'asset_tag' => 'INT11E-001',
        ]);
    }
}
