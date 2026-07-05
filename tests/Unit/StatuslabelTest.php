<?php

namespace Tests\Unit;

use App\Models\Statuslabel;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StatuslabelTest extends TestCase
{
    public function test_rtd_statuslabel_add()
    {
        $statuslabel = Statuslabel::factory()->rtd()->create();
        $this->assertModelExists($statuslabel);
    }

    public function test_pending_statuslabel_add()
    {
        $statuslabel = Statuslabel::factory()->pending()->create();
        $this->assertModelExists($statuslabel);
    }

    public function test_archived_statuslabel_add()
    {
        $statuslabel = Statuslabel::factory()->archived()->create();
        $this->assertModelExists($statuslabel);
    }

    public function test_out_for_repair_statuslabel_add()
    {
        $statuslabel = Statuslabel::factory()->outForRepair()->create();
        $this->assertModelExists($statuslabel);
    }

    public function test_broken_statuslabel_add()
    {
        $statuslabel = Statuslabel::factory()->broken()->create();
        $this->assertModelExists($statuslabel);
    }

    public function test_lost_statuslabel_add()
    {
        $statuslabel = Statuslabel::factory()->lost()->create();
        $this->assertModelExists($statuslabel);
    }

    public function test_ids_for_returns_matching_status_labels_per_kind(): void
    {
        $rtd = Statuslabel::factory()->rtd()->create();
        $pending = Statuslabel::factory()->pending()->create();
        $archived = Statuslabel::factory()->archived()->create();

        Statuslabel::clearIdCache();

        $this->assertContains($rtd->id, Statuslabel::idsFor('deployable')->all());
        $this->assertNotContains($pending->id, Statuslabel::idsFor('deployable')->all());

        $this->assertContains($pending->id, Statuslabel::idsFor('pending')->all());
        $this->assertNotContains($rtd->id, Statuslabel::idsFor('pending')->all());

        $this->assertContains($archived->id, Statuslabel::idsFor('archived')->all());
        $this->assertContains($rtd->id, Statuslabel::idsFor('not_archived')->all(), 'RTD label is "not archived"');
        $this->assertNotContains($archived->id, Statuslabel::idsFor('not_archived')->all());
    }

    public function test_ids_for_memoizes_within_a_request(): void
    {
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

        $this->assertSame(1, $queries, 'idsFor should hit the DB once per kind per request');
    }

    public function test_ids_for_cache_invalidates_on_save(): void
    {
        $original = Statuslabel::factory()->rtd()->create();

        Statuslabel::clearIdCache();
        $this->assertContains($original->id, Statuslabel::idsFor('deployable')->all());

        // New deployable label after the cache was warmed — must show up
        // because the saved event clears the cache.
        $added = Statuslabel::factory()->rtd()->create();

        $this->assertContains($added->id, Statuslabel::idsFor('deployable')->all());
    }
}
