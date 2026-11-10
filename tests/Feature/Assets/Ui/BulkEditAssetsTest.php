<?php

namespace Tests\Feature\Assets\Ui;

use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Company;
use App\Models\CustomField;
use App\Models\Location;
use App\Models\Statuslabel;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Tests\TestCase;

class BulkEditAssetsTest extends TestCase
{
    public function test_user_with_permissions_can_access_page()
    {
        $user = User::factory()->viewAssets()->editAssets()->create();
        $assets = Asset::factory()->count(2)->create();

        $id_array = $assets->pluck('id')->toArray();

        $this->actingAs($user)->post('/hardware/bulkedit', [
            'ids' => $id_array,
            'order' => 'asc',
            'bulk_actions' => 'edit',
            'sort' => 'id',
        ])->assertStatus(200);
    }

    public function test_handles_model_being_deleted()
    {
        $this->withoutExceptionHandling();

        $user = User::factory()->viewAssets()->editAssets()->create();
        $assets = Asset::factory()->count(2)->create();

        $assets->first()->model->forceDelete();

        $id_array = $assets->pluck('id')->toArray();

        $this->actingAs($user)->post('/hardware/bulkedit', [
            'ids' => $id_array,
            'order' => 'asc',
            'bulk_actions' => 'edit',
            'sort' => 'id',
        ])->assertStatus(200);
    }

    public function test_standard_user_cannot_access_page()
    {
        $user = User::factory()->create();
        $assets = Asset::factory()->count(2)->create();

        $id_array = $assets->pluck('id')->toArray();

        $this->actingAs($user)->post('/hardware/bulkedit', [
            'ids' => $id_array,
            'order' => 'asc',
            'bulk_actions' => 'edit',
            'sort' => 'id',
        ])->assertStatus(403);
    }

    public function test_bulk_edit_assets_accepts_all_possible_attributes()
    {
        // sets up all needed models and attributes on the assets
        // this test does not deal with custom fields - will be dealt with in separate cases
        $status1 = Statuslabel::factory()->create();
        $status2 = Statuslabel::factory()->create();
        $model1 = AssetModel::factory()->create();
        $model2 = AssetModel::factory()->create();
        $supplier1 = Supplier::factory()->create();
        $supplier2 = Supplier::factory()->create();
        $company1 = Company::factory()->create();
        $company2 = Company::factory()->create();
        $assets = Asset::factory()->count(10)->create([
            'name' => 'Old Asset Name',
            'purchase_date' => '2023-01-01',
            'expected_checkin' => '2023-01-01',
            'status_id' => $status1->id,
            'model_id' => $model1->id,
            // skipping locations on this test, it deserves it's own test
            'purchase_cost' => 1234.90,
            'supplier_id' => $supplier1->id,
            'company_id' => $company1->id,
            'warranty_months' => 24,
            'next_audit_date' => '2024-06-01',
            'requestable' => false,
            'notes' => 'This is a new note!',
        ]);

        // gets the ids together to submit to the endpoint
        $id_array = $assets->pluck('id')->toArray();

        // submits the ids and new values for each attribute. order_number
        // was dropped from the payload when the parent Asset column moved
        // to the Orders / OrderItems data model; the bulk-edit path no
        // longer touches it.
        $this->actingAs(User::factory()->editAssets()->create())->post(route('hardware/bulksave'), [
            'ids' => $id_array,
            'name' => 'New Asset Name',
            'purchase_date' => '2024-01-01',
            'expected_checkin' => '2024-01-01',
            'status_id' => $status2->id,
            'model_id' => $model2->id,
            'purchase_cost' => 5678.92,
            'supplier_id' => $supplier2->id,
            'company_id' => $company2->id,
            'warranty_months' => 36,
            'next_audit_date' => '2025-01-01',
            'requestable' => true,
            'notes' => 'This is a newer note!',
        ])
            ->assertStatus(302)
            ->assertSessionHasNoErrors();

        // asserts that each asset has the updated values
        Asset::findMany($id_array)->each(function (Asset $asset) use ($status2, $model2, $supplier2, $company2) {
            $this->assertEquals('2024-01-01', $asset->purchase_date->format('Y-m-d'));
            $this->assertEquals('2024-01-01', $asset->expected_checkin->format('Y-m-d'));
            $this->assertEquals($status2->id, $asset->status_id);
            $this->assertEquals('New Asset Name', $asset->name);
            $this->assertEquals($model2->id, $asset->model_id);
            $this->assertEquals(5678.92, $asset->purchase_cost);
            $this->assertEquals($supplier2->id, $asset->supplier_id);
            $this->assertEquals($company2->id, $asset->company_id);
            $this->assertEquals(36, $asset->warranty_months);
            $this->assertEquals('2025-01-01', $asset->next_audit_date);
            // shouldn't requestable be cast as a boolean??? it's not.
            $this->assertEquals(1, $asset->requestable);
            $this->assertEquals('This is a newer note!', $asset->notes);
        });
    }

