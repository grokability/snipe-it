<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\ShowCompanyTool;
use App\Models\Company;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class ShowCompanyToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->viewCompanies()->create());
    }

    private function handle(array $args = []): ResponseFactory
    {
        return (new ShowCompanyTool)->handle(new Request($args));
    }

    public function test_returns_company_by_id()
    {
        $company = Company::factory()->create(['name' => 'Lookup By ID Corp']);

        $content = $this->handle(['id' => $company->id])->getStructuredContent();

        $this->assertEquals($company->id, $content['id']);
        $this->assertEquals('Lookup By ID Corp', $content['name']);
    }

    public function test_returns_company_by_name()
    {
        $company = Company::factory()->create(['name' => 'Lookup By Name Corp']);

        $content = $this->handle(['name' => 'Lookup By Name Corp'])->getStructuredContent();

        $this->assertEquals($company->id, $content['id']);
        $this->assertEquals('Lookup By Name Corp', $content['name']);
    }

    public function test_returns_error_when_no_identifier_provided()
    {
        $this->assertTrue($this->handle()->responses()->first()->isError());
    }

    public function test_returns_error_when_not_found()
    {
        $this->assertTrue($this->handle(['id' => 999999])->responses()->first()->isError());
    }

    public function test_returns_error_when_user_lacks_permission()
    {
        $company = Company::factory()->create();
        $this->actingAs(User::factory()->create());

        $this->assertTrue($this->handle(['id' => $company->id])->responses()->first()->isError());
    }
}
