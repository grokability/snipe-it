<?php

namespace Tests\Unit\Models;

use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\CustomField;
use App\Models\Depreciation;
use App\Models\User;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class DepreciableAndCustomFieldTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->superuser()->create());
    }

    private function depreciableAsset(): Asset
    {
        $depreciation = Depreciation::factory()->create(['months' => 24]);
        $model = AssetModel::factory()->create(['depreciation_id' => $depreciation->id]);

        return Asset::factory()->for($model, 'model')->create([
            'purchase_cost' => 1000,
            'purchase_date' => now()->subYear()->toDateString(),
        ]);
    }

    public function test_depreciation_calculations(): void
    {
        $asset = $this->depreciableAsset();

        $this->assertIsNumeric($asset->getDepreciatedValue());
        $this->assertIsNumeric($asset->getLinearDepreciatedValue());
        $this->assertIsNumeric($asset->getMonthlyDepreciation());
        $this->assertIsNumeric($asset->depreciationProgressPercent());
    }

    public function test_half_year_depreciation_and_dates(): void
    {
        $asset = $this->depreciableAsset();

        $this->assertIsNumeric($asset->getHalfYearDepreciatedValue());
        $this->assertNotNull($asset->depreciated_date());
        $this->assertNotNull($asset->time_until_depreciated());
    }

    public static function formTypeProvider(): array
    {
        return [['checkin'], ['checkout'], ['audit'], [null]];
    }

    #[DataProvider('formTypeProvider')]
    public function test_custom_field_display_in_current_form(?string $formType): void
    {
        $field = CustomField::factory()->create();

        $this->assertIsBool((bool) $field->displayFieldInCurrentForm($formType));
    }

    public function test_custom_field_default_value_and_decryptable(): void
    {
        $field = CustomField::factory()->create();
        $model = AssetModel::factory()->create();

        $this->assertIsString((string) $field->defaultValue($model->id));
        $this->assertIsBool($field->isFieldDecryptable('some string'));
    }
}
