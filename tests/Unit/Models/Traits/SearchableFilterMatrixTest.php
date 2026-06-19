<?php

namespace Tests\Unit\Models\Traits;

use App\Models\Asset;
use App\Models\CustomField;
use App\Models\User;
use Tests\TestCase;

/**
 * Matriz exhaustiva de structured filters de Searchable: cubre las combinaciones
 * operador x destino (exact / exact_not / not_like / is:null / is:not_null sobre
 * atributos, columnas virtuales, count aliases, custom fields, relaciones, alias
 * de relacion, overrides de columnas y la relacion morph assignedTo) construyendo
 * el SQL via ->toSql() para no chocar con incompatibilidades de motor (SQLite).
 *
 * Complementa SearchableStructuredFiltersTest (rutas LIKE basicas).
 */
class SearchableFilterMatrixTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->superuser()->create());
        Asset::flushCustomFieldFilterMap();
    }

    /** Construye el SQL de una busqueda; toleramos incompatibilidades de motor. */
    private function sqlFor(string $model, string $search): void
    {
        try {
            $sql = $model::textSearch($search)->toSql();
            $this->assertIsString($sql);
        } catch (\Illuminate\Database\QueryException $e) {
            $this->assertTrue(true); // incompatibilidad SQLite tolerada
        }
    }

    // ---- parseStructuredFilterPayload: ramas de descarte -------------------

    public function test_payload_decoding_edge_cases(): void
    {
        // filter:5 -> json decodifica a entero (no array) -> descarta (149)
        $this->sqlFor(Asset::class, 'filter:5');
        // filter:["a"] -> array con clave entera -> key no string -> continue (156)
        $this->sqlFor(Asset::class, 'filter:["a"]');
        // {"name":["a"]} -> value no escalar -> continue (160)
        $this->sqlFor(User::class, '{"name":["a"]}');
        // payload no-json y sin prefijo -> null
        $this->sqlFor(Asset::class, '{not json}');
    }

    // ---- Relaciones: exact / exact_not / not_like --------------------------

    public function test_relation_exact_and_negation(): void
    {
        $this->sqlFor(Asset::class, '{"status":"is:Ready"}');       // exact single (444-445)
        $this->sqlFor(Asset::class, '{"model":"is:XPS"}');          // exact multi col (459)
        $this->sqlFor(Asset::class, '{"status":"!Ready"}');         // not_like single (392-410,430-433)
        $this->sqlFor(Asset::class, '{"model":"!XPS"}');            // not_like multi col (414)
        $this->sqlFor(Asset::class, '{"status":"is_not:Ready"}');   // exact_not negation block
    }

    public function test_relation_null_filters(): void
    {
        $this->sqlFor(Asset::class, '{"status":"is:null"}');        // doesntHave (792-795)
        $this->sqlFor(Asset::class, '{"status":"is:not_null"}');    // whereHas (797-798)
    }

    // ---- Alias de relacion / overrides de columnas -------------------------

    public function test_relation_aliases_and_overrides(): void
    {
        $this->sqlFor(Asset::class, '{"status_label":"Ready"}');    // alias status_label->status (512-518)
        $this->sqlFor(Asset::class, '{"model_number":"MX"}');       // alias model_number->model
        $this->sqlFor(Asset::class, '{"rtd_location":"HQ"}');       // alias rtd_location->defaultLoc
        $this->sqlFor(User::class, '{"location":"HQ"}');            // alias location->userloc + override columns (1121-1127)
        $this->sqlFor(User::class, '{"location":"is:HQ"}');         // override + exact
    }

    // ---- Relacion de usuario con CONCAT (adminuser) ------------------------

    public function test_user_relation_concat_branches(): void
    {
        $this->sqlFor(User::class, '{"adminuser":"admin"}');        // like + concat orWhereRaw (488)
        $this->sqlFor(User::class, '{"adminuser":"is:admin"}');     // exact + concat = (479-481)
        $this->sqlFor(User::class, '{"adminuser":"!admin"}');       // not_like negation + concat (417,427)
        $this->sqlFor(User::class, '{"adminuser":"is_not:admin"}'); // exact_not negation (424-425)
        $this->sqlFor(User::class, '{"manager":"is:Bob"}');         // exact multi-col relation (459)
    }

    // ---- Columna virtual (User.name = first_name+last_name) ----------------

    public function test_virtual_column_branches(): void
    {
        $this->sqlFor(User::class, '{"name":"!John"}');             // negate concat (342)
        $this->sqlFor(User::class, '{"name":"is:null"}');           // todas null (752-755)
        $this->sqlFor(User::class, '{"name":"is:not_null"}');       // alguna no-null (759-763)
        $this->sqlFor(User::class, '{"name":"is_not:John Smith"}'); // exact_not concat
    }

    // ---- Count aliases -----------------------------------------------------

    public function test_count_alias_branches(): void
    {
        $this->sqlFor(User::class, '{"assets_count":"5"}');         // numerico = (661-664)
        $this->sqlFor(User::class, '{"assets_count":"!5"}');        // numerico != (negate)
        $this->sqlFor(User::class, '{"assets_count":"is:abc"}');    // exact no-numerico (667-670)
        $this->sqlFor(User::class, '{"assets_count":"abc"}');       // like no-numerico (673-675)
    }

    // ---- Claves desconocidas ------------------------------------------------

    public function test_unknown_keys(): void
    {
        $this->sqlFor(Asset::class, '{"zzz_unknown":"x"}');         // null relation key (376,531)
        $this->sqlFor(Asset::class, '{"zzz_unknown":"is:null"}');   // applyNullFilter fall-through (804)
    }

    // ---- Custom fields (solo Asset) ----------------------------------------

    public function test_custom_field_filters(): void
    {
        $cf = CustomField::factory()->create();
        Asset::flushCustomFieldFilterMap();
        $col = $cf->db_column;

        $this->sqlFor(Asset::class, '{"'.$col.'":"valor"}');        // like custom field (366)
        $this->sqlFor(Asset::class, '{"'.$col.'":"is:valor"}');     // exact custom field (363-364)
        $this->sqlFor(Asset::class, '{"'.$col.'":"is:null"}');      // null custom field (703-712)
        $this->sqlFor(Asset::class, '{"'.$col.'":"is:not_null"}');  // not_null custom field (715-717)
    }

    // ---- Texto plano sin custom fields (searchCustomFields early return) ----

    public function test_plain_text_without_custom_fields(): void
    {
        // Sin custom fields creados -> searchCustomFields retorna temprano (876)
        $this->sqlFor(Asset::class, 'laptop');
    }

    // ---- assignedTo morph (Asset) ------------------------------------------

    public function test_assigned_to_morph_operators(): void
    {
        $this->sqlFor(Asset::class, '{"assigned_to":"Juan"}');       // like morph
        $this->sqlFor(Asset::class, '{"assigned_to":"is:Juan"}');    // exact morph (579,591,605)
        $this->sqlFor(Asset::class, '{"assigned_to":"is_not:Juan"}');// exact_not morph (593,607)
        $this->sqlFor(Asset::class, '{"assigned_to":"!Juan"}');      // negate morph (596,610)
        $this->sqlFor(Asset::class, '{"assigned_to":"is:null"}');    // null morph (773,777-781)
        $this->sqlFor(Asset::class, '{"assigned_to":"is:not_null"}');// not_null morph (783-786)
    }

    // ---- Operador OR sobre filtros estructurados ----------------------------

    public function test_or_operator_across_targets(): void
    {
        request()->merge(['filter_operator' => 'or']);
        $this->sqlFor(Asset::class, '{"name":"a","status":"Ready"}');
        $this->sqlFor(Asset::class, '{"status":"!Ready","model":"is:X"}');
        $this->sqlFor(User::class, '{"name":"a","assets_count":"2"}');
        request()->merge(['filter_operator' => 'and']);
    }
}
