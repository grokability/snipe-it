<?php

namespace Tests\Feature\AssetModels\Ui;

use App\Models\User;
use Tests\TestCase;

class IndexAssetModelsTest extends TestCase
{
    public function test_permission_required_to_view_asset_model_list()
    {
        $this->actingAs(User::factory()->create())
            ->get(route('models.index'))
            ->assertForbidden();
    }

    public function test_user_can_list_asset_models()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('models.index'))
            ->assertOk();
    }
}
