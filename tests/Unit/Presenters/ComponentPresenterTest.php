<?php

namespace Tests\Unit\Presenters;

use App\Models\Component;
use Tests\TestCase;

class ComponentPresenterTest extends TestCase
{
    public function test_dynamic_url()
    {
        $this->settings->set(['locale' => 'en-US']);

        $component = Component::factory()->create([
            'serial' => 'SN-123',
            'model_number' => 'MN-123',
        ]);

        $this->assertEquals(
            'https://example.com/en-US/SN-123/MN-123',
            $component->present()->dynamicUrl('https://example.com/{LOCALE}/{SERIAL}/{MODEL_NUMBER}')
        );
    }
}
