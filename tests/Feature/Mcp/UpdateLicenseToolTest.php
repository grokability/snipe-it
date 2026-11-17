<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\UpdateLicenseTool;
use App\Models\Category;
use App\Models\License;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class UpdateLicenseToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->editLicenses()->create());
    }

    private function handle(array $args): ResponseFactory
    {
        return (new UpdateLicenseTool)->handle(new Request($args));
    }

    public function test_updates_license_by_id()
    {
        $license = License::factory()->create(['name' => 'Original Name']);

        $content = $this->handle([
            'id' => $license->id,
            'new_name' => 'Updated Name',
        ])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertDatabaseHas('licenses', ['id' => $license->id, 'name' => 'Updated Name']);
    }

    public function test_updates_license_by_name()
    {
        $license = License::factory()->create(['name' => 'Find By Name License']);

        $content = $this->handle([
            'name' => 'Find By Name License',
            'new_name' => 'Renamed License',
        ])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertDatabaseHas('licenses', ['id' => $license->id, 'name' => 'Renamed License']);
    }

    public function test_response_includes_id_name_and_seats()
    {
        $license = License::factory()->create(['seats' => 5]);

        $content = $this->handle([
            'id' => $license->id,
            'new_name' => 'Response Check License',
        ])->getStructuredContent();

        $this->assertArrayHasKey('id', $content);
        $this->assertEquals('Response Check License', $content['name']);
        $this->assertEquals(5, $content['seats']);
    }

    public function test_updates_multiple_fields()
    {
        $license = License::factory()->create();
        $category = Category::factory()->create(['category_type' => 'license']);

        $this->handle([
            'id' => $license->id,
            'category_id' => $category->id,
            'serial' => 'SN-NEW-001',
            'purchase_cost' => 199.99,
            'expiration_date' => '2026-12-31',
            'maintained' => true,
            'reassignable' => true,
            'notes' => 'Updated notes',
        ]);

        $this->assertDatabaseHas('licenses', [
            'id' => $license->id,
            'category_id' => $category->id,
            'serial' => 'SN-NEW-001',
        ]);
    }

    public function test_returns_error_when_not_found()
    {
        $this->assertTrue($this->handle(['id' => 999999, 'new_name' => 'Ghost'])->responses()->first()->isError());
    }

    public function test_returns_error_when_user_lacks_permission()
    {
        $license = License::factory()->create(['name' => 'Unchanged License']);
        $this->actingAs(User::factory()->create());

        $this->assertTrue($this->handle([
            'id' => $license->id,
            'new_name' => 'Should Not Change',
        ])->responses()->first()->isError());

        $this->assertDatabaseHas('licenses', ['id' => $license->id, 'name' => 'Unchanged License']);
    }
}