    public function test_bulk_edit_assets_nulls_out_fields_if_selected()
    {
        // sets up all needed models and attributes on the assets
        // this test does not deal with custom fields - will be dealt with in separate cases
        $status1 = Statuslabel::factory()->create();
        $status2 = Statuslabel::factory()->create();
        $model1 = AssetModel::factory()->create();
        $model2 = AssetModel::factory()->create();
        $supplier1 = Supplier::factory()->create();
        $supplier2 = Supplier::factory()->create();
        $company1 = Company::factory()->create();
        $company2 = Company::factory()->create();
        $assets = Asset::factory()->count(10)->create([
            'name' => 'Old Asset Name',
            'purchase_date' => '2023-01-01',
            'expected_checkin' => '2023-01-01',
            'status_id' => $status1->id,
            'model_id' => $model1->id,
            // skipping locations on this test, it deserves it's own test
            'purchase_cost' => 1234.90,
            'supplier_id' => $supplier1->id,
            'company_id' => $company1->id,
            'warranty_months' => 24,
            'next_audit_date' => '2024-06-01',
            'requestable' => false,
            'notes' => 'This is a note that will be deleted',
        ]);

        // gets the ids together to submit to the endpoint
        $id_array = $assets->pluck('id')->toArray();

        // submits the ids and new values for each attribute
        $this->actingAs(User::factory()->editAssets()->create())->post(route('hardware/bulksave'), [
            'ids' => $id_array,
            'null_name' => '1',
            'null_purchase_date' => '1',
            'null_expected_checkin_date' => '1',
            'null_next_audit_date' => '1',
            'null_notes' => '1',
            'status_id' => $status2->id,
            'model_id' => $model2->id,
        ])
            ->assertStatus(302)
            ->assertSessionHasNoErrors();

        // asserts that each asset has the updated values
        Asset::findMany($id_array)->each(function (Asset $asset) {
            $this->assertNull($asset->name);
            $this->assertNull($asset->purchase_date);
            $this->assertNull($asset->expected_checkin);
            $this->assertNull($asset->next_audit_date);
            $this->assertNull($asset->notes);
        });
    }

    public function test_bulk_edit_writes_default_and_current_location_independently()
    {
        $originalDefault = Location::factory()->create();
        $originalCurrent = Location::factory()->create();
        $newDefault = Location::factory()->create();
        $newCurrent = Location::factory()->create();

        $assets = Asset::factory()->count(3)->create([
            'rtd_location_id' => $originalDefault->id,
            'location_id' => $originalCurrent->id,
        ]);

        $this->actingAs(User::factory()->editAssets()->create())->post(route('hardware/bulksave'), [
            'ids' => $assets->pluck('id')->toArray(),
            'rtd_location_id' => $newDefault->id,
            'location_id' => $newCurrent->id,
        ])
            ->assertStatus(302)
            ->assertSessionHasNoErrors();

        Asset::findMany($assets->pluck('id'))->each(function (Asset $asset) use ($newDefault, $newCurrent) {
            $this->assertEquals($newDefault->id, $asset->rtd_location_id);
            $this->assertEquals($newCurrent->id, $asset->location_id);
        });
    }

    public function test_bulk_edit_clears_default_and_current_location_when_null_checkboxes_set()
    {
        // Regression test for #11779. Pre-fix the bulk edit form had
        // no way to null out rtd_location_id or location_id, only the
        // single-asset edit path cleared them.
        $default = Location::factory()->create();
        $current = Location::factory()->create();
        $otherDefault = Location::factory()->create();

        $assets = Asset::factory()->count(3)->create([
            'rtd_location_id' => $default->id,
            'location_id' => $current->id,
        ]);

        $this->actingAs(User::factory()->editAssets()->create())->post(route('hardware/bulksave'), [
            'ids' => $assets->pluck('id')->toArray(),
            'null_rtd_location_id' => '1',
            'null_location_id' => '1',
        ])
            ->assertStatus(302)
            ->assertSessionHasNoErrors();

        Asset::findMany($assets->pluck('id'))->each(function (Asset $asset) {
            $this->assertNull($asset->rtd_location_id);
            $this->assertNull($asset->location_id);
        });

        // Mixed path: set default, clear current, independently.
        $moreAssets = Asset::factory()->count(2)->create([
            'rtd_location_id' => $default->id,
            'location_id' => $current->id,
        ]);

        $this->actingAs(User::factory()->editAssets()->create())->post(route('hardware/bulksave'), [
            'ids' => $moreAssets->pluck('id')->toArray(),
            'rtd_location_id' => $otherDefault->id,
            'null_location_id' => '1',
        ])
            ->assertStatus(302)
            ->assertSessionHasNoErrors();

        Asset::findMany($moreAssets->pluck('id'))->each(function (Asset $asset) use ($otherDefault) {
            $this->assertEquals($otherDefault->id, $asset->rtd_location_id);
            $this->assertNull($asset->location_id);
        });
    }

