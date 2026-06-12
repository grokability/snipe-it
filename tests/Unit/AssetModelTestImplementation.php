<?php

namespace Tests\Unit;

use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Category;
use App\Models\Manufacturer;
use App\Models\Depreciation;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

/**
 * Implementación de pruebas para AssetModel.
 * Justificación: Plan de Pruebas Unitarias v2.0 - Sección 5.2
 */
class AssetModelTestImplementation extends TestCase
{
    use DatabaseTransactions;

    /**
     * TC-ASM-01: Validaciones Requeridas
     * Referencia Wiki: Sección 10.2 (Validación automática con watson/validating)
     * Verifica que las reglas de $rules impidan guardar modelos incompletos.
     */
    public function test_asset_model_required_validations()
    {
        $model = new AssetModel();
        $this->assertFalse($model->isValid());
        $this->assertArrayHasKey('name', $model->getErrors()->toArray());
        $this->assertArrayHasKey('category_id', $model->getErrors()->toArray());
    }

    /**
     * TC-ASM-02: Mass Assignment
     * Referencia Wiki: Sección 2.2 (Integridad de registros y campos obligatorios)
     */
    public function test_asset_model_mass_assignment()
    {
        $attributes = [
            'name' => 'Dell XPS 15',
            'model_number' => 'XPS15-9500',
            'eol' => 36,
            'notes' => 'High-end laptop',
        ];
        $model = new AssetModel($attributes);
        foreach ($attributes as $key => $value) {
            $this->assertEquals($value, $model->$key);
        }
    }

    /**
     * TC-ASM-03: Relationships (Category, Manufacturer, Depreciation)
     * Referencia Wiki: Sección 2.2 (Relaciones con categorías y fabricantes)
     */
    public function test_asset_model_core_relationships()
    {
        $category = Category::factory()->create(['category_type' => 'asset']);
        $manufacturer = Manufacturer::factory()->create();
        $depreciation = Depreciation::factory()->create();

        $model = AssetModel::factory()->create([
            'category_id' => $category->id,
            'manufacturer_id' => $manufacturer->id,
            'depreciation_id' => $depreciation->id,
        ]);

        $this->assertInstanceOf(Category::class, $model->category);
        $this->assertInstanceOf(Manufacturer::class, $model->manufacturer);
        $this->assertInstanceOf(Depreciation::class, $model->depreciation);
    }

    /**
     * TC-ASM-04: percentRemaining() logic
     * Referencia Wiki: ID AM-007 / AM-008 (Cálculo de disponibilidad de activos)
     */
    public function test_asset_model_percent_remaining_calculation()
    {
        $category = Category::factory()->create(['category_type' => 'asset']);
        $model = AssetModel::factory()->create(['category_id' => $category->id]);
        
        Asset::factory()->count(2)->create(['model_id' => $model->id, 'status_id' => 1]); 
        Asset::factory()->count(2)->create(['model_id' => $model->id, 'assigned_to' => 1, 'status_id' => 2]); 

        $this->assertEquals(50.0, $model->percentRemaining());
    }

    /**
     * TC-ASM-05: isDeletable()
     * Referencia Wiki: ID AM-009 / AM-010 (Restricción de borrado con activos vinculados)
     */
    public function test_asset_model_is_deletable_constraints()
    {
        $category = Category::factory()->create(['category_type' => 'asset']);
        $model = AssetModel::factory()->create(['category_id' => $category->id]);

        $this->assertTrue($model->isDeletable());

        Asset::factory()->create(['model_id' => $model->id]);
        $this->assertFalse($model->isDeletable());
    }

    /**
     * TC-ASM-06: Scopes (InCategory, RequestableModels)
     * Referencia Wiki: ID AM-011 / AM-012 (Filtros de búsqueda y modelos solicitables)
     */
    public function test_asset_model_scopes()
    {
        $category = Category::factory()->create(['category_type' => 'asset']);
        AssetModel::factory()->count(3)->create(['category_id' => $category->id, 'requestable' => 1]);
        
        $this->assertEquals(3, AssetModel::inCategory([$category->id])->count());
        $this->assertEquals(3, AssetModel::requestableModels()->count());
    }

    /**
     * TC-ASM-07: Casting
     * Referencia Wiki: Auditoría de Repositorio (Manejo de tipos de datos en modelos)
     */
    public function test_asset_model_casting()
    {
        $model = AssetModel::factory()->make();
        $this->assertIsInt($model->category_id);
    }

    /**
     * TC-ASM-08: Soft Delete lifecycle
     * Referencia Wiki: ID AM-005 / AM-006 (Cascada de eliminación lógica de requests)
     */
    public function test_asset_model_soft_delete_lifecycle()
    {
        $category = Category::factory()->create(['category_type' => 'asset']);
        $model = AssetModel::factory()->create(['category_id' => $category->id]);
        
        $model->delete();
        $this->assertSoftDeleted('models', ['id' => $model->id]);
    }

    /**
     * TC-ASM-09: Presenter
     * Referencia Wiki: Sección 1.1 (Cobertura de Presenters para visualización)
     */
    public function test_asset_model_presenter_link()
    {
        $model = AssetModel::factory()->make();
        $this->assertInstanceOf(\App\Presenters\AssetModelPresenter::class, $model->present());
    }
}
