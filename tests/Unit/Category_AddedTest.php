<?php

namespace Tests\Unit;

use App\Helpers\Helper;
use App\Models\Accessory;
use App\Models\Category;
use App\Models\Component;
use App\Models\Consumable;
use App\Models\License;
use App\Models\User;
use Tests\TestCase;

class Category_AddedTest extends TestCase
{
    public function test_fails_validation_for_invalid_category_type()
    {
        $category = Category::factory()->make([
            'name' => 'Bogus Category',
            'category_type' => 'not-a-real-type',
        ]);

        $this->assertFalse($category->isValid());
        $this->assertArrayHasKey('category_type', $category->getErrors()->toArray());
    }

    public function test_name_must_be_unique_within_the_same_category_type()
    {
        Category::factory()->create([
            'name' => 'Duplicate Name',
            'category_type' => 'asset',
        ]);

        $duplicate = Category::factory()->make([
            'name' => 'Duplicate Name',
            'category_type' => 'asset',
        ]);

        $this->assertFalse($duplicate->isValid());
        $this->assertArrayHasKey('name', $duplicate->getErrors()->toArray());
    }

    public function test_same_name_is_allowed_across_different_category_types()
    {
        Category::factory()->create([
            'name' => 'Shared Name',
            'category_type' => 'asset',
        ]);

        $other = Category::factory()->make([
            'name' => 'Shared Name',
            'category_type' => 'accessory',
        ]);

        $this->assertTrue($other->isValid());
    }

    public function test_get_eula_returns_categorys_own_eula_text()
    {
        $category = Category::factory()->create([
            'eula_text' => 'My Custom EULA',
            'use_default_eula' => false,
        ]);

        $this->assertEquals(Helper::parseEscapedMarkedown('My Custom EULA'), $category->getEula());
    }

    public function test_get_eula_falls_back_to_default_eula_when_enabled()
    {
        $this->settings->setEula('Default Company EULA');

        $category = Category::factory()->create([
            'eula_text' => '',
            'use_default_eula' => true,
        ]);

        $this->assertEquals(Helper::parseEscapedMarkedown('Default Company EULA'), $category->getEula());
    }

    public function test_get_eula_returns_null_when_no_local_or_default_eula()
    {
        $category = Category::factory()->withNoLocalOrGlobalEula()->create();

        $this->assertNull($category->getEula());
    }

    public function test_checkin_email_attribute_is_cast_to_boolean_integer()
    {
        $this->assertSame(1, Category::factory()->make(['checkin_email' => 'true'])->checkin_email);
        $this->assertSame(1, Category::factory()->make(['checkin_email' => '1'])->checkin_email);
        $this->assertSame(0, Category::factory()->make(['checkin_email' => 'false'])->checkin_email);
        $this->assertSame(0, Category::factory()->make(['checkin_email' => ''])->checkin_email);
    }

    public function test_requires_acceptance_scope_only_returns_categories_requiring_acceptance()
    {
        Category::factory()->create([
            'name' => 'Requires Acceptance',
            'require_acceptance' => true,
        ]);

        Category::factory()->create([
            'name' => 'No Acceptance Needed',
            'require_acceptance' => false,
        ]);

        $results = Category::requiresAcceptance()->get();

        $this->assertTrue($results->contains('name', 'Requires Acceptance'));
        $this->assertFalse($results->contains('name', 'No Acceptance Needed'));
    }

    public function test_item_count_returns_accessory_count_for_accessory_category()
    {
        $category = Category::factory()->accessoryKeyboardCategory()->create();
        Accessory::factory()->count(3)->create(['category_id' => $category->id]);

        $this->assertEquals(3, $category->fresh()->itemCount());
    }

    public function test_item_count_returns_consumable_count_for_consumable_category()
    {
        $category = Category::factory()->consumablePaperCategory()->create();
        Consumable::factory()->count(2)->create(['category_id' => $category->id]);

        $this->assertEquals(2, $category->fresh()->itemCount());
    }

    public function test_item_count_returns_component_count_for_component_category()
    {
        $category = Category::factory()->componentRamCategory()->create();
        Component::factory()->count(4)->create(['category_id' => $category->id]);

        $this->assertEquals(4, $category->fresh()->itemCount());
    }

    public function test_item_count_returns_license_count_for_license_category()
    {
        $category = Category::factory()->licenseOfficeCategory()->create();
        License::factory()->create(['category_id' => $category->id]);

        $this->assertEquals(1, $category->fresh()->itemCount());
    }

    public function test_is_deletable_returns_true_for_empty_category_when_user_has_permission()
    {
        $this->actingAs(User::factory()->deleteCategories()->create());

        $category = Category::factory()->accessoryKeyboardCategory()->create();

        $this->assertTrue($category->isDeletable());
    }

    public function test_is_deletable_returns_false_when_category_has_items()
    {
        $this->actingAs(User::factory()->deleteCategories()->create());

        $category = Category::factory()->accessoryKeyboardCategory()->create();
        Accessory::factory()->create(['category_id' => $category->id]);

        $this->assertFalse($category->fresh()->isDeletable());
    }

    public function test_is_deletable_returns_false_without_delete_permission()
    {
        $this->actingAs(User::factory()->create());

        $category = Category::factory()->accessoryKeyboardCategory()->create();

        $this->assertFalse($category->isDeletable());
    }
}
