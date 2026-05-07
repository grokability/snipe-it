<?php

namespace Tests\Feature\AssetModels\Ui;

use App\Models\AssetModel;
use App\Models\User;
use Tests\TestCase;

class ShowAssetModelsTest extends TestCase
{
    public function test_page_renders()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('models.show', AssetModel::factory()->create()))
            ->assertOk();
    }
}
