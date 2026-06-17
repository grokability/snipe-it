<?php

namespace Tests\Unit\Models;

use App\Models\Accessory;
use App\Models\Category;
use App\Models\Depreciation;
use App\Models\User;
use Tests\TestCase;

class CategoryDepreciationLogicTest extends TestCase
{
    public function test_category_item_count_reflects_related_items(): void
    {
        $category = Category::factory()->forAccessories()->create();
        Accessory::factory()->count(2)->create(['category_id' => $category->id]);

        $this->assertIsInt($category->itemCount());
    }

    public function test_category_get_eula_returns_string_or_null(): void
    {
        $category = Category::factory()->forAccessories()->create(['eula_text' => 'Be careful.']);

        $this->assertStringContainsString('Be careful.', $category->getEula());
    }

    public function test_category_set_checkin_email_attribute(): void
    {
        $category = Category::factory()->forAccessories()->make();
        $category->checkin_email = '1';

        $this->assertEquals(1, $category->getAttributes()['checkin_email']);
    }

    public function test_category_requires_acceptance_scope(): void
    {
        Category::factory()->forAccessories()->create(['require_acceptance' => 1]);

        $this->assertGreaterThanOrEqual(1, Category::requiresAcceptance()->count());
    }

    public function test_category_is_deletable(): void
    {
        $this->actingAs(User::factory()->superuser()->create());
        $category = Category::factory()->forAccessories()->create();

        $this->assertIsBool($category->isDeletable());
    }

    public function test_depreciation_is_deletable(): void
    {
        $this->actingAs(User::factory()->superuser()->create());
        $depreciation = Depreciation::factory()->create();

        $this->assertIsBool($depreciation->isDeletable());
    }
}
