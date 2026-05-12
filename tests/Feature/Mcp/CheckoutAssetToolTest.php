<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\CheckoutAssetTool;
use App\Models\Asset;
use App\Models\Location;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class CheckoutAssetToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->checkoutAssets()->create());
    }

    private function handle(array $args): ResponseFactory
    {
        return (new CheckoutAssetTool)->handle(new Request($args));
    }

    public function test_checks_out_asset_to_user_by_asset_tag()
    {
        $asset = Asset::factory()->create();
        $user = User::factory()->create();

        $content = $this->handle([
            'asset_tag' => $asset->asset_tag,
            'checkout_to_type' => 'user',
            'assigned_user' => $user->id,
        ])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertDatabaseHas('assets', [
            'id' => $asset->id,
            'assigned_to' => $user->id,
            'assigned_type' => User::class,
        ]);
    }

    public function test_checks_out_asset_to_user_by_numeric_id()
    {
        $asset = Asset::factory()->create();
        $user = User::factory()->create();

        $content = $this->handle([
            'id' => $asset->id,
            'checkout_to_type' => 'user',
            'assigned_user' => $user->id,
        ])->getStructuredContent();

        $this->assertTrue($content['success']);
    }

    public function test_checks_out_asset_to_location()
    {
        $asset = Asset::factory()->create();
        $location = Location::factory()->create();

        $content = $this->handle([
            'asset_tag' => $asset->asset_tag,
            'checkout_to_type' => 'location',
            'assigned_location' => $location->id,
        ])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertDatabaseHas('assets', [
            'id' => $asset->id,
            'assigned_to' => $location->id,
            'assigned_type' => Location::class,
        ]);
    }

    public function test_checks_out_asset_to_another_asset()
    {
        $asset = Asset::factory()->create();
        $target = Asset::factory()->create();

        $content = $this->handle([
            'asset_tag' => $asset->asset_tag,
            'checkout_to_type' => 'asset',
            'assigned_asset' => $target->id,
        ])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertDatabaseHas('assets', [
            'id' => $asset->id,
            'assigned_to' => $target->id,
            'assigned_type' => Asset::class,
        ]);
    }

    public function test_returns_error_when_asset_tag_not_found()
    {
        $this->assertTrue($this->handle([
            'asset_tag' => 'DOES-NOT-EXIST',
            'checkout_to_type' => 'user',
            'assigned_user' => User::factory()->create()->id,
        ])->responses()->first()->isError());
    }

    public function test_returns_error_when_asset_already_checked_out()
    {
        $existingUser = User::factory()->create();
        $asset = Asset::factory()->assignedToUser($existingUser)->create();
        $newUser = User::factory()->create();

        $this->assertTrue($this->handle([
            'asset_tag' => $asset->asset_tag,
            'checkout_to_type' => 'user',
            'assigned_user' => $newUser->id,
        ])->responses()->first()->isError());

        $this->assertDatabaseHas('assets', [
            'id' => $asset->id,
            'assigned_to' => $existingUser->id,
        ]);
    }

    public function test_returns_error_when_target_user_not_found()
    {
        $asset = Asset::factory()->create();

        $this->assertTrue($this->handle([
            'asset_tag' => $asset->asset_tag,
            'checkout_to_type' => 'user',
            'assigned_user' => 99999,
        ])->responses()->first()->isError());
    }

    public function test_returns_error_when_target_location_not_found()
    {
        $asset = Asset::factory()->create();

        $this->assertTrue($this->handle([
            'asset_tag' => $asset->asset_tag,
            'checkout_to_type' => 'location',
            'assigned_location' => 99999,
        ])->responses()->first()->isError());
    }

    public function test_response_includes_asset_tag_and_target_info()
    {
        $asset = Asset::factory()->create();
        $user = User::factory()->create();

        $content = $this->handle([
            'asset_tag' => $asset->asset_tag,
            'checkout_to_type' => 'user',
            'assigned_user' => $user->id,
        ])->getStructuredContent();

        $this->assertEquals($asset->asset_tag, $content['asset_tag']);
        $this->assertEquals('user', $content['checked_out_to_type']);
        $this->assertEquals($user->id, $content['checked_out_to_id']);
    }

    public function test_returns_error_when_user_lacks_permission()
    {
        $this->actingAs(User::factory()->create());
        $asset = Asset::factory()->create();
        $user = User::factory()->create();

        $this->assertTrue($this->handle([
            'asset_tag' => $asset->asset_tag,
            'checkout_to_type' => 'user',
            'assigned_user' => $user->id,
        ])->responses()->first()->isError());
    }
}
