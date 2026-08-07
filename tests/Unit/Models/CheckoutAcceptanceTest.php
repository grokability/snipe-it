<?php

namespace Tests\Unit\Models;

use App\Models\CheckoutAcceptance;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class CheckoutAcceptanceTest extends TestCase
{
    public function test_accepted_scope_returns_only_accepted_acceptances(): void
    {
        $acceptedAcceptance = CheckoutAcceptance::factory()->accepted()->create();
        CheckoutAcceptance::factory()->pending()->create();

        $results = CheckoutAcceptance::accepted()->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->first()->is($acceptedAcceptance));
    }

    public function test_not_superseded_scope_excludes_superseded_acceptances(): void
    {
        $supersedingAcceptance = CheckoutAcceptance::factory()->pending()->create();
        $liveAcceptance = CheckoutAcceptance::factory()->accepted()->create();
        $supersededAcceptance = CheckoutAcceptance::factory()->accepted()->create();
        $supersededAcceptance->markSupersededBy($supersedingAcceptance);

        $results = CheckoutAcceptance::notSuperseded()->pluck('id');

        $this->assertTrue($results->contains($liveAcceptance->id));
        $this->assertTrue($results->contains($supersedingAcceptance->id));
        $this->assertFalse($results->contains($supersededAcceptance->id));
    }

    public function test_mark_superseded_by_sets_pointer_and_timestamp(): void
    {
        $supersedingAcceptance = CheckoutAcceptance::factory()->pending()->create();
        $supersededAcceptance = CheckoutAcceptance::factory()->accepted()->create();

        $supersededAcceptance->markSupersededBy($supersedingAcceptance);

        $this->assertEquals($supersedingAcceptance->id, $supersededAcceptance->superseded_by_id);
        $this->assertInstanceOf(Carbon::class, $supersededAcceptance->superseded_at);

        $supersededAcceptance->refresh();

        $this->assertEquals($supersedingAcceptance->id, $supersededAcceptance->superseded_by_id);
        $this->assertNotNull($supersededAcceptance->superseded_at);
    }
}
