<?php

namespace Tests\Feature\Consumables\Api;

use App\Models\Consumable;
use App\Models\Category;
use App\Models\User;
use Tests\TestCase;

class ConsumableUpdateTest extends TestCase
{

    public function testCanUpdateConsumableViaPatchWithoutCategoryType()
    {
        $consumable = Consumable::factory()->create();

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->patchJson(route('api.consumables.update', $consumable), [
                'name' => 'Test Consumable',
            ])
            ->assertOk()
            ->assertStatusMessageIs('success')
            ->assertStatus(200)
            ->json();

        $consumable->refresh();
        $this->assertEquals('Test Consumable', $consumable->name, 'Name was not updated');

    }

    public function testCannotUpdateConsumableViaPatchWithInvalidCategoryType()
    {
        $category = Category::factory()->create(['category_type' => 'asset']);
        $consumable = Consumable::factory()->create();

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->patchJson(route('api.consumables.update', $consumable), [
                'name' => 'Test Consumable',
                'category_id' => $category->id,
            ])
            ->assertOk()
            ->assertStatusMessageIs('error')
            ->assertStatus(200)
            ->json();

        $category->refresh();
        $this->assertNotEquals('Test Consumable', $consumable->name, 'Name was not updated');
        $this->assertNotEquals('consumable', $consumable->category_id, 'Category was not updated');

    }

}
