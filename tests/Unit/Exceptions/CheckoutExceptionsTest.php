<?php

namespace Tests\Unit\Exceptions;

use App\Exceptions\AssetNotRequestable;
use App\Exceptions\CheckoutNotAllowed;
use App\Exceptions\ItemStillHasAssets;
use App\Models\Asset;
use Tests\TestCase;

class CheckoutExceptionsTest extends TestCase
{
    public function test_checkout_not_allowed_message_and_string(): void
    {
        $e = new CheckoutNotAllowed('not allowed');

        $this->assertEquals('not allowed', $e->getMessage());
        $this->assertIsString((string) $e);
    }

    public function test_checkout_not_allowed_default_message(): void
    {
        $e = new CheckoutNotAllowed;

        $this->assertIsString((string) $e);
    }

    public function test_asset_not_requestable_is_throwable(): void
    {
        $asset = Asset::factory()->create();

        $this->expectException(AssetNotRequestable::class);
        throw new AssetNotRequestable($asset);
    }

    public function test_item_still_has_assets_is_throwable(): void
    {
        $this->expectException(ItemStillHasAssets::class);
        throw new ItemStillHasAssets('still has assets');
    }
}
