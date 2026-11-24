<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\MarkConsumableOutstandingConsumedTool;
use App\Models\Actionlog;
use App\Models\Consumable;
use App\Models\ConsumableAssignment;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class MarkConsumableOutstandingConsumedToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->checkoutConsumables()->create());
    }

    private function handle(array $args = []): ResponseFactory
    {
        return (new MarkConsumableOutstandingConsumedTool)->handle(new Request($args));
    }

    public function test_marks_every_outstanding_distribution_consumed(): void
    {
        $consumable = Consumable::factory()
            ->checkedOutToUser()
            ->checkedOutToUser()
            ->checkedOutToUser()
            ->create();
        $assignments = ConsumableAssignment::where('consumable_id', $consumable->id)->get();

        $content = $this->handle(['id' => $consumable->id])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertSame(3, $content['consumed_count']);
        $assignments->each(fn ($a) => $this->assertNotNull($a->fresh()->consumed_at));
    }

    public function test_leaves_already_consumed_rows_alone(): void
    {
        // Pre-stamped rows keep their original consumed_at.
        $consumable = Consumable::factory()->checkedOutToUser()->checkedOutToUser()->create();
        $assignments = ConsumableAssignment::where('consumable_id', $consumable->id)->get();
        $pre = $assignments->first();
        $originalStamp = now()->subDay();
        $pre->consumed_at = $originalStamp;
        $pre->save();

        $this->handle(['id' => $consumable->id]);

        $this->assertSame(
            $originalStamp->format('Y-m-d H:i:s'),
            $pre->fresh()->consumed_at->format('Y-m-d H:i:s'),
        );
    }

    public function test_writes_one_summary_action_log_regardless_of_batch_size(): void
    {
        // Per-row logs on bulk operations would flood the dashboard.
        // Exactly one `consumed` entry with quantity=N per bulk call.
        $consumable = Consumable::factory()
            ->checkedOutToUser()
            ->checkedOutToUser()
            ->checkedOutToUser()
            ->create();

        $this->handle(['id' => $consumable->id]);

        $logs = Actionlog::where('action_type', 'consumed')
            ->where('item_type', Consumable::class)
            ->where('item_id', $consumable->id)
            ->get();
        $this->assertCount(1, $logs);
        $this->assertSame(3, (int) $logs->first()->quantity);
    }

    public function test_reports_zero_when_no_outstanding_rows(): void
    {
        $consumable = Consumable::factory()->create();

        $content = $this->handle(['id' => $consumable->id])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertSame(0, $content['consumed_count']);
    }

    public function test_requires_checkout_permission(): void
    {
        $this->actingAs(User::factory()->viewConsumables()->create());
        $consumable = Consumable::factory()->checkedOutToUser()->create();
        $assignment = ConsumableAssignment::where('consumable_id', $consumable->id)->firstOrFail();

        $content = $this->handle(['id' => $consumable->id])->getStructuredContent();

        $this->assertFalse($content['success'] ?? false);
        $this->assertNull($assignment->fresh()->consumed_at);
    }

    public function test_resolves_consumable_by_name(): void
    {
        $consumable = Consumable::factory()->checkedOutToUser()->create();
        $assignment = ConsumableAssignment::where('consumable_id', $consumable->id)->firstOrFail();

        $content = $this->handle(['name' => $consumable->name])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertSame(1, $content['consumed_count']);
        $this->assertNotNull($assignment->fresh()->consumed_at);
    }
}
