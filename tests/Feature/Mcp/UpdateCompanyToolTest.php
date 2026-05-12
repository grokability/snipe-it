<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\UpdateCompanyTool;
use App\Models\Company;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class UpdateCompanyToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->editCompanies()->create());
    }

    private function handle(array $args = []): ResponseFactory
    {
        return (new UpdateCompanyTool)->handle(new Request($args));
    }

    public function test_updates_company_by_id()
    {
        $company = Company::factory()->create(['name' => 'Original Corp']);

        $content = $this->handle([
            'id' => $company->id,
            'new_name' => 'Updated Corp',
        ])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertDatabaseHas('companies', ['id' => $company->id, 'name' => 'Updated Corp']);
    }

    public function test_updates_company_by_name()
    {
        $company = Company::factory()->create(['name' => 'Find By Name Corp']);

        $content = $this->handle([
            'name' => 'Find By Name Corp',
            'new_name' => 'Renamed Corp',
        ])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertDatabaseHas('companies', ['id' => $company->id, 'name' => 'Renamed Corp']);
    }

    public function test_renames_via_new_name()
    {
        $company = Company::factory()->create(['name' => 'Before Rename']);

        $content = $this->handle([
            'id' => $company->id,
            'new_name' => 'After Rename',
        ])->getStructuredContent();

        $this->assertEquals('After Rename', $content['name']);
        $this->assertDatabaseHas('companies', ['id' => $company->id, 'name' => 'After Rename']);
    }

    public function test_returns_error_when_not_found()
    {
        $this->assertTrue($this->handle(['id' => 999999, 'new_name' => 'Ghost Corp'])->responses()->first()->isError());
    }

    public function test_returns_error_when_no_identifier_provided()
    {
        $this->assertTrue($this->handle(['new_name' => 'X'])->responses()->first()->isError());
    }

    public function test_returns_error_when_user_lacks_permission()
    {
        $company = Company::factory()->create(['name' => 'Unchanged Corp']);
        $this->actingAs(User::factory()->create());

        $this->assertTrue($this->handle([
            'id' => $company->id,
            'new_name' => 'Should Not Change',
        ])->responses()->first()->isError());

        $this->assertDatabaseHas('companies', ['id' => $company->id, 'name' => 'Unchanged Corp']);
    }
}
