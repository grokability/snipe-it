<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\ListConsumablesTool;
use App\Models\Category;
use App\Models\Consumable;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class ListConsumablesToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->viewConsumables()->create());
    }

    private function handle(array $args = []): ResponseFactory
    {
        return (new ListConsumablesTool)->handle(new Request($args));
    }

    public function test_returns_total_and_pagination_metadata()
    {
        Consumable::factory()->count(3)->create();

        $content = $this->handle()->getStructuredContent();

        $this->assertArrayHasKey('total', $content);
        $this->assertArrayHasKey('offset', $content);
        $this->assertArrayHasKey('limit', $content);
        $this->assertArrayHasKey('consumables', $content);
        $this->assertGreaterThanOrEqual(3, $content['total']);
    }

    public function test_each_consumable_includes_key_fields()
    {
        Consumable::factory()->create(['name' => 'Test Ink Cartridge']);

        $content = $this->handle(['limit' => 500])->getStructuredContent();

        $consumable = collect($content['consumables'])->firstWhere('name', 'Test Ink Cartridge');

        $this->assertNotNull($consumable);
        $this->assertArrayHasKey('id', $consumable);
        $this->assertArrayHasKey('name', $consumable);
        $this->assertArrayHasKey('qty', $consumable);
        $this->assertArrayHasKey('category', $consumable);
    }

    public function test_limit_controls_results()
    {
        Consumable::factory()->count(10)->create();

        $content = $this->handle(['limit' => 3])->getStructuredContent();

        $this->assertCount(3, $content['consumables']);
        $this->assertEquals(3, $content['limit']);
    }

    public function test_filters_by_category_id()
    {
        $category = Category::factory()->create(['category_type' => 'consumable']);
        Consumable::factory()->create(['name' => 'Category Match', 'category_id' => $category->id]);
        Consumable::factory()->create(['name' => 'Other Consumable']);

        $content = $this->handle(['category_id' => $category->id])->getStructuredContent();

        $this->assertEquals(1, $content['total']);
        $this->assertEquals('Category Match', $content['consumables'][0]['name']);
    }

    public function test_returns_error_when_user_lacks_permission()
    {
        $this->actingAs(User::factory()->create());

        $this->assertTrue($this->handle()->responses()->first()->isError());
    }

    public function test_low_stock_only_returns_only_rows_at_or_below_min_amt()
    {
        $healthy = Consumable::factory()->create(['name' => 'Plenty', 'qty' => 100, 'min_amt' => 10]);
        $low = Consumable::factory()->create(['name' => 'Low', 'qty' => 2, 'min_amt' => 5]);

        $content = $this->handle(['low_stock_only' => true])->getStructuredContent();
        $names = array_column($content['consumables'], 'name');

        $this->assertContains('Low', $names);
        $this->assertNotContains('Plenty', $names);
    }

    public function test_filter_payload_forwards_to_text_search()
    {
        $match = Consumable::factory()->create(['name' => 'Toner Cartridge Cyan']);
        Consumable::factory()->create(['name' => 'Unrelated Item']);

        $content = $this->handle(['filter' => ['name' => 'Toner']])->getStructuredContent();
        $names = array_column($content['consumables'], 'name');

        $this->assertContains('Toner Cartridge Cyan', $names);
    }

    public function test_response_includes_qty_min_amt_remaining_and_notes()
    {
        Consumable::factory()->create(['name' => 'Response Field Check', 'qty' => 50, 'min_amt' => 5]);

        $content = $this->handle(['limit' => 500])->getStructuredContent();
        $row = collect($content['consumables'])->firstWhere('name', 'Response Field Check');

        $this->assertNotNull($row);
        $this->assertSame(50, $row['qty']);
        $this->assertSame(5, $row['min_amt']);
        $this->assertArrayHasKey('remaining', $row);
        $this->assertArrayHasKey('supplier', $row);
        $this->assertArrayHasKey('notes', $row);
    }

    public function test_default_hides_archived_consumables()
    {
        Consumable::factory()->create(['name' => 'Active Consumable']);
        Consumable::factory()->archived()->create(['name' => 'Retired Consumable']);

        $content = $this->handle()->getStructuredContent();

        $names = array_column($content['consumables'], 'name');
        $this->assertContains('Active Consumable', $names);
        $this->assertNotContains('Retired Consumable', $names);
    }

    public function test_include_archived_returns_both()
    {
        Consumable::factory()->create(['name' => 'Active Consumable']);
        Consumable::factory()->archived()->create(['name' => 'Retired Consumable']);

        $content = $this->handle(['include_archived' => true])->getStructuredContent();

        $names = array_column($content['consumables'], 'name');
        $this->assertContains('Active Consumable', $names);
        $this->assertContains('Retired Consumable', $names);
    }

    public function test_archived_only_returns_archived_rows_only()
    {
        Consumable::factory()->create(['name' => 'Active Consumable']);
        Consumable::factory()->archived()->create(['name' => 'Retired Consumable']);

        $content = $this->handle(['archived_only' => true])->getStructuredContent();

        $names = array_column($content['consumables'], 'name');
        $this->assertNotContains('Active Consumable', $names);
        $this->assertContains('Retired Consumable', $names);
    }

    public function test_each_row_includes_archived_flag()
    {
        Consumable::factory()->archived()->create(['name' => 'Retired']);

        $content = $this->handle(['archived_only' => true])->getStructuredContent();

        $row = collect($content['consumables'])->firstWhere('name', 'Retired');
        $this->assertNotNull($row);
        $this->assertArrayHasKey('archived', $row);
        $this->assertTrue($row['archived']);
    }

    public function test_low_stock_excludes_archived_even_when_include_archived_true()
    {
        Consumable::factory()->archived()->create([
            'name' => 'Archived Low Stock',
            'qty' => 1,
            'min_amt' => 5,
        ]);

        $content = $this->handle([
            'low_stock_only' => true,
            'include_archived' => true,
        ])->getStructuredContent();

        $names = array_column($content['consumables'], 'name');
        $this->assertNotContains('Archived Low Stock', $names);
    }
}
