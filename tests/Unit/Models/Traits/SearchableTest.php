<?php

namespace Tests\Unit\Models\Traits;

use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Ejercita el trait App\Models\Traits\Searchable a traves del scope textSearch
 * sobre el modelo Asset (rico en atributos, relaciones y custom fields).
 * El objetivo es recorrer las rutas de parseo y construccion de filtros.
 */
class SearchableTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->superuser()->create());

        $model = AssetModel::factory()->create(['name' => 'MacBook Pro', 'model_number' => 'MBP-16']);
        $company = Company::factory()->create(['name' => 'Acme Corp']);

        Asset::factory()->count(3)->for($model, 'model')->create([
            'company_id' => $company->id,
            'notes' => 'office laptop',
        ]);
    }

    public static function freeTextProvider(): array
    {
        return [
            'simple term'        => ['MacBook'],
            'multiple terms'     => ['MacBook AND laptop'],
            'numeric'            => ['123'],
            'date like'          => ['2020'],
            'no match'           => ['zzz-nonexistent-zzz'],
            'empty'              => [''],
        ];
    }

    #[DataProvider('freeTextProvider')]
    public function test_free_text_search_returns_collection(string $term): void
    {
        $this->assertInstanceOf(Collection::class, Asset::textSearch($term)->get());
    }

    public static function structuredFilterProvider(): array
    {
        return [
            'attribute like'      => ['{"name":"MacBook"}'],
            'attribute exact'     => ['{"asset_tag":"is:ABC-1"}'],
            'attribute not_like'  => ['{"serial":"!XYZ"}'],
            'attribute not token' => ['{"serial":"not:XYZ"}'],
            'attribute is_null'   => ['{"notes":"is:null"}'],
            'attribute not_null'  => ['{"notes":"is:not_null"}'],
            'attribute exact_not' => ['{"name":"is_not:Foo"}'],
            'numeric attribute'   => ['{"purchase_cost":"100"}'],
            'relation company'    => ['{"company":"Acme"}'],
            'relation model'      => ['{"model":"MacBook"}'],
            'relation category'   => ['{"category":"Laptops"}'],
            'relation status'     => ['{"status":"Ready"}'],
            'with filter prefix'  => ['filter:{"name":"MacBook"}'],
            'empty value ignored' => ['{"name":""}'],
        ];
    }

    #[DataProvider('structuredFilterProvider')]
    public function test_structured_filter_search_returns_collection(string $payload): void
    {
        $this->assertInstanceOf(Collection::class, Asset::textSearch($payload)->get());
    }

    public function test_or_multiple_columns_scope(): void
    {
        $results = Asset::orWhereMultipleColumns(['assets.name', 'assets.serial'], 'MacBook')->get();
        $this->assertInstanceOf(Collection::class, $results);
    }

    public function test_structured_filters_with_or_operator(): void
    {
        request()->merge(['filter_operator' => 'or']);

        $payload = '{"name":"MacBook","serial":"XYZ"}';

        $this->assertInstanceOf(Collection::class, Asset::textSearch($payload)->get());
    }

    public function test_assigned_to_relation_filter(): void
    {
        $this->assertInstanceOf(Collection::class, Asset::textSearch('{"assigned_to":"Smith"}')->get());
    }

    public function test_custom_field_search_paths(): void
    {
        $field = \App\Models\CustomField::factory()->create(['name' => 'My Custom Field']);

        // Texto libre y filtro estructurado por el nombre del custom field.
        $this->assertInstanceOf(Collection::class, Asset::textSearch('somevalue')->get());
        $this->assertInstanceOf(Collection::class, Asset::textSearch('{"'.$field->db_column.'":"somevalue"}')->get());
    }
}
