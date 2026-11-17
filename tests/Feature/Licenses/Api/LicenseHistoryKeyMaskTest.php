<?php

namespace Tests\Feature\Licenses\Api;

use App\Models\License;
use App\Models\User;
use Tests\TestCase;

class LicenseHistoryKeyMaskTest extends TestCase
{
    public function test_license_history_api_masks_old_and_new_serial_for_viewer_without_viewkeys(): void
    {
        $old = 'HISTORY-OLD-KEY-777';
        $new = 'HISTORY-NEW-KEY-888';

        $license = License::factory()->create(['serial' => $old]);
        $license->serial = $new;
        $license->save();

        $viewer = User::factory()->viewLicenses()->create();

        $response = $this->actingAsForApi($viewer)
            ->getJson(route('api.licenses.history', ['license' => $license->id]))
            ->assertOk();

        $body = $response->getContent();

        $this->assertStringNotContainsString($old, $body);
        $this->assertStringNotContainsString($new, $body);
        $this->assertStringContainsString(License::PRODUCT_KEY_MASK, $body);
    }

    public function test_license_history_api_shows_real_serial_for_viewkeys_holder(): void
    {
        $old = 'HISTORY-REAL-OLD-111';
        $new = 'HISTORY-REAL-NEW-222';

        $license = License::factory()->create(['serial' => $old]);
        $license->serial = $new;
        $license->save();

        $viewer = User::factory()->viewLicenses()->viewKeysLicenses()->create();

        $response = $this->actingAsForApi($viewer)
            ->getJson(route('api.licenses.history', ['license' => $license->id]))
            ->assertOk();

        $body = $response->getContent();

        $this->assertStringContainsString($old, $body);
        $this->assertStringContainsString($new, $body);
    }
}
