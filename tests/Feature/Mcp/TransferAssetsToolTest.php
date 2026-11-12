<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\TransferAssetsTool;
use App\Models\Accessory;
use App\Models\AccessoryCheckout;
use App\Models\Asset;
use App\Models\License;
use App\Models\LicenseSeat;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class TransferAssetsToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->superuser()->create());
    }

    private function handle(array $args): ResponseFactory
    {
        return resolve(TransferAssetsTool::class)->handle(new Request($args));
    }

    public function test_dry_run_returns_preview_without_moving_anything()
    {
        $from = User::factory()->create(['username' => 'leaver']);
        $to = User::factory()->create(['username' => 'receiver']);
        $asset = Asset::factory()->assignedToUser($from)->create();

        $content = $this->handle(['from_username' => 'leaver', 'to_username' => 'receiver'])
            ->getStructuredContent();

        $this->assertTrue($content['dry_run']);
        $this->assertCount(1, $content['preview']['assets_to_transfer']);
        $this->assertDatabaseHas('assets', ['id' => $asset->id, 'assigned_to' => $from->id]);
    }

    public function test_dry_run_is_default()
    {
        User::factory()->create(['username' => 'a']);
        User::factory()->create(['username' => 'b']);

        $content = $this->handle(['from_username' => 'a', 'to_username' => 'b'])->getStructuredContent();

        $this->assertTrue($content['dry_run']);
    }

    public function test_execute_moves_assets()
    {
        $from = User::factory()->create(['username' => 'leaver']);
        $to = User::factory()->create(['username' => 'receiver']);
        $asset = Asset::factory()->assignedToUser($from)->create();

        $this->handle(['from_username' => 'leaver', 'to_username' => 'receiver', 'dry_run' => false]);

        $fresh = $asset->fresh();
        $this->assertSame($to->id, $fresh->assigned_to);
    }

    public function test_execute_moves_license_seats()
    {
        $from = User::factory()->create(['username' => 'leaver']);
        $to = User::factory()->create(['username' => 'receiver']);
        $license = License::factory()->create();
        $seat = LicenseSeat::factory()->create([
            'license_id' => $license->id,
            'assigned_to' => $from->id,
        ]);

        $this->handle(['from_username' => 'leaver', 'to_username' => 'receiver', 'dry_run' => false]);

        $this->assertSame($to->id, $seat->fresh()->assigned_to);
    }

    public function test_execute_moves_accessory_checkouts()
    {
        $from = User::factory()->create(['username' => 'leaver']);
        $to = User::factory()->create(['username' => 'receiver']);
        $accessory = Accessory::factory()->create();
        $checkout = AccessoryCheckout::factory()->create([
            'accessory_id' => $accessory->id,
            'assigned_type' => User::class,
            'assigned_to' => $from->id,
        ]);

        $this->handle(['from_username' => 'leaver', 'to_username' => 'receiver', 'dry_run' => false]);

        $this->assertSame($to->id, $checkout->fresh()->assigned_to);
    }

    public function test_rejects_same_user_transfer()
    {
        User::factory()->create(['username' => 'solo']);

        $response = $this->handle(['from_username' => 'solo', 'to_username' => 'solo']);

        $this->assertTrue($response->responses()->first()->isError());
    }

    public function test_returns_ambiguous_match_set_when_from_name_resolves_to_multiple()
    {
        User::factory()->count(2)->create(['first_name' => 'John', 'last_name' => 'Doe']);
        User::factory()->create(['username' => 'receiver']);

        $content = $this->handle([
            'from_first_name' => 'John',
            'from_last_name' => 'Doe',
            'to_username' => 'receiver',
        ])->getStructuredContent();

        $this->assertFalse($content['success']);
        $this->assertSame('ambiguous_user', $content['error']);
        $this->assertSame('from', $content['side']);
    }

    public function test_returns_ambiguous_match_set_when_to_name_resolves_to_multiple()
    {
        User::factory()->create(['username' => 'source']);
        User::factory()->count(2)->create(['first_name' => 'Jane', 'last_name' => 'Smith']);

        $content = $this->handle([
            'from_username' => 'source',
            'to_first_name' => 'Jane',
            'to_last_name' => 'Smith',
        ])->getStructuredContent();

        $this->assertFalse($content['success']);
        $this->assertSame('to', $content['side']);
    }

    public function test_returns_error_when_from_user_not_found()
    {
        User::factory()->create(['username' => 'receiver']);

        $response = $this->handle(['from_username' => 'nobody', 'to_username' => 'receiver']);

        $this->assertTrue($response->responses()->first()->isError());
    }

    public function test_preview_captures_the_before_state_on_both_sides()
    {
        $from = User::factory()->create(['username' => 'leaver']);
        $to = User::factory()->create(['username' => 'receiver']);

        $content = $this->handle(['from_username' => 'leaver', 'to_username' => 'receiver'])
            ->getStructuredContent();

        $this->assertSame($from->id, $content['preview']['from']['id']);
        $this->assertSame($to->id, $content['preview']['to']['id']);
    }
}
