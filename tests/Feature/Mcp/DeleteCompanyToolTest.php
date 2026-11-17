<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\DeleteCompanyTool;
use App\Models\Company;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class DeleteCompanyToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->deleteCompanies()->create());
    }

    private function handle(array $args = []): ResponseFactory
    {
        return (new DeleteCompanyTool)->handle(new Request($args));
    }

    public function test_deletes_company_by_id()
    {
        $company = Company::factory()->create();

        $content = $this->handle(['id' => $company->id])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertSoftDeleted('companies', ['id' => $company->id]);
    }

    public function test_deletes_company_by_name()
    {
        $company = Company::factory()->create(['name' => 'Delete By Name Corp']);

        $content = $this->handle(['name' => 'Delete By Name Corp'])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertSoftDeleted('companies', ['id' => $company->id]);
    }

    public function test_response_includes_name()
    {
        $company = Company::factory()->create(['name' => 'Named Company']);

        $content = $this->handle(['id' => $company->id])->getStructuredContent();

        $this->assertEquals('Named Company', $content['name']);
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
        $this->assertNotSoftDeleted('companies', ['id' => $company->id]);
    }
}
