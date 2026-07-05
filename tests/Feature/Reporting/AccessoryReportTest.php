<?php

namespace Tests\Feature\Reporting;

use App\Models\Accessory;
use App\Models\User;
use Tests\TestCase;

class AccessoryReportTest extends TestCase
{
    public function test_requires_permission_to_export_accessory_report()
    {
        $this->actingAs(User::factory()->create())
            ->get(route('reports/export/accessories'))
            ->assertForbidden();
    }

    public function test_export_returns_csv_with_correct_headers()
    {
        $this->actingAs(User::factory()->canViewReports()->create())
            ->get(route('reports/export/accessories'))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=utf-8');
    }

    public function test_export_contains_accessory_data()
    {
        $accessory = Accessory::factory()->create(['name' => 'Test Widget', 'qty' => 10]);

        $this->actingAs(User::factory()->canViewReports()->create())
            ->get(route('reports/export/accessories'))
            ->assertOk()
            ->assertSeeTextInStreamedResponse('Test Widget');
    }

    public function test_export_contains_category_name()
    {
        $accessory = Accessory::factory()->create(['name' => 'Categorized Item', 'qty' => 5]);

        $this->actingAs(User::factory()->canViewReports()->create())
            ->get(route('reports/export/accessories'))
            ->assertOk()
            ->assertSeeTextInStreamedResponse($accessory->category->name);
    }

    public function test_header_row_appears_only_once()
    {
        Accessory::factory()->count(3)->create();

        $response = $this->actingAs(User::factory()->canViewReports()->create())
            ->get(route('reports/export/accessories'));

        $response->assertOk();

        $content = $response->streamedContent();
        $this->assertSame(1, substr_count($content, 'Accessory Name'));
    }

    public function test_export_reflects_qty_and_remaining()
    {
        $accessory = Accessory::factory()->create(['name' => 'Qty Check', 'qty' => 8]);

        $response = $this->actingAs(User::factory()->canViewReports()->create())
            ->get(route('reports/export/accessories'));

        $response->assertOk();

        $content = $response->streamedContent();
        $this->assertStringContainsString('Qty Check', $content);
        $this->assertStringContainsString('8', $content);
    }
}
