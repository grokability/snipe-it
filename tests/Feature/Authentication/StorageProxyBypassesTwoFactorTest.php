<?php

use App\Models\Setting;
use App\Models\User;

/**
 * When 2FA is universally required and the current user hasn't
 * completed it, /two-factor-enroll and related pages reference
 * branding images via <img src="/storage-proxy/…"> when PUBLIC_S3_PROXY
 * is enabled. Those image fetches must NOT be intercepted by
 * CheckForTwoFactor — otherwise the browser sees broken images AND
 * the middleware's setIntendedUrl() call clobbers the real intended
 * URL with a storage-proxy path, causing post-2FA redirects to land
 * on a logo URL instead of the dashboard. See issue #19457.
 */
test('storage proxy is not redirected for a two factor incomplete session', function () {
    // 2 = universal mandatory 2FA
    Setting::factory()->create();
    $settings = Setting::getSettings();
    $settings->two_factor_enabled = '2';
    $settings->save();

    $user = User::factory()->create([
        'two_factor_enrolled' => 0,
        'two_factor_secret' => null,
    ]);

    // Sanity check: an ordinary web route DOES get redirected
    // to /two-factor-enroll, so the middleware is actually
    // engaged in this test.
    $this->actingAs($user)
        ->get('/')
        ->assertRedirect(route('two-factor-enroll'));

    // The storage-proxy route responds directly (either 200 with
    // content, 304 not-modified, or 404 for a non-existent file)
    // rather than 302 to /two-factor-enroll. What we're actually
    // testing here is the absence of the 2FA-enrollment redirect.
    $response = $this->actingAs($user)
        ->get('/storage-proxy/setting-logo-999.svg');

    $this->assertNotEquals(
        route('two-factor-enroll'),
        $response->headers->get('Location'),
        'storage-proxy must not redirect image requests to the 2FA enroll page'
    );
    $this->assertNotSame(302, $response->status(), 'storage-proxy must not 302 for a 2FA-incomplete session');
});
