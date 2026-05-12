<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\ShowLicenseTool;
use App\Models\License;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class ShowLicenseToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->viewLicenses()->create());
    }

    private function handle(array $args): ResponseFactory
    {
        return (new ShowLicenseTool)->handle(new Request($args));
    }

    public function test_returns_license_by_id()
    {
        $license = License::factory()->create(['name' => 'Test License', 'seats' => 5]);

        $content = $this->handle(['id' => $license->id])->getStructuredContent();

        $this->assertEquals($license->id, $content['id']);
        $this->assertEquals('Test License', $content['name']);
        $this->assertEquals(5, $content['seats']);
    }

    public function test_returns_license_by_name()
    {
        $license = License::factory()->create(['name' => 'FindByName License']);

        $content = $this->handle(['name' => 'FindByName License'])->getStructuredContent();

        $this->assertEquals($license->id, $content['id']);
        $this->assertEquals('FindByName License', $content['name']);
    }

    public function test_response_includes_seat_counts()
    {
        $license = License::factory()->create(['seats' => 3, 'reassignable' => true]);
        $user = User::factory()->create();

        $seat = $license->freeSeat();
        $seat->assigned_to = $user->id;
        $seat->save();

        $content = $this->handle(['id' => $license->id])->getStructuredContent();

        $this->assertEquals(3, $content['seats']);
        $this->assertEquals(1, $content['assigned_seats']);
        $this->assertEquals(2, $content['free_seats']);
    }

    public function test_response_includes_key_fields()
    {
        $license = License::factory()->create([
            'name' => 'Full Details License',
            'serial' => 'SN-12345',
            'license_name' => 'Acme Corp',
            'license_email' => 'legal@acme.com',
        ]);

        $content = $this->handle(['id' => $license->id])->getStructuredContent();

        $this->assertArrayHasKey('serial', $content);
        $this->assertArrayHasKey('license_name', $content);
        $this->assertArrayHasKey('license_email', $content);
        $this->assertArrayHasKey('maintained', $content);
        $this->assertArrayHasKey('reassignable', $content);
        $this->assertArrayHasKey('category', $content);
        $this->assertArrayHasKey('category_id', $content);
        $this->assertEquals('SN-12345', $content['serial']);
        $this->assertEquals('Acme Corp', $content['license_name']);
    }

    public function test_returns_error_when_no_identifier_provided()
    {
        $this->assertTrue($this->handle([])->responses()->first()->isError());
    }

    public function test_returns_error_when_not_found()
    {
        $this->assertTrue($this->handle(['id' => 999999])->responses()->first()->isError());
    }

    public function test_returns_error_when_user_lacks_permission()
    {
        $license = License::factory()->create();
        $this->actingAs(User::factory()->create());

        $this->assertTrue($this->handle(['id' => $license->id])->responses()->first()->isError());
    }
}
