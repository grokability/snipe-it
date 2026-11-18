<?php

use App\Mcp\Tools\DeleteConsumableTool;
use App\Models\Consumable;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->deleteConsumables()->create());
});

function deleteConsumableTool(array $args = []): ResponseFactory
{
    return (new DeleteConsumableTool)->handle(new Request($args));
}

test('deletes consumable by id', function () {
    $consumable = Consumable::factory()->create();

    $content = deleteConsumableTool(['id' => $consumable->id])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertSoftDeleted('consumables', ['id' => $consumable->id]);
});

test('deletes consumable by name', function () {
    $consumable = Consumable::factory()->create(['name' => 'Delete By Name Consumable']);

    $content = deleteConsumableTool(['name' => 'Delete By Name Consumable'])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertSoftDeleted('consumables', ['id' => $consumable->id]);
});

test('response includes name', function () {
    $consumable = Consumable::factory()->create(['name' => 'Named Delete Consumable']);

    $content = deleteConsumableTool(['id' => $consumable->id])->getStructuredContent();

    expect($content['name'])->toEqual('Named Delete Consumable');
});

test('returns error when not found', function () {
    expect(deleteConsumableTool(['id' => 999999])->responses()->first()->isError())->toBeTrue();
});

test('returns error when consumable has checkouts', function () {
    $consumable = Consumable::factory()->create(['qty' => 5]);

    DB::table('consumables_users')->insert([
        'consumable_id' => $consumable->id,
        'assigned_to' => User::factory()->create()->id,
        'created_at' => now(),
        'updated_at' => now(),
        'note' => null,
        'created_by' => auth()->id(),
    ]);

    $response = deleteConsumableTool(['id' => $consumable->id]);

    expect($response->responses()->first()->isError())->toBeTrue();
    $this->assertNotSoftDeleted('consumables', ['id' => $consumable->id]);
});

test('returns error when user lacks permission', function () {
    $this->actingAs(User::factory()->create());
    $consumable = Consumable::factory()->create();

    expect(deleteConsumableTool(['id' => $consumable->id])->responses()->first()->isError())->toBeTrue();
    $this->assertNotSoftDeleted('consumables', ['id' => $consumable->id]);
});
