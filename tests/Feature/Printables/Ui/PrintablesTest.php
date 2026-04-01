<?php

namespace Tests\Feature\Printables\Ui;

use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Category;
use App\Models\Printable;
use App\Models\User;
use Tests\TestCase;

class PrintablesTest extends TestCase
{
    // -------------------------------------------------------------------------
    // Index
    // -------------------------------------------------------------------------

    public function test_user_without_permission_cannot_view_printables_index(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('printables.index'))
            ->assertForbidden();
    }

    public function test_user_with_categories_view_permission_can_view_printables_index(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get(route('printables.index'))
            ->assertOk();
    }

    // -------------------------------------------------------------------------
    // Create
    // -------------------------------------------------------------------------

    public function test_user_without_permission_cannot_see_create_form(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('printables.create'))
            ->assertForbidden();
    }

    public function test_admin_can_see_create_form(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get(route('printables.create'))
            ->assertOk()
            ->assertViewIs('printables.edit');
    }

    // -------------------------------------------------------------------------
    // Store
    // -------------------------------------------------------------------------

    public function test_user_without_permission_cannot_create_printable(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('printables.store'), [
                'name'    => 'Test Printable',
                'content' => '<p>{asset_tag}</p>',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('printables', ['name' => 'Test Printable']);
    }

    public function test_admin_can_create_printable(): void
    {
        $category = Category::factory()->create(['category_type' => 'asset']);

        $this->actingAs(User::factory()->admin()->create())
            ->post(route('printables.store'), [
                'name'         => 'Test Printable',
                'content'      => '<p>{asset_tag}</p>',
                'category_ids' => [$category->id],
            ])
            ->assertRedirect(route('printables.index'));

        $this->assertDatabaseHas('printables', ['name' => 'Test Printable']);

        $printable = Printable::where('name', 'Test Printable')->firstOrFail();
        $this->assertContains($category->id, $printable->categories->pluck('id')->toArray());
    }

    public function test_printable_requires_name_and_content(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->post(route('printables.store'), [
                'name'    => '',
                'content' => '',
            ])
            ->assertSessionHasErrors(['name', 'content']);
    }

    // -------------------------------------------------------------------------
    // Edit / Update
    // -------------------------------------------------------------------------

    public function test_user_without_permission_cannot_see_edit_form(): void
    {
        $printable = Printable::factory()->create();

        $this->actingAs(User::factory()->create())
            ->get(route('printables.edit', $printable->id))
            ->assertForbidden();
    }

    public function test_admin_can_see_edit_form(): void
    {
        $printable = Printable::factory()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('printables.edit', $printable->id))
            ->assertOk()
            ->assertViewIs('printables.edit');
    }

    public function test_admin_can_update_printable(): void
    {
        $printable = Printable::factory()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->put(route('printables.update', $printable->id), [
                'name'    => 'Updated Name',
                'content' => '<p>Updated content {model_name}</p>',
            ])
            ->assertRedirect(route('printables.index'));

        $this->assertDatabaseHas('printables', ['name' => 'Updated Name']);
    }

    // -------------------------------------------------------------------------
    // Delete
    // -------------------------------------------------------------------------

    public function test_user_without_permission_cannot_delete_printable(): void
    {
        $printable = Printable::factory()->create();

        $this->actingAs(User::factory()->create())
            ->delete(route('printables.destroy', $printable->id))
            ->assertForbidden();

        $this->assertModelExists($printable);
    }

    public function test_admin_can_delete_printable(): void
    {
        $printable = Printable::factory()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->delete(route('printables.destroy', $printable->id))
            ->assertRedirect(route('printables.index'));

        $this->assertSoftDeleted($printable);
    }

    // -------------------------------------------------------------------------
    // Single-Asset Generation
    // -------------------------------------------------------------------------

    public function test_user_can_generate_printable_for_asset_in_matching_category(): void
    {
        $category  = Category::factory()->create(['category_type' => 'asset']);
        $printable = Printable::factory()->create();
        $printable->categories()->attach($category->id);

        $model = AssetModel::factory()->create(['category_id' => $category->id]);
        $asset = Asset::factory()->create(['model_id' => $model->id]);

        $this->actingAs(User::factory()->viewAssets()->create())
            ->get(route('hardware.printable.show', ['asset' => $asset->id, 'printable' => $printable->id]))
            ->assertOk();
    }

    public function test_user_cannot_generate_printable_for_asset_in_different_category(): void
    {
        $category1 = Category::factory()->create(['category_type' => 'asset']);
        $category2 = Category::factory()->create(['category_type' => 'asset']);
        $printable = Printable::factory()->create();
        $printable->categories()->attach($category1->id);

        $model = AssetModel::factory()->create(['category_id' => $category2->id]);
        $asset = Asset::factory()->create(['model_id' => $model->id]);

        $this->actingAs(User::factory()->viewAssets()->create())
            ->get(route('hardware.printable.show', ['asset' => $asset->id, 'printable' => $printable->id]))
            ->assertRedirect(route('hardware.show', $asset->id));
    }

    // -------------------------------------------------------------------------
    // Category Assignment
    // -------------------------------------------------------------------------

    public function test_printables_are_synced_when_category_is_updated(): void
    {
        $category  = Category::factory()->create(['category_type' => 'asset']);
        $printable = Printable::factory()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->put(route('categories.update', ['category' => $category->id]), [
                'name'          => $category->name,
                'category_type' => 'asset',
                'printable_ids' => [$printable->id],
            ])
            ->assertRedirect(route('categories.index'));

        $this->assertContains(
            $printable->id,
            $category->fresh()->printables->pluck('id')->toArray()
        );
    }
}
