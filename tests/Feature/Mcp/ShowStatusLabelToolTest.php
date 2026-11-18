<?php

use App\Mcp\Tools\ShowStatusLabelTool;
use App\Models\Statuslabel;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->viewStatusLabels()->create());
});

function showStatusLabelTool(array $args = []): ResponseFactory
{
    return (new ShowStatusLabelTool)->handle(new Request($args));
}

test('returns status label by id', function () {
    $label = Statuslabel::factory()->create(['name' => 'Show By ID Status Label']);

    $content = showStatusLabelTool(['id' => $label->id])->getStructuredContent();

    expect($content['id'])->toEqual($label->id);
    expect($content['name'])->toEqual('Show By ID Status Label');
});

test('returns status label by name', function () {
    Statuslabel::factory()->create(['name' => 'Show By Name Status Label']);

    $content = showStatusLabelTool(['name' => 'Show By Name Status Label'])->getStructuredContent();

    expect($content['name'])->toEqual('Show By Name Status Label');
});

test('returns error when no identifier provided', function () {
    expect(showStatusLabelTool()->responses()->first()->isError())->toBeTrue();
});

test('returns error when not found', function () {
    expect(showStatusLabelTool(['id' => 999999])->responses()->first()->isError())->toBeTrue();
});

test('returns error when user lacks permission', function () {
    $label = Statuslabel::factory()->create();
    $this->actingAs(User::factory()->create());

    expect(showStatusLabelTool(['id' => $label->id])->responses()->first()->isError())->toBeTrue();
});
