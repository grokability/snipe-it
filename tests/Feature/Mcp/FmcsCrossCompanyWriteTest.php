<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\CreateAssetTool;
use App\Mcp\Tools\CreateConsumableTool;
use App\Mcp\Tools\CreateLocationTool;
use App\Mcp\Tools\UpdateAssetTool;
use App\Mcp\Tools\UpdateConsumableTool;
use App\Mcp\Tools\UpdateLocationTool;
use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Category;
use App\Models\Company;
use App\Models\Consumable;
use App\Models\Location;
use App\Models\Statuslabel;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use Laravel\Mcp\Request;
use Tests\TestCase;

/**
 * Regression coverage: a non-superuser in Company A cannot land a
 * create/update that targets a Company B they cannot reach. Six tools,
 * all FMCS-enabled, mirroring the REST pattern that uses
 * Company::getIdForCurrentUser to clamp the submitted company_id to
 * one the caller can actually see.
 */
class FmcsCrossCompanyWriteTest extends TestCase
{
    private Company $companyA;

    private Company $companyB;

    private User $userInA;

    protected function setUp(): void
    {
        parent::setUp();

        [$this->companyA, $this->companyB] = Company::factory()->count(2)->create();

        $this->userInA = $this->companyA->users()->save(
            User::factory()
                ->createAssets()
                ->editAssets()
                ->createConsumables()
                ->editConsumables()
                ->createLocations()
                ->editLocations()
                ->make()
        );

        $this->settings->enableScopedLocationsWithFullMultipleCompanySupport();
        $this->actingAs($this->userInA);
    }

    public function test_create_asset_cross_company_is_rejected()
    {
        $model = AssetModel::factory()->create();
        $status = Statuslabel::factory()->create();

        $this->expectException(ValidationException::class);
        try {
            (new CreateAssetTool)->handle(new Request([
                'asset_tag' => 'PLANT-1',
                'model_id' => $model->id,
                'status_id' => $status->id,
                'company_id' => $this->companyB->id,
            ]));
        } finally {
            $this->assertDatabaseMissing('assets', ['company_id' => $this->companyB->id, 'asset_tag' => 'PLANT-1']);
        }
    }

    public function test_update_asset_cross_company_is_rejected()
    {
        $asset = $this->companyA->assets()->save(Asset::factory()->make());

        $this->expectException(ValidationException::class);
        try {
            (new UpdateAssetTool)->handle(new Request([
                'id' => $asset->id,
                'company_id' => $this->companyB->id,
            ]));
        } finally {
            $this->assertNotEquals($this->companyB->id, $asset->fresh()->company_id);
        }
    }

    public function test_create_consumable_cross_company_is_rejected()
    {
        $category = Category::factory()->forConsumables()->create();

        $this->expectException(ValidationException::class);
        try {
            (new CreateConsumableTool)->handle(new Request([
                'name' => 'PlantCons',
                'qty' => 10,
                'category_id' => $category->id,
                'company_id' => $this->companyB->id,
            ]));
        } finally {
            $this->assertDatabaseMissing('consumables', ['name' => 'PlantCons', 'company_id' => $this->companyB->id]);
        }
    }

    public function test_update_consumable_cross_company_is_rejected()
    {
        $consumable = Consumable::factory()->create(['company_id' => $this->companyA->id]);

        $this->expectException(ValidationException::class);
        try {
            (new UpdateConsumableTool)->handle(new Request([
                'id' => $consumable->id,
                'company_id' => $this->companyB->id,
            ]));
        } finally {
            $this->assertNotEquals($this->companyB->id, $consumable->fresh()->company_id);
        }
    }

    public function test_create_location_cross_company_is_rejected()
    {
        $this->expectException(ValidationException::class);
        try {
            (new CreateLocationTool)->handle(new Request([
                'name' => 'PlantLoc',
                'company_id' => $this->companyB->id,
            ]));
        } finally {
            $this->assertDatabaseMissing('locations', ['name' => 'PlantLoc', 'company_id' => $this->companyB->id]);
        }
    }

    public function test_update_location_cross_company_is_rejected()
    {
        $location = Location::factory()->create(['company_id' => $this->companyA->id]);

        $this->expectException(ValidationException::class);
        try {
            (new UpdateLocationTool)->handle(new Request([
                'id' => $location->id,
                'company_id' => $this->companyB->id,
            ]));
        } finally {
            $this->assertNotEquals($this->companyB->id, $location->fresh()->company_id);
        }
    }
}
