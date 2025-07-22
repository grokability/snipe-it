<?php

namespace Tests\Feature\Categories\Api;

use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Category;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class CreateCategoriesTest extends TestCase
{
    public function testRequiresPermissionToCreateCategory()
    {
        $this->actingAsForApi(User::factory()->create())
            ->postJson(route('api.categories.store'))
            ->assertForbidden();
    }

    public function testCanCreateCategoryWithValidCategoryType()
    {
        $response = $this->actingAsForApi(User::factory()->superuser()->create())
            ->postJson(route('api.categories.store'), [
                'name' => 'Test Category',
                'eula_text' => 'Test EULA',
                'category_type' => 'accessory',
                'notes' => 'Test Note',
                'require_acceptance' => true,
                'alert_on_response' => true,
            ])
            ->assertOk()
            ->assertStatusMessageIs('success')
            ->assertStatus(200)
            ->json();

        $this->assertTrue(Category::where('name', 'Test Category')->exists());

        $category = Category::find($response['payload']['id']);
        $this->assertEquals('Test Category', $category->name);
        $this->assertEquals('Test EULA', $category->eula_text);
        $this->assertEquals('Test Note', $category->notes);
        $this->assertEquals('accessory', $category->category_type);
        $this->assertEquals(1, $category->require_acceptance);
        $this->assertEquals(1, $category->alert_on_response);
    }

    public function testCannotCreateCategoryWithoutCategoryType()
    {
        $response = $this->actingAsForApi(User::factory()->superuser()->create())
            ->postJson(route('api.categories.store'), [
                'name' => 'Test Category',
            ])
            ->assertOk()
            ->assertStatus(200)
            ->assertStatusMessageIs('error')
            ->assertJson([
                'messages' => [
                    'category_type'    => ['The category type field is required.'],
                ],
            ]);
        $this->assertFalse(Category::where('name', 'Test Category')->exists());

    }

    public function testCannotCreateCategoryWithInvalidCategoryType()
    {
        $this->actingAsForApi(User::factory()->superuser()->create())
            ->postJson(route('api.categories.store'), [
                'name' => 'Test Category',
                'eula_text' => 'Test EULA',
                'category_type' => 'invalid',
            ])
            ->assertOk()
            ->assertStatus(200)
            ->assertStatusMessageIs('error')
            ->assertJson([
                'messages' => [
                    'category_type'    => ['The selected category type is invalid.'],
                ],
            ]);
        
        $this->assertFalse(Category::where('name', 'Test Category')->exists());

    }
}
