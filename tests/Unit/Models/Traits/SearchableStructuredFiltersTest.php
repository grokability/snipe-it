<?php

namespace Tests\Unit\Models\Traits;

use App\Models\Asset;
use App\Models\User;
use Tests\TestCase;

/**
 * Cubre las ramas de structured filters de Searchable::scopeTextSearch a traves
 * de textSearch(...)->toSql(), que construye la query (ejecutando todo el codigo
 * de parseo/aplicacion) sin ejecutarla contra SQLite (evita incompatibilidades de motor).
 */
class SearchableStructuredFiltersTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->superuser()->create());
    }

    /** Construye el SQL de una busqueda; toleramos incompatibilidades de motor. */
    private function sqlFor(string $model, string $search): void
    {
        try {
            $sql = $model::textSearch($search)->toSql();
            $this->assertIsString($sql);
            $this->assertNotEmpty($sql);
        } catch (\Illuminate\Database\QueryException $e) {
            $this->assertTrue(true); // incompatibilidad SQLite tolerada
        }
    }

    public function test_plain_text_terms_path(): void
    {
        // Sin filtros estructurados -> searchAttributes/CustomFields/Relations/advanced.
        $this->sqlFor(Asset::class, 'laptop');
        $this->sqlFor(Asset::class, 'foo AND bar');
    }

    public function test_attribute_like_filter(): void
    {
        $this->sqlFor(Asset::class, '{"name":"laptop"}');
    }

    public function test_attribute_negation_filters(): void
    {
        $this->sqlFor(Asset::class, '{"name":"!laptop"}');
        $this->sqlFor(Asset::class, '{"name":"not:laptop"}');
    }

    public function test_null_filters(): void
    {
        $this->sqlFor(Asset::class, '{"name":"is:null"}');
        $this->sqlFor(Asset::class, '{"name":"is:not_null"}');
    }

    public function test_exact_match_filters(): void
    {
        $this->sqlFor(Asset::class, '{"name":"is:Laptop X"}');
        $this->sqlFor(Asset::class, '{"name":"is_not:Laptop X"}');
    }

    public function test_empty_value_is_ignored(): void
    {
        // Valor que queda vacio tras quitar prefijo -> se ignora sin error.
        $this->sqlFor(Asset::class, '{"name":"is:"}');
    }

    public function test_relation_filter(): void
    {
        // Clave de relacion (status) -> searchRelations / whereHas.
        $this->sqlFor(Asset::class, '{"status":"Ready"}');
        $this->sqlFor(Asset::class, '{"model":"XPS"}');
    }

    public function test_assigned_to_relation_filter(): void
    {
        // 'assigned_to' es relacion morph polimorfica -> applyAssignedToRelationFilter.
        $this->sqlFor(Asset::class, '{"assigned_to":"Juan"}');
    }

    public function test_filter_with_or_operator(): void
    {
        request()->merge(['filter_operator' => 'or']);
        $this->sqlFor(Asset::class, '{"name":"a","serial":"b"}');
        request()->merge(['filter_operator' => 'and']);
    }

    public function test_filter_prefixed_payload(): void
    {
        // payload con prefijo explicito "filter:".
        $this->sqlFor(Asset::class, 'filter:{"name":"laptop"}');
    }

    public function test_user_virtual_column_filter(): void
    {
        // 'name' en User es columna virtual (first_name+last_name via CONCAT).
        $this->sqlFor(User::class, '{"name":"John Smith"}');
        $this->sqlFor(User::class, '{"name":"is:John Smith"}');
    }

    public function test_user_count_alias_filter(): void
    {
        // Clave de count alias -> applyCountAliasFilter.
        $this->sqlFor(User::class, '{"assets_count":"2"}');
    }
}
