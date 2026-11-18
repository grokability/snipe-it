<?php

use App\Models\Statuslabel;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->viewStatusLabels()->create());
});

function handle(array $args = []): ResponseFactory
{
    return handle(new Request($args));
}

test('returns status labels with pagination', function () {
    Statuslabel::factory()->count(3)->create();

    $content = handle()->getStructuredContent();

    expect($content)->toHaveKey('status_labels');
    expect($content['total'])->toBeGreaterThanOrEqual(3);
});

test('each status label has key fields', function () {
    Statuslabel::factory()->create(['name' => 'Key Fields Status Label']);

    $content = handle(['search' => 'Key Fields Status Label'])->getStructuredContent();

    expect($content['status_labels'])->not->toBeEmpty();
    $label = $content['status_labels'][0];
    expect($label)->toHaveKey('id');
    expect($label)->toHaveKey('name');
    expect($label)->toHaveKey('type');
});

test('returns error when user lacks permission', function () {
    $this->actingAs(User::factory()->create());

    expect(handle()->responses()->first()->isError())->toBeTrue();
});
