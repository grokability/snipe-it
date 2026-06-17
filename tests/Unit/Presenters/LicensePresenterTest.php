<?php

namespace Tests\Unit\Presenters;

use App\Models\License;
use App\Models\User;
use Tests\TestCase;

class LicensePresenterTest extends TestCase
{
    public function test_name_url_renders_link(): void
    {
        $this->actingAs(User::factory()->superuser()->create());
        $license = License::factory()->create();

        $this->assertStringContainsString('<a href=', $license->present()->nameUrl());
        $this->assertStringContainsString('licenses/'.$license->id, $license->present()->nameUrl());
    }

    public function test_full_name_returns_name(): void
    {
        $this->actingAs(User::factory()->superuser()->create());
        $license = License::factory()->create(['name' => 'Acme License']);

        $this->assertEquals('Acme License', $license->present()->fullName());
    }

    public function test_view_url_points_to_licenses_show(): void
    {
        $this->actingAs(User::factory()->superuser()->create());
        $license = License::factory()->create();

        $this->assertEquals(route('licenses.show', $license->id), $license->present()->viewUrl());
    }

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
