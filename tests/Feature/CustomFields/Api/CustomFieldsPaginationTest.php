<?php

namespace Tests\Feature\CustomFields\Api;

use App\Models\CustomField;
use App\Models\User;
use Tests\TestCase;

/**
 * Regression coverage for GitHub #20033: /api/v1/fields emitted
 * pagination metadata but returned every row, ignoring ?limit and
 * ?page. Mirrors the pattern established for #19334.
 */
class CustomFieldsPaginationTest extends TestCase
{
    public function test_fields_endpoint_paginates_rows_and_reports_accurate_metadata(): void
    {
        $this->markIncompleteIfMySQL('Custom Fields tests do not work on MySQL');

        CustomField::query()->delete();
        CustomField::factory()->count(7)->create();

        $response = $this->actingAsForApi(User::factory()->viewCustomFields()->create())
            ->getJson(route('api.customfields.index', ['limit' => 3, 'page' => 2]))
            ->assertOk()
            ->json();

        $this->assertSame(7, $response['total']);
        $this->assertCount(3, $response['rows']);
        $this->assertSame(3, $response['per_page']);
        $this->assertSame(2, $response['current_page']);
        $this->assertSame(3, $response['total_pages']);
        $this->assertNotNull($response['next_page_url']);
        $this->assertNotNull($response['prev_page_url']);
    }

    public function test_fields_endpoint_last_page_returns_remainder_and_no_next_url(): void
    {
        $this->markIncompleteIfMySQL('Custom Fields tests do not work on MySQL');

        CustomField::query()->delete();
        CustomField::factory()->count(7)->create();

        $response = $this->actingAsForApi(User::factory()->viewCustomFields()->create())
            ->getJson(route('api.customfields.index', ['limit' => 3, 'page' => 3]))
            ->assertOk()
            ->json();

        $this->assertSame(7, $response['total']);
        $this->assertCount(1, $response['rows']);
        $this->assertSame(3, $response['current_page']);
        $this->assertNull($response['next_page_url']);
        $this->assertNotNull($response['prev_page_url']);
    }

    public function test_fields_endpoint_offset_param_also_works(): void
    {
        $this->markIncompleteIfMySQL('Custom Fields tests do not work on MySQL');

        CustomField::query()->delete();
        CustomField::factory()->count(7)->create();

        $response = $this->actingAsForApi(User::factory()->viewCustomFields()->create())
            ->getJson(route('api.customfields.index', ['limit' => 3, 'offset' => 6]))
            ->assertOk()
            ->json();

        $this->assertSame(7, $response['total']);
        $this->assertCount(1, $response['rows']);
    }
}
