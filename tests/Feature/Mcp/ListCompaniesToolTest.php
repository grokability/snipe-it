<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\ListCompaniesTool;
use App\Models\Company;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class ListCompaniesToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->viewCompanies()->create());
    }

    private function handle(array $args = []): ResponseFactory
    {
        return (new ListCompaniesTool)->handle(new Request($args));
    }

    public function test_returns_total_and_pagination_metadata()
    {
        Company::factory()->count(3)->create();

        $content = $this->handle()->getStructuredContent();

        $this->assertGreaterThanOrEqual(3, $content['total']);
        $this->assertArrayHasKey('companies', $content);
        $this->assertArrayHasKey('limit', $content);
        $this->assertArrayHasKey('offset', $content);
    }

    public function test_each_company_includes_key_fields()
    {
        Company::factory()->create();

        $content = $this->handle()->getStructuredContent();

        $this->assertNotEmpty($content['companies']);
        $company = $content['companies'][0];
        $this->assertArrayHasKey('id', $company);
        $this->assertArrayHasKey('name', $company);
    }

    public function test_limit_controls_results()
    {
        Company::factory()->count(10)->create();

        $content = $this->handle(['limit' => 3])->getStructuredContent();

        $this->assertCount(3, $content['companies']);
        $this->assertGreaterThan(3, $content['total']);
    }

    public function test_returns_error_when_user_lacks_permission()
    {
        $this->actingAs(User::factory()->create());

        $this->assertTrue($this->handle()->responses()->first()->isError());
    }
}
