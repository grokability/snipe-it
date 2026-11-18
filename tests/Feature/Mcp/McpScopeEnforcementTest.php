<?php

use App\Enums\McpScope;
use App\Mcp\Tools\DeleteAssetTool;
use App\Mcp\Tools\ShowAssetTool;
use App\Models\Asset;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Laravel\Passport\Passport;

function callShow(array $args): ResponseFactory
{
    return (new ShowAssetTool)->handle(new Request($args));
}

function callDelete(array $args): ResponseFactory
{
    return (new DeleteAssetTool)->handle(new Request($args));
}

test('read scope is sufficient for show asset', function () {
    $asset = Asset::factory()->create();
    Passport::actingAs(User::factory()->viewAssets()->create(), [McpScope::Read->value]);

    $response = callShow(['id' => $asset->id]);

    expect($response->responses()->first()->isError())->toBeFalse();
});

test('write scope cannot call a read tool', function () {
    $asset = Asset::factory()->create();
    Passport::actingAs(User::factory()->viewAssets()->create(), [McpScope::Write->value]);

    $response = callShow(['id' => $asset->id]);

    expect($response->responses()->first()->isError())->toBeTrue();
});

test('delete scope cannot call a read tool even with full permissions', function () {
    $asset = Asset::factory()->create();
    Passport::actingAs(User::factory()->superuser()->create(), [McpScope::Delete->value]);

    $response = callShow(['id' => $asset->id]);

    expect($response->responses()->first()->isError())->toBeTrue();
});

test('read scope cannot call a delete tool', function () {
    $asset = Asset::factory()->create();
    Passport::actingAs(User::factory()->superuser()->create(), [McpScope::Read->value]);

    $response = callDelete(['id' => $asset->id]);

    expect($response->responses()->first()->isError())->toBeTrue();
    $this->assertDatabaseHas('assets', ['id' => $asset->id, 'deleted_at' => null]);
});

test('delete scope plus delete permission can delete', function () {
    $asset = Asset::factory()->create();
    Passport::actingAs(User::factory()->deleteAssets()->create(), [McpScope::Delete->value]);

    $response = callDelete(['id' => $asset->id]);

    expect($response->responses()->first()->isError())->toBeFalse();
    $this->assertSoftDeleted('assets', ['id' => $asset->id]);
});

test('multiple scopes on token allow matching tools', function () {
    $asset = Asset::factory()->create();
    Passport::actingAs(
        User::factory()->deleteAssets()->viewAssets()->create(),
        [McpScope::Read->value, McpScope::Delete->value],
    );

    expect(callShow(['id' => $asset->id])->responses()->first()->isError())->toBeFalse();
    expect(callDelete(['id' => $asset->id])->responses()->first()->isError())->toBeFalse();
});

test('acting as without passport skips scope check', function () {
    $asset = Asset::factory()->create();
    $this->actingAs(User::factory()->viewAssets()->create());

    $response = callShow(['id' => $asset->id]);

    expect($response->responses()->first()->isError())->toBeFalse();
});

test('token with no mcp scopes is refused', function () {
    $asset = Asset::factory()->create();
    Passport::actingAs(User::factory()->superuser()->create(), []);

    $response = callShow(['id' => $asset->id]);

    expect($response->responses()->first()->isError())->toBeTrue();
});

test('session scope alone cannot call a capability tool', function () {
    // McpScope::Session (`mcp:use`) is the connect-and-discover
    // scope. It passes the handshake middleware so tools/list
    // works, but per-tool exact-match still blocks capability
    // calls. This is the layered design: a session-only client
    // can enumerate but cannot execute.
    $asset = Asset::factory()->create();
    Passport::actingAs(User::factory()->viewAssets()->create(), [McpScope::Session->value]);

    $response = callShow(['id' => $asset->id]);

    expect($response->responses()->first()->isError())->toBeTrue();
});
