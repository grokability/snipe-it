<?php

namespace Tests\Unit\Models;

use App\Models\AssetModel;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Tests\TestCase;

class AssetModelLogicTest extends TestCase
{
    public function test_is_deletable_returns_bool(): void
    {
        $this->actingAs(User::factory()->superuser()->create());
        $model = AssetModel::factory()->create();

        $this->assertIsBool($model->isDeletable());
    }

    public function test_percent_remaining_is_numeric(): void
    {
        $model = AssetModel::factory()->create();

        $this->assertIsNumeric($model->percentRemaining());
    }

    public function test_default_values_returns_collection(): void
    {
        $model = AssetModel::factory()->create();

        $this->assertInstanceOf(Collection::class, $model->defaultValues()->get());
    }

    public function test_get_image_url_returns_string_or_false(): void
    {
        $model = AssetModel::factory()->create(['image' => null]);

        $result = $model->getImageUrl();
        $this->assertTrue(is_string($result) || $result === false);
    }

    public function test_scope_in_category(): void
    {
        $category = Category::factory()->forAssets()->create();
        AssetModel::factory()->create(['category_id' => $category->id]);

        $this->assertInstanceOf(Collection::class, AssetModel::inCategory([$category->id])->get());
    }

    public function test_scope_requestable_models(): void
    {
        AssetModel::factory()->create();

        $this->assertInstanceOf(Collection::class, AssetModel::RequestableModels()->get());
    }
}
