<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\DeleteAccessoryTool;
use App\Models\Accessory;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class DeleteAccessoryToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->deleteAccessories()->create());
    }

    private function handle(array $args): ResponseFactory
    {
        return (new DeleteAccessoryTool)->handle(new Request($args));
    }

    public function test_deletes_accessory_by_id()
    {
        $accessory = Accessory::factory()->create();

        $content = $this->handle(['id' => $accessory->id])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertSoftDeleted('accessories', ['id' => $accessory->id]);
    }

    public function test_deletes_accessory_by_name()
    {
        $accessory = Accessory::factory()->create(['name' => 'Delete By Name']);

        $content = $this->handle(['name' => 'Delete By Name'])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertSoftDeleted('accessories', ['id' => $accessory->id]);
    }

    public function test_response_includes_name()
    {
        $accessory = Accessory::factory()->create(['name' => 'Named Accessory']);

        $content = $this->handle(['id' => $accessory->id])->getStructuredContent();

        $this->assertEquals('Named Accessory', $content['name']);
    }

    public function test_returns_error_when_not_found()
    {
        $this->assertTrue($this->handle(['id' => 999999])->responses()->first()->isError());
    }

    public function test_returns_error_when_accessory_has_checkouts()
    {
        $user = User::factory()->create();
        $accessory = Accessory::factory()->checkedOutToUser($user)->create();

        $response = $this->handle(['id' => $accessory->id]);

        $this->assertTrue($response->responses()->first()->isError());
        $this->assertNotSoftDeleted('accessories', ['id' => $accessory->id]);
    }

    public function test_returns_error_when_user_lacks_permission()
    {
        $this->actingAs(User::factory()->create());
        $accessory = Accessory::factory()->create();

        $this->assertTrue($this->handle(['id' => $accessory->id])->responses()->first()->isError());
        $this->assertNotSoftDeleted('accessories', ['id' => $accessory->id]);
    }
}
