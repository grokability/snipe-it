<?php

namespace Tests\Unit\Models;

use App\Models\Category;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

/**
 * GHSA-8rcp-h6xj-5v7h: write-time hex-only validation on tag_color
 * so a CSS-injection payload cannot land in the DB in the first
 * place. The same rule shape ships on Company, Department, Location,
 * Manufacturer, Supplier and MaintenanceType. Category is the
 * representative here because the advisory PoC targeted it.
 */
#[Group('security')]
class CategoryTagColorValidationTest extends TestCase
{
    public function test_valid_hex_tag_color_passes_validation(): void
    {
        $category = Category::factory()->make([
            'tag_color' => '#aabbcc',
        ]);

        $this->assertTrue($category->isValid());
    }

    public function test_six_digit_hex_variants_pass(): void
    {
        foreach (['#fff', '#ffff', '#ffffff', '#ffffffff', '#AABBCC'] as $color) {
            $category = Category::factory()->make(['tag_color' => $color]);
            $this->assertTrue($category->isValid(), "Expected {$color} to pass validation");
        }
    }

    public function test_null_tag_color_passes_validation(): void
    {
        $category = Category::factory()->make(['tag_color' => null]);

        $this->assertTrue($category->isValid());
    }

    public function test_css_injection_payload_is_rejected(): void
    {
        $category = Category::factory()->make([
            'tag_color' => 'red; position:fixed; top:0; left:0; background:url(http://evil.example/)',
        ]);

        $this->assertFalse($category->isValid());
        $this->assertArrayHasKey('tag_color', $category->getErrors()->toArray());
    }

    public function test_color_keyword_is_rejected(): void
    {
        $category = Category::factory()->make(['tag_color' => 'red']);

        $this->assertFalse($category->isValid());
    }

    public function test_rgb_function_is_rejected(): void
    {
        $category = Category::factory()->make(['tag_color' => 'rgb(255, 0, 0)']);

        $this->assertFalse($category->isValid());
    }

    public function test_malformed_hex_is_rejected(): void
    {
        foreach (['#x', '#aabbccdde', 'aabbcc', '#aabbcc;'] as $color) {
            $category = Category::factory()->make(['tag_color' => $color]);
            $this->assertFalse($category->isValid(), "Expected {$color} to fail validation");
        }
    }
}
