<?php

use App\Models\Statuslabel;
use Illuminate\Support\Facades\DB;

test('rtd statuslabel add', function () {
    $statuslabel = Statuslabel::factory()->rtd()->create();
    $this->assertModelExists($statuslabel);
});

test('pending statuslabel add', function () {
    $statuslabel = Statuslabel::factory()->pending()->create();
    $this->assertModelExists($statuslabel);
});

test('archived statuslabel add', function () {
    $statuslabel = Statuslabel::factory()->archived()->create();
    $this->assertModelExists($statuslabel);
});

test('out for repair statuslabel add', function () {
    $statuslabel = Statuslabel::factory()->outForRepair()->create();
    $this->assertModelExists($statuslabel);
});

test('broken statuslabel add', function () {
    $statuslabel = Statuslabel::factory()->broken()->create();
    $this->assertModelExists($statuslabel);
});

test('lost statuslabel add', function () {
    $statuslabel = Statuslabel::factory()->lost()->create();
    $this->assertModelExists($statuslabel);
});

test('ids for returns matching status labels per kind', function () {
    $rtd = Statuslabel::factory()->rtd()->create();
    $pending = Statuslabel::factory()->pending()->create();
    $archived = Statuslabel::factory()->archived()->create();

    Statuslabel::clearIdCache();

    expect(Statuslabel::idsFor('deployable')->all())->toContain($rtd->id);
    expect(Statuslabel::idsFor('deployable')->all())->not->toContain($pending->id);

    expect(Statuslabel::idsFor('pending')->all())->toContain($pending->id);
    expect(Statuslabel::idsFor('pending')->all())->not->toContain($rtd->id);

    expect(Statuslabel::idsFor('archived')->all())->toContain($archived->id);
    expect($rtd->id)->toBeIn(Statuslabel::idsFor('not_archived')->all(), 'RTD label is "not archived"');
    expect(Statuslabel::idsFor('not_archived')->all())->not->toContain($archived->id);
});

test('ids for memoizes within a request', function () {
    // Each scope (RTD / Pending / Undeployable / Archived / NotArchived)
    // used to fire a fresh `SELECT id FROM status_labels` every time it
    // was called. Under the API transformer loops that became dozens of
    // identical queries per request. The cache should collapse repeated
    // calls to a single query.
    Statuslabel::factory()->rtd()->create();
    Statuslabel::clearIdCache();

    $queries = 0;
    DB::listen(function ($q) use (&$queries) {
        if (str_contains($q->sql, 'status_labels')) {
            $queries++;
        }
    });

    Statuslabel::idsFor('deployable');
    Statuslabel::idsFor('deployable');
    Statuslabel::idsFor('deployable');

    expect($queries)->toBe(1, 'idsFor should hit the DB once per kind per request');
});

test('ids for cache invalidates on save', function () {
    $original = Statuslabel::factory()->rtd()->create();

    Statuslabel::clearIdCache();
    expect(Statuslabel::idsFor('deployable')->all())->toContain($original->id);

    // New deployable label after the cache was warmed — must show up
    // because the saved event clears the cache.
    $added = Statuslabel::factory()->rtd()->create();

    expect(Statuslabel::idsFor('deployable')->all())->toContain($added->id);
});
