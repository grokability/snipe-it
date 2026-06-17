<?php

namespace Tests\Unit\Transformers;

use App\Http\Transformers\AssetsTransformer;
use App\Models\AccessoryCheckout;
use App\Models\User;
use Tests\TestCase;

class AssetsTransformerCheckoutTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->superuser()->create());
    }

    public function test_transform_checkedout_accessories(): void
    {
        $checkouts = AccessoryCheckout::factory()->count(2)->create();

        $result = (new AssetsTransformer)->transformCheckedoutAccessories($checkouts, $checkouts->count());

        $this->assertArrayHasKey('rows', $result);
        $this->assertArrayHasKey('total', $result);
    }

    public function test_transform_single_checkedout_accessory(): void
    {
        $checkout = AccessoryCheckout::factory()->create();

        $result = (new AssetsTransformer)->transformCheckedoutAccessory($checkout);

        $this->assertIsArray($result);
    }
}
