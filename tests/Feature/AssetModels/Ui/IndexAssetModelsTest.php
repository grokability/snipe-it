<?php

namespace Tests\Feature\AssetModels\Ui;

use App\Models\User;
use Tests\TestCase;

class IndexAssetModelsTest extends TestCase
{
    public function testPermissionRequiredToViewAssetModelList()
    {
        $this->actingAs(User::factory()->create())
            ->get(route('models.index'))
            ->assertForbidden();
    }

    public function testUserCanListAssetModels()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('models.index'))
            ->assertOk();
    }
}
