<?php

use App\Helpers\Helper;

test('sameorigin url rejects offsite slo relaystate', function () {
    expect(Helper::sameOriginUrl('https://attacker.example.net/phish'))->toBeNull('A hostile RelayState URL from an IdP must be rejected by the same-origin guard before it reaches the session or the away() emission.');
});

test('sameorigin url accepts legitimate slo relaystate', function () {
    expect(Helper::sameOriginUrl(config('app.url').'/dashboard'))->not->toBeNull('A legitimate same-origin RelayState URL must survive the same-origin guard so an SLO round-trip can return the user to a Snipe-IT page.');
});
