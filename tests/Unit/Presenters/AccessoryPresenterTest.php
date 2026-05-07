<?php

namespace Tests\Unit\Presenters;

use App\Models\Accessory;
use Tests\TestCase;

class AccessoryPresenterTest extends TestCase
{
    public function test_dynamic_url()
    {
        $this->settings->set(['locale' => 'en-US']);

        $accessory = Accessory::factory()->create(['model_number' => 'MN-123']);

        $this->assertEquals(
            'https://example.com/en-US/MN-123',
            $accessory->present()->dynamicUrl('https://example.com/{LOCALE}/{MODEL_NUMBER}')
        );
    }
}