    public function test_bulk_edit_assets_accepts_and_updates_unencrypted_custom_fields()
    {
        $this->markIncompleteIfMySQL('Custom Fields tests do not work on MySQL');

        CustomField::factory()->ram()->create();
        CustomField::factory()->cpu()->create();

        // when getting the custom field directly from the factory the field has not been fully created yet
        // so we have to do a query afterwards to get the actual model :shrug:

        $ram = CustomField::where('name', 'RAM')->first();
        $cpu = CustomField::where('name', 'CPU')->first();

        $assets = Asset::factory()->count(10)->hasMultipleCustomFields([$ram, $cpu])->create([
            $ram->db_column => 8,
            $cpu->db_column => '2.1',
        ]);

        $id_array = $assets->pluck('id')->toArray();

        $this->actingAs(User::factory()->editAssets()->create())->post(route('hardware/bulksave'), [
            'ids' => $id_array,
            $ram->db_column => 16,
            $cpu->db_column => '4.1',
        ])->assertStatus(302);

        Asset::findMany($id_array)->each(function (Asset $asset) use ($ram, $cpu) {
            $this->assertEquals(16, $asset->{$ram->db_column});
            $this->assertEquals('4.1', $asset->{$cpu->db_column});
        });
    }

    public function test_bulk_edit_assets_nulls_custom_fields_if_selected()
    {
        $this->markIncompleteIfMySQL('Custom Fields tests do not work on MySQL');

        CustomField::factory()->ram()->create();
        CustomField::factory()->cpu()->create();
        CustomField::factory()->phone()->create();

        // when getting the custom field directly from the factory the field has not been fully created yet
        // so we have to do a query afterwards to get the actual model :shrug:

        $ram = CustomField::where('name', 'RAM')->first();
        $cpu = CustomField::where('name', 'CPU')->first();
        $phone = CustomField::where('name', 'Phone Number')->first();

        $assets = Asset::factory()->count(10)->hasMultipleCustomFields([$ram, $cpu])->create([
            $ram->db_column => 8,
            $cpu->db_column => '2.1',
        ]);

        $id_array = $assets->pluck('id')->toArray();

        $this->actingAs(User::factory()->editAssets()->create())->post(route('hardware/bulksave'), [
            'ids' => $id_array,
            $ram->db_column => 16,
            $cpu->db_column => '4.1',
            'null'.$phone->db_column => '8304997586',
        ])->assertStatus(302);

        $this->actingAs(User::factory()->editAssets()->create())->post(route('hardware/bulksave'), [
            'ids' => $id_array,
            null.$phone->db_column => 1,
        ]);

        Asset::findMany($id_array)->each(function (Asset $asset) use ($ram, $cpu, $phone) {
            $this->assertEquals(16, $asset->{$ram->db_column});
            $this->assertEquals('4.1', $asset->{$cpu->db_column});
            $this->assertEquals(null, $asset->{$phone->db_column});
        });
    }

    public function test_bulk_edit_assets_accepts_and_updates_encrypted_custom_fields()
    {
        $this->markIncompleteIfMySQL('Custom Fields tests do not work on MySQL');

        CustomField::factory()->testEncrypted()->create();

        $encrypted = CustomField::where('name', 'Test Encrypted')->first();

        $assets = Asset::factory()->count(10)->hasEncryptedCustomField($encrypted)->create([
            $encrypted->db_column => 'Original Encrypted Text',
        ]);

        $id_array = $assets->pluck('id')->toArray();

        $this->actingAs(User::factory()->admin()->create())->post(route('hardware/bulksave'), [
            'ids' => $id_array,
            $encrypted->db_column => 'New Encrypted Text',
        ])->assertStatus(302);

        Asset::findMany($id_array)->each(function (Asset $asset) use ($encrypted) {
            $this->assertEquals('New Encrypted Text', Crypt::decrypt($asset->getRawOriginal($encrypted->db_column)));
        });
    }

