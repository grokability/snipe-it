<?php

use App\Models\Accessory;

test('archived scope returns only archived', function () {
    $live = Accessory::factory()->create();
    $archived = Accessory::factory()->archived()->create();

    $ids = Accessory::query()->archived()->pluck('id')->all();

    expect($ids)->toContain($archived->id);
    expect($ids)->not->toContain($live->id);
});

test('not archived scope excludes archived', function () {
    $live = Accessory::factory()->create();
    $archived = Accessory::factory()->archived()->create();

    $ids = Accessory::query()->notArchived()->pluck('id')->all();

    expect($ids)->toContain($live->id);
    expect($ids)->not->toContain($archived->id);
});

test('accessories for show hides archived when setting off', function () {
    $this->settings->set(['show_archived_in_list' => 0]);

    $live = Accessory::factory()->create();
    $archived = Accessory::factory()->archived()->create();

    $ids = Accessory::query()->accessoriesForShow()->pluck('id')->all();

    expect($ids)->toContain($live->id);
    expect($ids)->not->toContain($archived->id);
});

test('accessories for show includes archived when setting on', function () {
    $this->settings->set(['show_archived_in_list' => 1]);

    $live = Accessory::factory()->create();
    $archived = Accessory::factory()->archived()->create();

    $ids = Accessory::query()->accessoriesForShow()->pluck('id')->all();

    expect($ids)->toContain($live->id);
    expect($ids)->toContain($archived->id);
});

test('requestable scope excludes archived', function () {
    $requestable = Accessory::factory()->create(['requestable' => 1]);
    $archivedButRequestable = Accessory::factory()->archived()->create(['requestable' => 1]);

    $ids = Accessory::query()->requestable()->pluck('id')->all();

    expect($ids)->toContain($requestable->id);
    expect($ids)->not->toContain($archivedButRequestable->id);
});
