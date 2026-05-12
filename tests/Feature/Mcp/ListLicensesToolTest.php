<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\ListLicensesTool;
use App\Models\Category;
use App\Models\License;
use App\Models\Manufacturer;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class ListLicensesToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->viewLicenses()->create());
    }

    private function handle(array $args = []): ResponseFactory
    {
        return (new ListLicensesTool)->handle(new Request($args));
    }

    public function test_returns_total_licenses_and_pagination_metadata()
    {
        License::factory()->count(3)->create();

        $content = $this->handle()->getStructuredContent();

        $this->assertArrayHasKey('total', $content);
        $this->assertArrayHasKey('licenses', $content);
        $this->assertArrayHasKey('limit', $content);
        $this->assertArrayHasKey('offset', $content);
        $this->assertGreaterThanOrEqual(3, $content['total']);
    }

    public function test_each_license_includes_key_fields()
    {
        License::factory()->create();

        $content = $this->handle()->getStructuredContent();

        $license = $content['licenses'][0];
        $this->assertArrayHasKey('id', $license);
        $this->assertArrayHasKey('name', $license);
        $this->assertArrayHasKey('seats', $license);
        $this->assertArrayHasKey('free_seats', $license);
        $this->assertArrayHasKey('category', $license);
    }

    public function test_limit_controls_number_of_results_returned()
    {
        License::factory()->count(10)->create();

        $content = $this->handle(['limit' => 3])->getStructuredContent();

        $this->assertCount(3, $content['licenses']);
        $this->assertGreaterThan(3, $content['total']);
    }

    public function test_offset_skips_results_for_pagination()
    {
        License::factory()->count(10)->create();

        $page1 = $this->handle(['limit' => 4, 'offset' => 0])->getStructuredContent();
        $page2 = $this->handle(['limit' => 4, 'offset' => 4])->getStructuredContent();

        $ids1 = array_column($page1['licenses'], 'id');
        $ids2 = array_column($page2['licenses'], 'id');

        $this->assertEmpty(array_intersect($ids1, $ids2));
    }

    public function test_search_matches_on_name()
    {
        $target = License::factory()->create(['name' => 'UniqueLicenseXYZ']);
        License::factory()->create(['name' => 'Something Else']);

        $content = $this->handle(['search' => 'UniqueLicenseXYZ'])->getStructuredContent();
        $ids = array_column($content['licenses'], 'id');

        $this->assertContains($target->id, $ids);
    }

    public function test_filters_by_category_id()
    {
        $category = Category::factory()->create(['category_type' => 'license']);
        $matching = License::factory()->create(['category_id' => $category->id]);
        $other = License::factory()->create();

        $content = $this->handle(['category_id' => $category->id])->getStructuredContent();
        $ids = array_column($content['licenses'], 'id');

        $this->assertContains($matching->id, $ids);
        $this->assertNotContains($other->id, $ids);
    }

    public function test_filters_by_manufacturer_id()
    {
        $manufacturer = Manufacturer::factory()->create();
        $matching = License::factory()->create(['manufacturer_id' => $manufacturer->id]);
        $other = License::factory()->create(['manufacturer_id' => null]);

        $content = $this->handle(['manufacturer_id' => $manufacturer->id])->getStructuredContent();
        $ids = array_column($content['licenses'], 'id');

        $this->assertContains($matching->id, $ids);
        $this->assertNotContains($other->id, $ids);
    }

    public function test_returns_error_when_user_lacks_permission()
    {
        $this->actingAs(User::factory()->create());

        $this->assertTrue($this->handle()->responses()->first()->isError());
    }
}
