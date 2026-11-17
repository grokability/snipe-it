<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\DeleteLicenseTool;
use App\Models\License;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class DeleteLicenseToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->deleteLicenses()->create());
    }

    private function handle(array $args): ResponseFactory
    {
        return (new DeleteLicenseTool)->handle(new Request($args));
    }

    public function test_deletes_license_by_id()
    {
        $license = License::factory()->create();

        $content = $this->handle(['id' => $license->id])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertSoftDeleted('licenses', ['id' => $license->id]);
    }

    public function test_deletes_license_by_name()
    {
        $license = License::factory()->create(['name' => 'Delete By Name License']);

        $content = $this->handle(['name' => 'Delete By Name License'])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertSoftDeleted('licenses', ['id' => $license->id]);
    }

    public function test_response_includes_name()
    {
        $license = License::factory()->create(['name' => 'Named License']);

        $content = $this->handle(['id' => $license->id])->getStructuredContent();

        $this->assertEquals('Named License', $content['name']);
    }

    public function test_returns_error_when_not_found()
    {
        $this->assertTrue($this->handle(['id' => 999999])->responses()->first()->isError());
    }

    public function test_returns_error_when_seats_are_assigned()
    {
        $license = License::factory()->create(['seats' => 3, 'reassignable' => true]);
        $user = User::factory()->create();

        $seat = $license->freeSeat();
        $seat->assigned_to = $user->id;
        $seat->save();

        $response = $this->handle(['id' => $license->id]);

        $this->assertTrue($response->responses()->first()->isError());
        $this->assertNotSoftDeleted('licenses', ['id' => $license->id]);
    }

    public function test_returns_error_when_user_lacks_permission()
    {
        $license = License::factory()->create();
        $this->actingAs(User::factory()->create());

        $this->assertTrue($this->handle(['id' => $license->id])->responses()->first()->isError());
        $this->assertNotSoftDeleted('licenses', ['id' => $license->id]);
    }
}
