<?php

use App\Mcp\Tools\GetActivityLogTool;
use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->superuser()->create());
});

function getActivityLogTool(array $args = []): ResponseFactory
{
    return (new GetActivityLogTool)->handle(new Request($args));
}

test('returns activity log', function () {
    $asset = Asset::factory()->laptopZenbook()->create();
    $log = new Actionlog;
    $log->item_type = Asset::class;
    $log->item_id = $asset->id;
    $log->action_type = 'update';
    $log->target_id = auth()->id();
    $log->save();

    $content = getActivityLogTool()->getStructuredContent();

    expect($content)->toHaveKey('activity');
    expect($content)->toHaveKey('total');
    expect($content['total'])->toBeGreaterThanOrEqual(1);
});

test('filters by action type', function () {
    $asset = Asset::factory()->laptopZenbook()->create();
    $log = new Actionlog;
    $log->item_type = Asset::class;
    $log->item_id = $asset->id;
    $log->action_type = 'checkout';
    $log->target_id = auth()->id();
    $log->save();

    $content = getActivityLogTool(['action_type' => 'checkout'])->getStructuredContent();

    expect($content['total'])->toBeGreaterThanOrEqual(1);
    foreach ($content['activity'] as $entry) {
        expect($entry['action_type'])->toEqual('checkout');
    }
});

test('filters by item id', function () {
    $asset = Asset::factory()->laptopZenbook()->create();
    $log = new Actionlog;
    $log->item_type = Asset::class;
    $log->item_id = $asset->id;
    $log->action_type = 'update';
    $log->target_id = auth()->id();
    $log->save();

    $content = getActivityLogTool([
        'item_id' => $asset->id,
        'item_type' => Asset::class,
    ])->getStructuredContent();

    expect($content['total'])->toBeGreaterThanOrEqual(1);
    foreach ($content['activity'] as $entry) {
        expect($entry['item_id'])->toEqual($asset->id);
    }
});

test('user without activity view or type view gets empty feed', function () {
    // Mirrors Api\DashboardController::activity: callers without
    // activity.view AND without any type-level view permission
    // get an empty feed, not a hard error. The dashboard treats
    // this the same way.
    $this->actingAs(User::factory()->create());

    $content = getActivityLogTool()->getStructuredContent();

    expect($content['total'])->toBe(0);
    expect($content['activity'])->toBe([]);
});
