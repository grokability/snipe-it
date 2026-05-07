<?php

namespace Tests\Unit\Presenters;

use App\Models\License;
use Tests\TestCase;

class LicensePresenterTest extends TestCase
{
    public function test_dynamic_url()
    {
        $this->settings->set(['locale' => 'en-US']);

        $license = License::factory()->create();

        $this->assertEquals(
            'https://example.com/en-US',
            $license->present()->dynamicUrl('https://example.com/{LOCALE}')
        );
    }
}
