<?php

use App\Http\Middleware\CheckUserIsActivated;
use App\Http\Middleware\EnforceApiTwoFactorEnrollment;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

test('deactivated user is rejected at the mcp route', function () {
    $user = User::factory()->create(['activated' => 0]);
    $this->actingAs($user);

    $request = Request::create('/mcp/snipe-it', 'POST');
    $request->headers->set('Authorization', 'Bearer fake-mcp-token');
    $request->setUserResolver(fn () => auth()->user());

    $response = app(CheckUserIsActivated::class)->handle($request, fn () => response('through', 200));

    expect($response->getStatusCode())->toBe(Response::HTTP_UNAUTHORIZED);
});

test('activated user passes through', function () {
    $user = User::factory()->create(['activated' => 1]);
    $this->actingAs($user);

    $request = Request::create('/mcp/snipe-it', 'POST');
    $request->headers->set('Authorization', 'Bearer fake-mcp-token');
    $request->setUserResolver(fn () => auth()->user());

    $response = app(CheckUserIsActivated::class)->handle($request, fn () => response('through', 200));

    expect($response->getStatusCode())->toBe(200);
});

test('user without 2fa is rejected when 2fa is required', function () {
    $this->settings->set(['two_factor_enabled' => '2']);

    $user = User::factory()->create([
        'activated' => 1,
        'two_factor_enrolled' => 0,
    ]);
    $this->actingAs($user);

    $request = Request::create('/mcp/snipe-it', 'POST');
    $request->setUserResolver(fn () => auth()->user());

    $response = app(EnforceApiTwoFactorEnrollment::class)->handle($request, fn () => response('through', 200));

    expect($response->getStatusCode())->toBe(Response::HTTP_FORBIDDEN);
});

test('user with 2fa enrolled passes through even when 2fa is required', function () {
    $this->settings->set(['two_factor_enabled' => '2']);

    $user = User::factory()->create([
        'activated' => 1,
        'two_factor_enrolled' => 1,
    ]);
    $this->actingAs($user);

    $request = Request::create('/mcp/snipe-it', 'POST');
    $request->setUserResolver(fn () => auth()->user());

    $response = app(EnforceApiTwoFactorEnrollment::class)->handle($request, fn () => response('through', 200));

    expect($response->getStatusCode())->toBe(200);
});

test('2fa enforcement skips when setting is off', function () {
    $this->settings->set(['two_factor_enabled' => '']);

    $user = User::factory()->create([
        'activated' => 1,
        'two_factor_enrolled' => 0,
    ]);
    $this->actingAs($user);

    $request = Request::create('/mcp/snipe-it', 'POST');
    $request->setUserResolver(fn () => auth()->user());

    $response = app(EnforceApiTwoFactorEnrollment::class)->handle($request, fn () => response('through', 200));

    expect($response->getStatusCode())->toBe(200);
});
