<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\CreateLicenseTool;
use App\Models\Category;
use App\Models\LicenseSeat;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class CreateLicenseToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->createLicenses()->create());
    }

    private function handle(array $args): ResponseFactory
    {
        return (new CreateLicenseTool)->handle(new Request($args));
    }

    public function test_creates_license_with_required_fields()
    {
        $category = Category::factory()->create(['category_type' => 'license']);

        $content = $this->handle([
            'name' => 'Test License',
            'seats' => 5,
            'category_id' => $category->id,
        ])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertDatabaseHas('licenses', ['name' => 'Test License', 'seats' => 5]);
    }

    public function test_response_includes_id_name_seats_and_category()
    {
        $category = Category::factory()->create(['category_type' => 'license']);

        $content = $this->handle([
            'name' => 'Response License',
            'seats' => 10,
            'category_id' => $category->id,
        ])->getStructuredContent();

        $this->assertArrayHasKey('id', $content);
        $this->assertEquals('Response License', $content['name']);
        $this->assertEquals(10, $content['seats']);
        $this->assertEquals($category->id, $content['category_id']);
    }

    public function test_creates_license_seats_automatically()
    {
        $category = Category::factory()->create(['category_type' => 'license']);

        $content = $this->handle([
            'name' => 'Seat Auto License',
            'seats' => 3,
            'category_id' => $category->id,
        ])->getStructuredContent();

        $licenseId = $content['id'];
        $this->assertEquals(3, LicenseSeat::where('license_id', $licenseId)->count());
    }

    public function test_creates_license_with_optional_fields()
    {
        $category = Category::factory()->create(['category_type' => 'license']);

        $this->handle([
            'name' => 'Full License',
            'seats' => 2,
            'category_id' => $category->id,
            'serial' => 'SN-FULL-001',
            'license_name' => 'Acme Corp',
            'license_email' => 'admin@acme.com',
            'purchase_date' => '2024-01-15',
            'purchase_cost' => 299.99,
            'expiration_date' => '2025-01-15',
            'maintained' => true,
            'reassignable' => false,
            'notes' => 'Important license',
        ]);

        $this->assertDatabaseHas('licenses', [
            'name' => 'Full License',
            'serial' => 'SN-FULL-001',
            'license_name' => 'Acme Corp',
            'license_email' => 'admin@acme.com',
        ]);
    }

    public function test_returns_error_when_name_missing()
    {
        $category = Category::factory()->create(['category_type' => 'license']);

        $this->assertTrue($this->handle([
            'seats' => 5,
            'category_id' => $category->id,
        ])->responses()->first()->isError());
    }

    public function test_returns_error_when_seats_missing()
    {
        $category = Category::factory()->create(['category_type' => 'license']);

        $this->assertTrue($this->handle([
            'name' => 'No Seats',
            'category_id' => $category->id,
        ])->responses()->first()->isError());
    }

    public function test_returns_error_when_category_missing()
    {
        $this->assertTrue($this->handle([
            'name' => 'No Category',
            'seats' => 5,
        ])->responses()->first()->isError());
    }

    public function test_returns_error_when_category_does_not_exist()
    {
        $this->assertTrue($this->handle([
            'name' => 'Bad Category',
            'seats' => 5,
            'category_id' => 999999,
        ])->responses()->first()->isError());
    }

    public function test_returns_error_when_user_lacks_permission()
    {
        $this->actingAs(User::factory()->create());
        $category = Category::factory()->create(['category_type' => 'license']);

        $this->assertTrue($this->handle([
            'name' => 'Blocked License',
            'seats' => 5,
            'category_id' => $category->id,
        ])->responses()->first()->isError());

        $this->assertDatabaseMissing('licenses', ['name' => 'Blocked License']);
    }
}
