<?php

namespace Tests\Unit\Transformers;

use App\Http\Transformers\CustomFieldsetsTransformer;
use App\Http\Transformers\CustomFieldsTransformer;
use App\Http\Transformers\DatatablesTransformer;
use App\Http\Transformers\SelectlistTransformer;
use App\Models\Company;
use App\Models\CustomField;
use App\Models\CustomFieldset;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class SmallTransformersTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->superuser()->create());
    }

    public function test_custom_fields_transformer(): void
    {
        $fields = CustomField::factory()->count(2)->create();
        $transformer = new CustomFieldsTransformer;

        $this->assertArrayHasKey('rows', $transformer->transformCustomFields($fields, $fields->count()));
        $this->assertArrayHasKey('rows', $transformer->transformCustomFieldsWithDefaultValues($fields, 1, $fields->count()));
    }

    public function test_custom_fieldsets_transformer(): void
    {
        $fieldsets = CustomFieldset::factory()->count(2)->create();

        $this->assertArrayHasKey('rows', (new CustomFieldsetsTransformer)->transformCustomFieldsets($fieldsets, $fieldsets->count()));
    }

    public function test_datatables_transformer(): void
    {
        $companies = Company::factory()->count(3)->create();

        $result = (new DatatablesTransformer)->transformDatatables($companies, $companies->count());

        $this->assertArrayHasKey('total', $result);
        $this->assertArrayHasKey('rows', $result);
    }

    public function test_selectlist_transformer(): void
    {
        $companies = Company::factory()->count(3)->create();
        $paginator = new LengthAwarePaginator($companies, $companies->count(), 20, 1);

        $result = (new SelectlistTransformer)->transformSelectlist($paginator);

        $this->assertArrayHasKey('results', $result);
        $this->assertArrayHasKey('pagination', $result);
    }
}
