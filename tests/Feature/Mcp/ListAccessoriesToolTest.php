<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\ListAccessoriesTool;
use App\Models\Accessory;
use App\Models\AccessoryCheckout;
use App\Models\Category;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class ListAccessoriesToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->viewAccessories()->create());
    }

    private function handle(array $args = []): ResponseFactory
    {
        return (new ListAccessoriesTool)->handle(new Request($args));
    }

    public function test_returns_total_and_pagination_metadata()
    {
        Accessory::factory()->count(3)->create();

        $content = $this->handle()->getStructuredContent();

        $this->assertArrayHasKey('total', $content);
        $this->assertArrayHasKey('offset', $content);
        $this->assertArrayHasKey('limit', $content);
        $this->assertArrayHasKey('accessories', $content);
        $this->assertGreaterThanOrEqual(3, $content['total']);
    }

    public function test_each_accessory_includes_qty_min_amt_and_remaining()
    {
        Accessory::factory()->create(['name' => 'Test Mouse', 'qty' => 10, 'min_amt' => 2]);

        $content = $this->handle(['limit' => 500])->getStructuredContent();

        $row = collect($content['accessories'])->firstWhere('name', 'Test Mouse');

        $this->assertNotNull($row);
        $this->assertSame(10, $row['qty']);
        $this->assertSame(2, $row['min_amt']);
        $this->assertArrayHasKey('remaining', $row);
        $this->assertArrayHasKey('supplier', $row);
        $this->assertArrayHasKey('notes', $row);
    }

    public function test_filters_by_category_id()
    {
        $category = Category::factory()->create(['category_type' => 'accessory']);
        Accessory::factory()->create(['name' => 'Category Match', 'category_id' => $category->id]);
        Accessory::factory()->create(['name' => 'Other Accessory']);

        $content = $this->handle(['category_id' => $category->id])->getStructuredContent();

        $this->assertSame(1, $content['total']);
        $this->assertSame('Category Match', $content['accessories'][0]['name']);
    }

    public function test_low_stock_only_returns_only_rows_at_or_below_min_amt()
    {
        $healthy = Accessory::factory()->create([
            'name' => 'Plenty In Stock',
            'qty' => 100,
            'min_amt' => 10,
        ]);
        $low = Accessory::factory()->create([
            'name' => 'Low Stock',
            'qty' => 3,
            'min_amt' => 5,
        ]);

        $content = $this->handle(['low_stock_only' => true])->getStructuredContent();

        $names = array_column($content['accessories'], 'name');
        $this->assertContains('Low Stock', $names);
        $this->assertNotContains('Plenty In Stock', $names);
    }

    public function test_low_stock_only_accounts_for_active_checkouts()
    {
        $accessory = Accessory::factory()->create([
            'name' => 'Checked Out Low',
            'qty' => 5,
            'min_amt' => 2,
        ]);

        // Check out 4 units. Remaining (5-4=1) is below min_amt (2) so it
        // should be reported as low stock even though raw qty > min_amt.
        AccessoryCheckout::factory()->count(4)->create([
            'accessory_id' => $accessory->id,
            'assigned_type' => User::class,
            'assigned_to' => User::factory()->create()->id,
        ]);

        $content = $this->handle(['low_stock_only' => true])->getStructuredContent();

        $names = array_column($content['accessories'], 'name');
        $this->assertContains('Checked Out Low', $names);
    }

    public function test_default_hides_archived_accessories()
    {
        Accessory::factory()->create(['name' => 'Active Accessory']);
        Accessory::factory()->archived()->create(['name' => 'Retired Accessory']);

        $content = $this->handle()->getStructuredContent();

        $names = array_column($content['accessories'], 'name');
        $this->assertContains('Active Accessory', $names);
        $this->assertNotContains('Retired Accessory', $names);
    }

    public function test_include_archived_returns_both()
    {
        Accessory::factory()->create(['name' => 'Active Accessory']);
        Accessory::factory()->archived()->create(['name' => 'Retired Accessory']);

        $content = $this->handle(['include_archived' => true])->getStructuredContent();

        $names = array_column($content['accessories'], 'name');
        $this->assertContains('Active Accessory', $names);
        $this->assertContains('Retired Accessory', $names);
    }

    public function test_archived_only_returns_archived_rows_only()
    {
        Accessory::factory()->create(['name' => 'Active Accessory']);
        Accessory::factory()->archived()->create(['name' => 'Retired Accessory']);

        $content = $this->handle(['archived_only' => true])->getStructuredContent();

        $names = array_column($content['accessories'], 'name');
        $this->assertNotContains('Active Accessory', $names);
        $this->assertContains('Retired Accessory', $names);
    }

    public function test_each_accessory_row_includes_archived_flag()
    {
        Accessory::factory()->archived()->create(['name' => 'Retired']);

        $content = $this->handle(['archived_only' => true])->getStructuredContent();

        $row = collect($content['accessories'])->firstWhere('name', 'Retired');
        $this->assertNotNull($row);
        $this->assertArrayHasKey('archived', $row);
        $this->assertTrue($row['archived']);
    }

    public function test_low_stock_excludes_archived_even_when_include_archived_true()
    {
        Accessory::factory()->archived()->create([
            'name' => 'Archived Low Stock',
            'qty' => 1,
            'min_amt' => 5,
        ]);

        $content = $this->handle([
            'low_stock_only' => true,
            'include_archived' => true,
        ])->getStructuredContent();

        $names = array_column($content['accessories'], 'name');
        $this->assertNotContains('Archived Low Stock', $names);
    }

    public function test_limit_controls_results()
    {
        Accessory::factory()->count(5)->create();

        $content = $this->handle(['limit' => 2])->getStructuredContent();

        $this->assertCount(2, $content['accessories']);
        $this->assertSame(2, $content['limit']);
    }

    public function test_returns_error_when_user_lacks_permission()
    {
        $this->actingAs(User::factory()->create());

        $this->assertTrue($this->handle()->responses()->first()->isError());
    }
}
