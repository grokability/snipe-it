<?php

namespace Tests\Unit\Models;

use App\Models\Asset;
use App\Models\License;
use Illuminate\Database\Eloquent\Collection;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Cubre mutadores y metodos de la clase base App\Models\SnipeModel no cubiertos
 * por el SnipeModelTest existente.
 */
class SnipeModelMoreTest extends TestCase
{
    public static function nullableIdMutatorProvider(): array
    {
        return [
            ['min_amt'],
            ['parent_id'],
            ['fieldset_id'],
            ['company_id'],
            ['warranty_months'],
            ['rtd_location_id'],
            ['department_id'],
            ['manager_id'],
            ['model_id'],
            ['status_id'],
        ];
    }

    #[DataProvider('nullableIdMutatorProvider')]
    public function test_empty_string_is_normalized_to_null(string $attribute): void
    {
        $asset = new Asset;
        $asset->{$attribute} = '';
        $this->assertNull($asset->getAttributes()[$attribute]);

        $asset->{$attribute} = 5;
        $this->assertEquals(5, $asset->getAttributes()[$attribute]);
    }

    public function test_apply_offset_and_limit_scope(): void
    {
        Asset::factory()->count(3)->create();

        $this->assertInstanceOf(Collection::class, Asset::applyOffsetAndLimit(2)->get());
    }

    public function test_get_eula_returns_string_or_null(): void
    {
        $license = License::factory()->create();

        $result = $license->getEula();
        $this->assertTrue(is_string($result) || is_null($result));
    }

    public function test_expires_accessors_are_accessible(): void
    {
        $license = License::factory()->create(['expiration_date' => '2030-01-01']);

        $this->assertNotNull($license->expires_formatted_date);
        $this->assertNotNull($license->expires_diff_in_days);
    }
}
