<?php

use App\Mcp\Tools\DeleteLicenseTool;
use App\Models\License;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->deleteLicenses()->create());
});

function deleteLicenseTool(array $args): ResponseFactory
{
    return (new DeleteLicenseTool)->handle(new Request($args));
}

test('deletes license by id', function () {
    $license = License::factory()->create();

    $content = deleteLicenseTool(['id' => $license->id])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertSoftDeleted('licenses', ['id' => $license->id]);
});

test('deletes license by name', function () {
    $license = License::factory()->create(['name' => 'Delete By Name License']);

    $content = deleteLicenseTool(['name' => 'Delete By Name License'])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertSoftDeleted('licenses', ['id' => $license->id]);
});

test('response includes name', function () {
    $license = License::factory()->create(['name' => 'Named License']);

    $content = deleteLicenseTool(['id' => $license->id])->getStructuredContent();

    expect($content['name'])->toEqual('Named License');
});

test('returns error when not found', function () {
    expect(deleteLicenseTool(['id' => 999999])->responses()->first()->isError())->toBeTrue();
});

test('returns error when seats are assigned', function () {
    $license = License::factory()->create(['seats' => 3, 'reassignable' => true]);
    $user = User::factory()->create();

    $seat = $license->freeSeat();
    $seat->assigned_to = $user->id;
    $seat->save();

    $response = deleteLicenseTool(['id' => $license->id]);

    expect($response->responses()->first()->isError())->toBeTrue();
    $this->assertNotSoftDeleted('licenses', ['id' => $license->id]);
});

test('returns error when user lacks permission', function () {
    $license = License::factory()->create();
    $this->actingAs(User::factory()->create());

    expect(deleteLicenseTool(['id' => $license->id])->responses()->first()->isError())->toBeTrue();
    $this->assertNotSoftDeleted('licenses', ['id' => $license->id]);
});
