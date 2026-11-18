<?php

use App\Models\License;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->deleteLicenses()->create());
});

function handle(array $args): ResponseFactory
{
    return handle(new Request($args));
}

test('deletes license by id', function () {
    $license = License::factory()->create();

    $content = handle(['id' => $license->id])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertSoftDeleted('licenses', ['id' => $license->id]);
});

test('deletes license by name', function () {
    $license = License::factory()->create(['name' => 'Delete By Name License']);

    $content = handle(['name' => 'Delete By Name License'])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertSoftDeleted('licenses', ['id' => $license->id]);
});

test('response includes name', function () {
    $license = License::factory()->create(['name' => 'Named License']);

    $content = handle(['id' => $license->id])->getStructuredContent();

    expect($content['name'])->toEqual('Named License');
});

test('returns error when not found', function () {
    expect(handle(['id' => 999999])->responses()->first()->isError())->toBeTrue();
});

test('returns error when seats are assigned', function () {
    $license = License::factory()->create(['seats' => 3, 'reassignable' => true]);
    $user = User::factory()->create();

    $seat = $license->freeSeat();
    $seat->assigned_to = $user->id;
    $seat->save();

    $response = handle(['id' => $license->id]);

    expect($response->responses()->first()->isError())->toBeTrue();
    $this->assertNotSoftDeleted('licenses', ['id' => $license->id]);
});

test('returns error when user lacks permission', function () {
    $license = License::factory()->create();
    $this->actingAs(User::factory()->create());

    expect(handle(['id' => $license->id])->responses()->first()->isError())->toBeTrue();
    $this->assertNotSoftDeleted('licenses', ['id' => $license->id]);
});
