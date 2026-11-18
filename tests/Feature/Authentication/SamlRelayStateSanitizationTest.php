<?php

use App\Models\User;

beforeEach(function () {
    // CheckForSetup middleware redirects to /setup when there are no
    // users. Creating one lets acs actually run.
    User::factory()->create();
});

test('offsite relaystate is not written to intended url', function () {
    session()->forget('url.intended');

    $this->post(route('saml.acs'), [
        'RelayState' => 'https://evil.example.com/steal-session',
        'SAMLResponse' => 'invalid',
    ]);

    expect(session('url.intended'))->toBeNull('Off-site RelayState must not become url.intended');
});

test('javascript scheme relaystate is not written to intended url', function () {
    session()->forget('url.intended');

    $this->post(route('saml.acs'), [
        'RelayState' => 'javascript:alert(document.cookie)',
        'SAMLResponse' => 'invalid',
    ]);

    expect(session('url.intended'))->toBeNull();
});

test('scheme relative offsite relaystate is not written to intended url', function () {
    session()->forget('url.intended');

    $this->post(route('saml.acs'), [
        'RelayState' => '//evil.example.com/steal-session',
        'SAMLResponse' => 'invalid',
    ]);

    expect(session('url.intended'))->toBeNull();
});

test('same origin relaystate is preserved', function () {
    $safeRelay = config('app.url').'/hardware?status=1';

    $this->post(route('saml.acs'), [
        'RelayState' => $safeRelay,
        'SAMLResponse' => 'invalid',
    ]);

    expect(session('url.intended'))->toBe($safeRelay);
});

test('crlf in relaystate is stripped', function () {
    $poisoned = config('app.url')."/hardware\r\nSet-Cookie: attacker=1";

    $this->post(route('saml.acs'), [
        'RelayState' => $poisoned,
        'SAMLResponse' => 'invalid',
    ]);

    // CR/LF stripped so this can't split the HTTP response into a
    // header-injection primitive. Remaining text is harmless once
    // the line breaks are gone.
    $stored = session('url.intended');
    expect($stored)->not->toBeNull();
    $this->assertStringNotContainsString("\r", $stored);
    $this->assertStringNotContainsString("\n", $stored);
});
