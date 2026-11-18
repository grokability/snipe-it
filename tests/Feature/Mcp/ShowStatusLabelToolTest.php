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

test('returns status label by id', function () {
    $label = Statuslabel::factory()->create(['name' => 'Show By ID Status Label']);

    $content = handle(['id' => $label->id])->getStructuredContent();

    expect($content['id'])->toEqual($label->id);
    expect($content['name'])->toEqual('Show By ID Status Label');
});

test('returns status label by name', function () {
    Statuslabel::factory()->create(['name' => 'Show By Name Status Label']);

    $content = handle(['name' => 'Show By Name Status Label'])->getStructuredContent();

    expect($content['name'])->toEqual('Show By Name Status Label');
});

test('returns error when no identifier provided', function () {
    expect(handle()->responses()->first()->isError())->toBeTrue();
});

test('returns error when not found', function () {
    expect(handle(['id' => 999999])->responses()->first()->isError())->toBeTrue();
});

test('returns error when user lacks permission', function () {
    $label = Statuslabel::factory()->create();
    $this->actingAs(User::factory()->create());

    expect(handle(['id' => $label->id])->responses()->first()->isError())->toBeTrue();
});
