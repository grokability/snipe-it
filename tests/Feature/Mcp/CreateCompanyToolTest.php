<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\CreateCompanyTool;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class CreateCompanyToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->createCompanies()->create());
    }

    private function handle(array $args = []): ResponseFactory
    {
        return (new CreateCompanyTool)->handle(new Request($args));
    }

    public function test_creates_company_with_required_fields()
    {
        $content = $this->handle(['name' => 'Test Company LLC'])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertDatabaseHas('companies', ['name' => 'Test Company LLC']);
    }

    public function test_response_includes_id_and_name()
    {
        $content = $this->handle(['name' => 'Response Company Inc'])->getStructuredContent();

        $this->assertArrayHasKey('id', $content);
        $this->assertEquals('Response Company Inc', $content['name']);
    }

    public function test_creates_with_optional_fields()
    {
        $this->handle([
            'name' => 'Full Company Corp',
            'phone' => '555-1234567',
            'fax' => '555-7654321',
            'email' => 'company@example.com',
        ]);

        $this->assertDatabaseHas('companies', [
            'name' => 'Full Company Corp',
            'phone' => '555-1234567',
            'fax' => '555-7654321',
            'email' => 'company@example.com',
        ]);
    }

    public function test_returns_error_when_name_missing()
    {
        $this->assertTrue($this->handle([])->responses()->first()->isError());
    }

    public function test_returns_error_when_user_lacks_permission()
    {
        $this->actingAs(User::factory()->create());

        $this->assertTrue($this->handle(['name' => 'Blocked Company'])->responses()->first()->isError());
        $this->assertDatabaseMissing('companies', ['name' => 'Blocked Company']);
    }
}
