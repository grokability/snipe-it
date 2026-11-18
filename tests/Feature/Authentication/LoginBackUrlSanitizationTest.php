<?php

use App\Http\Controllers\Auth\LoginController;
use App\Models\User;
use Illuminate\Support\Facades\Session;

beforeEach(function () {
    // CheckForSetup middleware bounces /login to /setup without a
    // user in the DB. Create one so the constructor actually runs.
    User::factory()->create();
});

test('same origin referer is stashed as backurl on login form visit', function () {
    $safeReferer = config('app.url').'/hardware?status_type=Deployed';

    $this->from($safeReferer)
        ->get(route('login'))
        ->assertSessionHas('backUrl', $safeReferer);
});

test('offsite referer is not stashed as backurl on login form visit', function () {
    $this->from('https://evil.example.com/phish')
        ->get(route('login'))
        ->assertSessionMissing('backUrl');
});

test('javascript scheme referer is not stashed', function () {
    // URL::previous() falls back to the previous URL or, absent
    // that, session()['_previous']['url']. Seed it directly so we
    // can test what happens if a javascript: URL somehow reaches
    // URL::previous().
    $this->withSession(['_previous' => ['url' => 'javascript:alert(1)']])
        ->get(route('login'))
        ->assertSessionMissing('backUrl');
});

test('redirect to returns stashed same origin url', function () {
    // Construct first — the constructor writes its own backUrl from
    // URL::previous(). Then override the session slot with the
    // scenario we're actually asserting on.
    $controller = app(LoginController::class);

    $safeReferer = config('app.url').'/hardware?status_type=Deployed';
    Session::put('backUrl', $safeReferer);

    expect($controller->redirectTo())->toBe($safeReferer);
});

test('redirect to falls back when backurl is missing', function () {
    $controller = app(LoginController::class);

    Session::forget('backUrl');

    // Default is $this->redirectTo = '/' (see LoginController).
    expect($controller->redirectTo())->toBe('/');
});

test('redirect to ignores poisoned backurl', function () {
    // Defense-in-depth: if a future writer skips the write-side
    // gate, the read here still keeps the redirect on-domain.
    $controller = app(LoginController::class);

    Session::put('backUrl', 'https://evil.example.com/steal-session');

    expect($controller->redirectTo())->toBe('/');
});
