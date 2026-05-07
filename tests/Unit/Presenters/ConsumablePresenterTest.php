<?php

namespace Tests\Unit\Presenters;

use App\Models\Consumable;
use Tests\TestCase;

class ConsumablePresenterTest extends TestCase
{
    public function test_dynamic_url()
    {
        $this->settings->set(['locale' => 'en-US']);

        $consumable = Consumable::factory()->create(['model_number' => 'MN-123']);

        $this->assertEquals(
            'https://example.com/en-US/MN-123',
            $consumable->present()->dynamicUrl('https://example.com/{LOCALE}/{MODEL_NUMBER}')
        );
    }
}
