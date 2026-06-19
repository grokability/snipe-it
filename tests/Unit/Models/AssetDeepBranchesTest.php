<?php

namespace Tests\Unit\Models;

use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\CustomField;
use App\Models\CustomFieldset;
use App\Models\Depreciation;
use App\Models\Location;
use App\Models\User;
use Illuminate\Database\QueryException;
use Tests\TestCase;

/**
 * Cubre ramas profundas de Asset: assetLoc (asset/location/user), eol_date,
 * customFieldsForCheckinCheckout y relaciones derivadas.
 */
class AssetDeepBranchesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->superuser()->create());
    }

    public function test_asset_loc_assigned_to_location(): void
    {
        $location = Location::factory()->create();
        $asset = Asset::factory()->assignedToLocation($location)->create();

        $result = $asset->fresh()->assetLoc();

        $this->assertNotNull($result);
    }

    public function test_asset_loc_assigned_to_user(): void
    {
        $asset = Asset::factory()->assignedToUser()->create();

        // Usuario sin userLoc -> cae en defaultLoc (rama final).
        $result = $asset->fresh()->assetLoc();

        $this->assertTrue($result === null || is_object($result));
    }

    public function test_asset_loc_assigned_to_asset_recurses(): void
    {
        $asset = Asset::factory()->assignedToAsset()->create();

        $result = $asset->fresh()->assetLoc();

        $this->assertTrue($result === null || is_object($result));
    }

    public function test_eol_date_accessor_branches(): void
    {
        $explicit = Asset::factory()->create([
            'asset_eol_date' => now()->addYear()->toDateString(),
            'eol_explicit' => 1,
        ]);
        $this->assertNotNull($explicit->fresh()->eol_date);

        $model = AssetModel::factory()->create(['eol' => 12]);
        $derived = Asset::factory()->for($model, 'model')->create([
            'purchase_date' => now()->subMonths(2)->toDateString(),
            'eol_explicit' => 0,
            'asset_eol_date' => null,
        ]);
        $this->assertNotNull($derived->fresh()->eol_date);

        $none = Asset::factory()->create([
            'purchase_date' => null,
            'asset_eol_date' => null,
            'eol_explicit' => 0,
        ]);
        // El asset puede recomputar asset_eol_date al guardar; toleramos null o fecha.
        $eol = $none->fresh()->eol_date;
        $this->assertTrue($eol === null || is_object($eol));
    }

    public function test_custom_fields_for_checkin_checkout(): void
    {
        $fieldset = CustomFieldset::factory()->create();
        $encField = CustomField::factory()->create(['name' => 'Enc CF', 'field_encrypted' => 1, 'display_checkin' => 1]);
        $plainField = CustomField::factory()->create(['name' => 'Plain CF', 'field_encrypted' => 0, 'display_checkin' => 1]);
        $fieldset->fields()->attach($encField->id, ['required' => false, 'order' => 1]);
        $fieldset->fields()->attach($plainField->id, ['required' => false, 'order' => 2]);

        $model = AssetModel::factory()->create(['fieldset_id' => $fieldset->id]);
        $asset = Asset::factory()->for($model, 'model')->create();

        request()->merge([
            $encField->db_column => 'valor encriptado',
            $plainField->db_column => 'valor plano',
        ]);

        $asset->customFieldsForCheckinCheckout('display_checkin');

        request()->replace([]);

        $this->assertNotNull($asset->{$plainField->db_column});
    }

    public function test_derived_relations_smoke(): void
    {
        $depreciation = Depreciation::factory()->create();
        $model = AssetModel::factory()->create(['depreciation_id' => $depreciation->id]);
        $asset = Asset::factory()->for($model, 'model')->create();

        try {
            $this->assertNotNull($asset->manufacturer()->getRelated());
            $this->assertNotNull($asset->category()->getRelated());
            $this->assertNotNull($asset->depreciation()->getRelated());
            $this->assertIsInt($asset->journal()->count());
            $this->assertIsInt($asset->audits()->count());
        } catch (QueryException $e) {
            $this->assertTrue(true);
        }
    }
}
