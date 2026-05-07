<?php

namespace Tests\Feature\Assets\Ui;

use App\Models\AssetModel;
use App\Models\Statuslabel;
use App\Models\User;
use Tests\TestCase;

class StoreAssetsTest extends TestCase
{
    public function test_permission_required_to_view_create_asset_page()
    {
        $this->actingAs(User::factory()->create())
            ->get(route('hardware.create'))
            ->assertForbidden();
    }

    public function test_permission_required_to_store_asset()
    {
        $this->actingAs(User::factory()->create())
            ->post(route('hardware.store'), [
                'model_id' => AssetModel::factory()->create()->id,
                'status_id' => Statuslabel::factory()->create()->id,
            ])
            ->assertForbidden();
    }

    public function test_create_asset_page_renders()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('hardware.create'))
            ->assertOk();
    }

    public function test_asset_can_be_stored_with_serial_required_and_serial_provided()
    {
        $user = User::factory()->superuser()->create();
        $this->actingAs($user);

        $model = AssetModel::factory()->create([
            'require_serial' => 1,
        ]);

        $response = $this->post(route('hardware.store'), [
            'model_id' => $model->id,
            'serials' => [1 => 'ABC123'],
            'asset_tags' => [1 => '1234'],
            'status_id' => 1,
            // other required fields...
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success-unescaped');
        $this->assertNotEquals(
            trans('admin/hardware/form.serial_required'),
            session('error')
        );
        $this->assertDatabaseHas('assets', [
            'model_id' => $model->id,
            'serial' => 'ABC123',
            'asset_tag' => '1234',
        ]);

    }

    public function test_asset_cannot_be_stored_if_serial_required_and_missing()
    {
        $user = User::factory()->superuser()->create();
        $this->actingAs($user);

        $model = AssetModel::factory()->create([
            'require_serial' => 1,
        ]);

        $response = $this->post(route('hardware.store'), [
            'model_id' => $model->id,
            'serials' => [], // ← serial missing
            'asset_tags' => [1 => '1234'],
            'status_id' => 1,
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['serials.1']);

        $this->assertDatabaseMissing('assets', [
            'model_id' => $model->id,
            'asset_tag' => '1234',
        ]);

        $response->assertSessionMissing('success-unescaped');
    }
}
