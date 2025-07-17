<?php

namespace Tests\Feature\Categories\Api;

use App\Models\Category;
use App\Models\User;
use Tests\TestCase;

class UpdateCategoriesTest extends TestCase
{
    public function test_requires_permission_to_update_category()
    {
        $category = Category::factory()->create();

        $this->actingAsForApi(User::factory()->create())
            ->patchJson(route('api.categories.update', $category))
            ->assertForbidden();
    }

    public function test_can_update_category()
    {
        $category = Category::factory()->forAssets()->create([
            'name' => 'Test Category',
            'require_acceptance' => false,
            'alert_on_response' => false,
        ]);

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->patchJson(route('api.categories.update', $category), [
                'name' => 'Test Category Edited',
                'notes' => 'Test Note Edited',
                'require_acceptance' => true,
                'alert_on_response' => true,
            ])
            ->assertOk()
            ->assertStatusMessageIs('success')
            ->assertStatus(200);

        $category->refresh();
        $this->assertEquals('Test Category Edited', $category->name, 'Name was not updated');
        $this->assertEquals('Test Note Edited', $category->notes, 'Note was not updated');
        $this->assertEquals(1, $category->require_acceptance, 'Require acceptance was not updated');
        $this->assertTrue($category->alert_on_response, 'Alert on response was not updated');
    }

    public function testCanUpdateCategoryViaPatchWithoutCategoryType()
    {
        $category = Category::factory()->create();

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->patchJson(route('api.categories.update', $category), [
                'name' => 'Test Category',
                'eula_text' => 'Test EULA',
                'notes' => 'Test Note',
            ])
            ->assertOk()
            ->assertStatusMessageIs('success')
            ->assertStatus(200)
            ->json();

        //dd($response);
        $category->refresh();
        $this->assertEquals('Test Category', $category->name, 'Name was not updated');
        $this->assertEquals('Test EULA', $category->eula_text, 'EULA was not updated');
        $this->assertEquals('Test Note', $category->notes, 'Note was not updated');

    }

    public function testCannotUpdateCategoryViaPatchWithCategoryType()
    {
        $category = Category::factory()->create();

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->patchJson(route('api.categories.update', $category), [
                'name' => 'Test Category',
                'eula_text' => 'Test EULA',
                'category_type' => 'accessory',
                'note' => 'Test Note',
            ])
            ->assertOk()
            ->assertStatusMessageIs('error')
            ->assertStatus(200)
            ->json();
        
        $category->refresh();
        $this->assertNotEquals('Test Category', $category->name, 'Name was not updated');
        $this->assertNotEquals('Test EULA', $category->eula_text, 'EULA was not updated');
        $this->assertNotEquals('Test Note', $category->notes, 'Note was not updated');
        $this->assertNotEquals('accessory', $category->category_type, 'EULA was not updated');

    }
}
