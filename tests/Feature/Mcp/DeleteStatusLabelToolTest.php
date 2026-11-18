<?php

use App\Models\Asset;
use App\Models\Statuslabel;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->deleteStatusLabels()->create());
});

function handle(array $args = []): ResponseFactory
{
    return handle(new Request($args));
}

test('deletes status label by id', function () {
    $label = Statuslabel::factory()->create();

    $content = handle(['id' => $label->id])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertSoftDeleted('status_labels', ['id' => $label->id]);
});

test('deletes status label by name', function () {
    $label = Statuslabel::factory()->create(['name' => 'Delete By Name Status Label']);

    $content = handle(['name' => 'Delete By Name Status Label'])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertSoftDeleted('status_labels', ['id' => $label->id]);
});

test('returns error when not found', function () {
    expect(handle(['id' => 999999])->responses()->first()->isError())->toBeTrue();
});

test('returns error when status label has assets', function () {
    $label = Statuslabel::factory()->rtd()->create();
    Asset::factory()->create(['status_id' => $label->id]);

    $response = handle(['id' => $label->id]);

    expect($response->responses()->first()->isError())->toBeTrue();
    $this->assertNotSoftDeleted('status_labels', ['id' => $label->id]);
});

test('returns error when user lacks permission', function () {
    $label = Statuslabel::factory()->create();
    $this->actingAs(User::factory()->create());

    expect(handle(['id' => $label->id])->responses()->first()->isError())->toBeTrue();
    $this->assertNotSoftDeleted('status_labels', ['id' => $label->id]);
});