    public function test_bulk_edit_assets_requires_admin_to_update_encrypted_custom_fields()
    {
        $this->markIncompleteIfMySQL('Custom Fields tests do not work on mysql');
        $edit_user = User::factory()->editAssets()->create();
        $admin_user = User::factory()->admin()->create();

        CustomField::factory()->testEncrypted()->create();

        $encrypted = CustomField::where('name', 'Test Encrypted')->first();

        $admin_assets = Asset::factory()->count(5)->hasEncryptedCustomField($encrypted)->create([
            $encrypted->db_column => 'Original Encrypted Text',
        ]);

        $standard_assets = Asset::factory()->count(5)->hasEncryptedCustomField($encrypted)->create([
            $encrypted->db_column => 'Original Encrypted Text',
        ]);

        $admin_id_array = $admin_assets->pluck('id')->toArray();
        $standard_id_array = $standard_assets->pluck('id')->toArray();

        $this->actingAs($admin_user)->post(route('hardware/bulksave'), [
            'ids' => $admin_id_array,
            $encrypted->db_column => 'New Encrypted Text',
        ])->assertStatus(302);

        // do we want to return an error when this happens???
        $this->actingAs($edit_user)->post(route('hardware/bulksave'), [
            'ids' => $standard_id_array,
            $encrypted->db_column => 'New Encrypted Text',
        ])->assertStatus(302);

        Asset::findMany($admin_id_array)->each(function (Asset $asset) use ($encrypted) {
            $this->assertEquals('New Encrypted Text', Crypt::decrypt($asset->getRawOriginal($encrypted->db_column)));
        });

        Asset::findMany($standard_id_array)->each(function (Asset $asset) use ($encrypted) {
            $this->assertEquals('Original Encrypted Text', Crypt::decrypt($asset->getRawOriginal($encrypted->db_column)));
        });
    }

    public function test_same_origin_referer_is_stored_as_bulk_back_url()
    {
        $user = User::factory()->viewAssets()->editAssets()->create();
        $assets = Asset::factory()->count(2)->create();
        $originUrl = route('hardware.index').'?status_type=Deployed';

        $this->actingAs($user)
            ->from($originUrl)
            ->post('/hardware/bulkedit', [
                'ids' => $assets->pluck('id')->toArray(),
                'bulk_actions' => 'edit',
                'sort' => 'id',
                'order' => 'asc',
            ])
            ->assertSessionHas('bulk_back_url', $originUrl);
    }

    public function test_offsite_referer_is_not_stored_as_bulk_back_url()
    {
        $user = User::factory()->viewAssets()->editAssets()->create();
        $assets = Asset::factory()->count(2)->create();

        $this->actingAs($user)
            ->from('https://evil.example.com/phish')
            ->post('/hardware/bulkedit', [
                'ids' => $assets->pluck('id')->toArray(),
                'bulk_actions' => 'edit',
                'sort' => 'id',
                'order' => 'asc',
            ])
            ->assertSessionMissing('bulk_back_url');
    }

    public function test_bulk_update_redirects_to_stashed_same_origin_url()
    {
        $user = User::factory()->superuser()->create();
        $asset = Asset::factory()->create();
        $originUrl = route('hardware.index').'?status_type=Deployed';

        $this->actingAs($user)
            ->withSession(['bulk_back_url' => $originUrl])
            ->post('/hardware/bulksave', [
                'ids' => [$asset->id => '1'],
            ])
            ->assertRedirect($originUrl);
    }

    public function test_bulk_update_falls_back_to_hardware_index_when_stashed_url_is_missing()
    {
        $user = User::factory()->superuser()->create();
        $asset = Asset::factory()->create();

        $this->actingAs($user)
            ->post('/hardware/bulksave', [
                'ids' => [$asset->id => '1'],
            ])
            ->assertRedirect(route('hardware.index'));
    }

    public function test_bulk_update_ignores_poisoned_stashed_url()
    {
        // If the write-side gate were ever bypassed, the read-side
        // helper coalescing must still keep the redirect on-domain.
        $user = User::factory()->superuser()->create();
        $asset = Asset::factory()->create();

        $this->actingAs($user)
            ->withSession(['bulk_back_url' => 'https://evil.example.com/steal-session'])
            ->post('/hardware/bulksave', [
                'ids' => [$asset->id => '1'],
            ])
            ->assertRedirect(route('hardware.index'));
    }
}
