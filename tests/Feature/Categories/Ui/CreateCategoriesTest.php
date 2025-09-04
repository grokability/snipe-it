<?php

namespace Tests\Feature\Categories\Ui;

use App\Models\AssetModel;
use App\Models\Category;
use App\Models\User;
use Tests\TestCase;

class CreateCategoriesTest extends TestCase
{
    public function testPermissionRequiredToCreateCategories()
    {
        $this->actingAs(User::factory()->create())
            ->post(route('categories.store'), [
                'name' => 'Test Category',
                'category_type' => 'asset',
            ])
            ->assertForbidden();
    }

    public function testPageRenders()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('categories.create'))
            ->assertOk();
    }

    public function testUserCanCreateCategories()
    {
        $this->assertFalse(Category::where('name', 'Test Category')->exists());

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('categories.store'), [
                'name' => 'Test Category',
                'category_type' => 'asset',
                'eula_text' => 'Sample text',
                'require_acceptance' => '1',
                'notes' => 'My Note',
            ])
            ->assertRedirect(route('categories.index'));

        $this->assertDatabaseHas('categories', [
            'name' => 'Test Category',
            'category_type' => 'asset',
            'eula_text' => 'Sample text',
            'notes' => 'My Note',
            'require_acceptance' => 1,
            'alert_on_response' => 0,
        ]);
    }

    public function testUserCannotCreateCategoriesWithInvalidType()
    {
        $this->assertFalse(Category::where('name', 'Test Category')->exists());

        $this->actingAs(User::factory()->superuser()->create())
            ->from(route('categories.create'))
            ->post(route('categories.store'), [
                'name' => 'Test Category',
                'category_type' => 'invalid',
            ])
            ->assertRedirect(route('categories.create'));

        $this->assertFalse(Category::where('name', 'Test Category')->exists());
    }

}
