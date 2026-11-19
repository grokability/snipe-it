<?php

use App\Models\Component;

test('archived scope returns only archived', function () {
    $live = Component::factory()->create();
    $archived = Component::factory()->archived()->create();

    $ids = Component::query()->archived()->pluck('id')->all();

    expect($ids)->toContain($archived->id);
    expect($ids)->not->toContain($live->id);
});

test('not archived scope excludes archived', function () {
    $live = Component::factory()->create();
    $archived = Component::factory()->archived()->create();

    $ids = Component::query()->notArchived()->pluck('id')->all();

    expect($ids)->toContain($live->id);
    expect($ids)->not->toContain($archived->id);
});

test('components for show hides archived when setting off', function () {
    $this->settings->set(['show_archived_in_list' => 0]);

    $live = Component::factory()->create();
    $archived = Component::factory()->archived()->create();

    $ids = Component::query()->componentsForShow()->pluck('id')->all();

    expect($ids)->toContain($live->id);
    expect($ids)->not->toContain($archived->id);
});

test('components for show includes archived when setting on', function () {
    $this->settings->set(['show_archived_in_list' => 1]);

    $live = Component::factory()->create();
    $archived = Component::factory()->archived()->create();

    $ids = Component::query()->componentsForShow()->pluck('id')->all();

    expect($ids)->toContain($live->id);
    expect($ids)->toContain($archived->id);
});

test('requestable scope excludes archived', function () {
    $requestable = Component::factory()->create(['requestable' => 1]);
    $archivedButRequestable = Component::factory()->archived()->create(['requestable' => 1]);

    $ids = Component::query()->requestable()->pluck('id')->all();

    expect($ids)->toContain($requestable->id);
    expect($ids)->not->toContain($archivedButRequestable->id);
});
