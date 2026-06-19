<?php

namespace Tests\Unit\Transformers;

use App\Http\Transformers\AssetsTransformer;
use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Component;
use App\Models\CustomField;
use App\Models\CustomFieldset;
use App\Models\License;
use App\Models\LicenseSeat;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Tests\TestCase;

/**
 * Cubre las ramas de custom fields (encriptados / DATE / requestable), componentes
 * y la rama "sin viewKeys" de AssetsTransformer, que no estaban cubiertas.
 */
class AssetsTransformerCustomFieldsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->superuser()->create());
    }

    /**
     * Crea un asset cuyo modelo tiene un fieldset con campos encriptados,
     * de fecha, de texto plano y uno requestable, con valores seteados.
     */
    private function assetWithCustomFields(): Asset
    {
        $fieldset = CustomFieldset::factory()->create();

        $encText = CustomField::factory()->create(['name' => 'Enc Text', 'field_encrypted' => 1, 'format' => 'ANY']);
        $encDate = CustomField::factory()->create(['name' => 'Enc Date', 'field_encrypted' => 1, 'format' => 'DATE']);
        $plainDate = CustomField::factory()->create(['name' => 'Plain Date', 'field_encrypted' => 0, 'format' => 'DATE']);
        $reqField = CustomField::factory()->create([
            'name' => 'Req Field', 'field_encrypted' => 0, 'format' => 'DATE', 'show_in_requestable_list' => 1,
        ]);

        foreach ([$encText, $encDate, $plainDate, $reqField] as $i => $field) {
            $fieldset->fields()->attach($field->id, ['required' => false, 'order' => $i + 1]);
        }

        $model = AssetModel::factory()->create(['fieldset_id' => $fieldset->id]);
        $asset = Asset::factory()->for($model, 'model')->create();

        $asset->{$encText->db_column} = Crypt::encrypt('secreto');
        $asset->{$encDate->db_column} = Crypt::encrypt('2024-01-01');
        $asset->{$plainDate->db_column} = '2024-05-05';
        $asset->{$reqField->db_column} = '2024-06-06';
        $asset->save();

        return $asset->fresh();
    }

    public function test_transform_asset_custom_fields_visible_to_superuser(): void
    {
        $asset = $this->assetWithCustomFields();

        $result = (new AssetsTransformer)->transformAsset($asset);

        $this->assertArrayHasKey('custom_fields', $result);
        $this->assertNotEmpty($result['custom_fields']);
    }

    public function test_transform_asset_custom_fields_hidden_from_regular_user(): void
    {
        $asset = $this->assetWithCustomFields();

        // Usuario sin permiso de ver custom fields encriptados -> rama "ENCRYPTED".
        $this->actingAs(User::factory()->create(['permissions' => '{}']));

        $result = (new AssetsTransformer)->transformAsset($asset);

        $this->assertArrayHasKey('custom_fields', $result);
    }

    public function test_transform_asset_with_components(): void
    {
        $asset = Asset::factory()->create();
        $component = Component::factory()->create();
        $component->assets()->attach($component->id, [
            'asset_id' => $asset->id,
            'created_by' => auth()->id(),
            'assigned_qty' => 1,
            'note' => 'comp',
        ]);

        request()->merge(['components' => 'true']);

        $result = (new AssetsTransformer)->transformAsset($asset->fresh());

        request()->merge(['components' => null]);

        $this->assertArrayHasKey('components', $result);
        $this->assertCount(1, $result['components']);
    }

    public function test_transform_requested_asset_custom_fields(): void
    {
        $asset = $this->assetWithCustomFields();

        $result = (new AssetsTransformer)->transformRequestedAsset($asset);

        $this->assertArrayHasKey('custom_fields', $result);
        $this->assertArrayHasKey('available_actions', $result);
    }

    public function test_transform_assets_datatable(): void
    {
        Asset::factory()->count(2)->create();
        $assets = Asset::all();

        $result = (new AssetsTransformer)->transformAssetsDatatable($assets);

        $this->assertArrayHasKey('rows', $result);
    }

    public function test_transform_assigned_to_user(): void
    {
        $user = User::factory()->create();
        $asset = Asset::factory()->assignedToUser($user)->create();
        // 'assigned' es la relacion morph (no auto-cargable por el nombre); la fijamos.
        $asset->setRelation('assigned', $user);

        $result = (new AssetsTransformer)->transformAssignedTo($asset);

        $this->assertIsArray($result);
        $this->assertSame('user', $result['type']);
    }

    public function test_transform_license_checked_to_asset_without_view_keys(): void
    {
        $asset = Asset::factory()->create();
        $license = License::factory()->create(['serial' => 'SECRET-KEY']);
        $seat = LicenseSeat::factory()->create(['license_id' => $license->id, 'asset_id' => $asset->id]);

        // Usuario regular sin viewKeys -> product_key enmascarado (rama else).
        $this->actingAs(User::factory()->create(['permissions' => '{}']));

        $result = (new AssetsTransformer)->transformLicenseCheckedToAsset($seat);

        $this->assertArrayHasKey('license', $result);
        $this->assertNotSame('SECRET-KEY', $result['license']['serial']);
    }
}
