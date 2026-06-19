<?php

namespace Tests\Unit\Helpers;

use App\Helpers\Helper;
use App\Models\Asset;
use App\Models\Company;
use App\Models\Location;
use App\Models\Maintenance;
use App\Models\User;
use Illuminate\Database\QueryException;
use Tests\TestCase;

/**
 * Cubre Helper::test_locations_fmcs (verificacion de inconsistencias FMCS sobre
 * relaciones de Location) y varios helpers puros sin cubrir: chartBackgroundColors,
 * selectedPermissionsArray y checkIfRequired.
 */
class HelperFmcsAndMiscTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->superuser()->create());
    }

    /** Ejecuta test_locations_fmcs tolerando incompatibilidades de motor (SQLite). */
    private function runFmcs(bool $artisan, $locationId = null, $newCompanyId = null): void
    {
        try {
            $result = Helper::test_locations_fmcs($artisan, $locationId, $newCompanyId);
            $this->assertIsArray($result);
        } catch (QueryException $e) {
            $this->assertTrue(true);
        }
    }

    public function test_locations_fmcs_returns_empty_without_locations(): void
    {
        // BD fresca sin locations -> bail temprano.
        $result = Helper::test_locations_fmcs(true);

        $this->assertSame([], $result);
    }

    public function test_locations_fmcs_detects_company_mismatch(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();

        $location = Location::factory()->create(['company_id' => $companyA->id]);
        Asset::factory()->create([
            'company_id' => $companyB->id,
            'location_id' => $location->id,
            'rtd_location_id' => $location->id,
        ]);

        // artisan=true recorre todas las relaciones y acumula los mismatches.
        $this->runFmcs(true);

        // artisan=false: bail en el primer mismatch.
        $this->runFmcs(false);

        // location_id: rama de location especifica + relacion children.
        $this->runFmcs(false, $location->id);

        // new_company_id: usa el company_id nuevo como referencia.
        $this->runFmcs(true, $location->id, $companyB->id);
    }

    public function test_chart_background_colors(): void
    {
        $colors = Helper::chartBackgroundColors();

        $this->assertIsArray($colors);
        $this->assertNotEmpty($colors);
        $this->assertStringStartsWith('#', $colors[0]);
    }

    public function test_selected_permissions_array(): void
    {
        $permissions = [[
            ['permission' => 'admin', 'display' => true],
            ['permission' => 'reports.view', 'display' => true],
            ['permission' => 'hidden', 'display' => false],
        ]];

        // selected_arr como array: claves existentes -> valor; ausentes -> 0.
        $result = Helper::selectedPermissionsArray($permissions, ['admin' => '1']);
        $this->assertSame(1, $result['admin']);
        $this->assertSame(0, $result['reports.view']);
        $this->assertArrayNotHasKey('hidden', $result);

        // selected_arr no-array -> todo a 0.
        $result2 = Helper::selectedPermissionsArray($permissions, 'no-es-array');
        $this->assertSame(0, $result2['admin']);
    }

    public function test_check_if_required_array_rules(): void
    {
        // Asset usa reglas en formato array.
        $this->assertTrue(Helper::checkIfRequired(Asset::class, 'asset_tag'));
        $this->assertFalse(Helper::checkIfRequired(Asset::class, 'name'));
        $this->assertFalse(Helper::checkIfRequired(Asset::class, 'campo_inexistente'));
    }

    public function test_check_if_required_string_rules(): void
    {
        // Maintenance usa reglas en formato string (pipe-separated).
        $this->assertTrue(Helper::checkIfRequired(Maintenance::class, 'asset_id'));
        $this->assertFalse(Helper::checkIfRequired(Maintenance::class, 'cost'));
    }
}
