<?php

namespace Tests\Feature\Categories\Ui;

use App\Models\Category;
use App\Models\Asset;
use App\Models\User;
use Tests\TestCase;

class UpdateCategoriesTest extends TestCase
{
    public function testPermissionRequiredToStoreCategory()
    {
        $this->actingAs(User::factory()->create())
            ->post(route('categories.store'), [
                'name' => 'Test Category',
                'category_type' => 'asset'
            ])
            ->assertStatus(403)
            ->assertForbidden();
    }

    public function testPageRenders()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('categories.edit', Category::factory()->create()))
            ->assertOk();
    }

    public function testUserCanCreateCategories()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('categories.store'), [
                'name' => 'Test Category',
                'category_type' => 'asset',
            ])
            ->assertStatus(302)
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('categories.index'));

        $this->assertTrue(Category::where('name', 'Test Category')->exists());
    }

    public function testUserCanEditAssetCategory()
    {
        $category = Category::factory()->forAssets()->create([
            'name' => 'Test Category',
            'require_acceptance' => false,
            'alert_on_response' => false,
        ]);

        $this->assertTrue(Category::where('name', 'Test Category')->exists());

        $response = $this->actingAs(User::factory()->superuser()->create())
            ->put(route('categories.update', $category), [
                'name' => 'Test Category Edited',
                'notes' => 'Test Note Edited',
                'require_acceptance' => '1',
                'alert_on_response' => '1',
            ])
            ->assertStatus(302)
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('categories.index'));

        $this->followRedirects($response)->assertSee('Success');

        $this->assertDatabaseHas('categories', [
            'name' => 'Test Category Edited',
            'notes' => 'Test Note Edited',
            'require_acceptance' => 1,
            'alert_on_response' => 1,
        ]);
    }

    public function testUserCanChangeCategoryTypeIfNoAssetsAssociated()
    {
        $category = Category::factory()->forAssets()->create(['name' => 'Test Category']);
        $this->assertTrue(Category::where('name', 'Test Category')->exists());

        $response = $this->actingAs(User::factory()->superuser()->create())
            ->from(route('categories.edit', $category->id))
            ->put(route('categories.update', $category), [
                'name' => 'Test Category Edited',
                'category_type' => 'accessory',
                'notes' => 'Test Note Edited',
            ])
            ->assertSessionHasNoErrors()
            ->assertStatus(302)
            ->assertRedirect(route('categories.index'));

        $this->followRedirects($response)->assertSee('Success');
        $this->assertTrue(Category::where('name', 'Test Category Edited')->where('notes', 'Test Note Edited')->exists());

    }

    public function testUserCannotChangeCategoryTypeIfAssetsAreAssociated()
    {
        Asset::factory()->count(5)->laptopMbp()->create();
        $category = Category::where('name', 'Laptops')->first();

        $response = $this->actingAs(User::factory()->superuser()->create())
            ->from(route('categories.edit', $category))
            ->put(route('categories.update', $category), [
                'name' => 'Test Category Edited',
                'category_type' => 'accessory',
                'notes' => 'Test Note Edited',
            ])
            ->assertSessionHasErrors(['category_type'])
            ->assertInvalid(['category_type'])
            ->assertStatus(302)
            ->assertRedirect(route('categories.edit', $category));

        $this->followRedirects($response)->assertSee(trans('general.error'));
        $this->assertFalse(Category::where('name', 'Test Category Edited')->where('notes', 'Test Note Edited')->exists());

    }
}
