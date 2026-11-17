<?php

namespace Tests\Feature\Consumables\Ui;

use App\Models\Actionlog;
use App\Models\Consumable;
use App\Models\ConsumableAssignment;
use App\Models\User;
use Tests\TestCase;

class MarkConsumedTest extends TestCase
{
    public function test_single_consume_requires_checkout_permission(): void
    {
        $consumable = Consumable::factory()->checkedOutToUser()->create();
        $assignment = ConsumableAssignment::where('consumable_id', $consumable->id)->firstOrFail();

        $this->actingAs(User::factory()->viewConsumables()->create())
            ->post(route('consumables.consume', ['consumable' => $consumable->id, 'assignment' => $assignment->id]))
            ->assertForbidden();

        $this->assertNull($assignment->fresh()->consumed_at);
    }

    public function test_single_consume_sets_consumed_at(): void
    {
        $consumable = Consumable::factory()->checkedOutToUser()->create();
        $assignment = ConsumableAssignment::where('consumable_id', $consumable->id)->firstOrFail();

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('consumables.consume', ['consumable' => $consumable->id, 'assignment' => $assignment->id]))
            ->assertRedirect(route('consumables.show', $consumable))
            ->assertSessionHas('success');

        $this->assertNotNull($assignment->fresh()->consumed_at);
    }

    public function test_single_consume_decrements_outstanding_count(): void
    {
        // Regression pin for the archive-gate behavior: an outstanding
        // row that gets marked consumed should drop out of
        // numOutstanding() so the parent becomes archivable.
        $consumable = Consumable::factory()->checkedOutToUser()->create();
        $assignment = ConsumableAssignment::where('consumable_id', $consumable->id)->firstOrFail();

        $this->assertSame(1, $consumable->fresh()->numOutstanding());

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('consumables.consume', ['consumable' => $consumable->id, 'assignment' => $assignment->id]));

        $this->assertSame(0, $consumable->fresh()->numOutstanding());
    }

    public function test_single_consume_is_one_way(): void
    {
        // Posting again against an already-consumed row warns and
        // leaves consumed_at pinned to its original stamp.
        $consumable = Consumable::factory()->checkedOutToUser()->create();
        $assignment = ConsumableAssignment::where('consumable_id', $consumable->id)->firstOrFail();
        $firstStamp = now()->subHour();
        $assignment->consumed_at = $firstStamp;
        $assignment->save();

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('consumables.consume', ['consumable' => $consumable->id, 'assignment' => $assignment->id]))
            ->assertRedirect(route('consumables.show', $consumable))
            ->assertSessionHas('warning');

        $this->assertEquals($firstStamp->toDateTimeString(), $assignment->fresh()->consumed_at->toDateTimeString());
    }

    public function test_single_consume_rejects_cross_consumable_assignment(): void
    {
        // A pivot id that belongs to a different consumable than the
        // one in the URL must 404. Prevents a user with checkout on
        // consumable A from ending the lifecycle on consumable B's
        // pivot rows just by swapping the URL id.
        $consumableA = Consumable::factory()->checkedOutToUser()->create();
        $consumableB = Consumable::factory()->checkedOutToUser()->create();
        $assignmentB = ConsumableAssignment::where('consumable_id', $consumableB->id)->firstOrFail();

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('consumables.consume', ['consumable' => $consumableA->id, 'assignment' => $assignmentB->id]))
            ->assertNotFound();

        $this->assertNull($assignmentB->fresh()->consumed_at);
    }

    public function test_single_consume_writes_audit_log(): void
    {
        $consumable = Consumable::factory()->checkedOutToUser()->create();
        $assignment = ConsumableAssignment::where('consumable_id', $consumable->id)->firstOrFail();

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('consumables.consume', ['consumable' => $consumable->id, 'assignment' => $assignment->id]));

        $this->assertDatabaseHas('action_logs', [
            'item_type' => Consumable::class,
            'item_id' => $consumable->id,
            'target_type' => User::class,
            'target_id' => $assignment->assigned_to,
            'action_type' => 'consumed',
        ]);
    }

    public function test_bulk_consume_show_filters_already_consumed_rows(): void
    {
        $consumable = Consumable::factory()->checkedOutToUser()->checkedOutToUser()->create();
        $assignments = ConsumableAssignment::where('consumable_id', $consumable->id)->get();
        $outstanding = $assignments->first();
        $alreadyConsumed = $assignments->last();
        $alreadyConsumed->consumed_at = now();
        $alreadyConsumed->save();

        $response = $this->actingAs(User::factory()->superuser()->create())
            ->post(route('consumables.bulkconsume.show'), [
                'ids' => [$outstanding->id, $alreadyConsumed->id],
            ])
            ->assertOk()
            ->assertViewIs('consumables.bulk-consume');

        $visible = $response->viewData('assignments');
        $this->assertTrue($visible->contains('id', $outstanding->id));
        $this->assertFalse($visible->contains('id', $alreadyConsumed->id));
    }

    public function test_bulk_consume_store_marks_every_selected_row(): void
    {
        $consumable = Consumable::factory()->checkedOutToUser()->checkedOutToUser()->create();
        $assignments = ConsumableAssignment::where('consumable_id', $consumable->id)->get();

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('consumables.bulkconsume.store'), [
                'ids' => $assignments->pluck('id')->all(),
            ])
            ->assertRedirect(route('consumables.show', $consumable))
            ->assertSessionHas('success');

        $assignments->each(fn ($a) => $this->assertNotNull($a->fresh()->consumed_at));
        $this->assertSame(0, $consumable->fresh()->numOutstanding());
    }

    public function test_bulk_consume_store_requires_checkout_permission(): void
    {
        $consumable = Consumable::factory()->checkedOutToUser()->create();
        $assignment = ConsumableAssignment::where('consumable_id', $consumable->id)->firstOrFail();

        $this->actingAs(User::factory()->viewConsumables()->create())
            ->post(route('consumables.bulkconsume.store'), [
                'ids' => [$assignment->id],
            ])
            ->assertForbidden();

        $this->assertNull($assignment->fresh()->consumed_at);
    }

    public function test_bulk_consume_store_rejects_mixed_consumable_submission(): void
    {
        // Mixed-parent submissions are a sign the UI was tampered
        // with. Reject the whole batch rather than silently closing
        // out a cross-parent set.
        $consumableA = Consumable::factory()->checkedOutToUser()->create();
        $consumableB = Consumable::factory()->checkedOutToUser()->create();
        $assignmentA = ConsumableAssignment::where('consumable_id', $consumableA->id)->firstOrFail();
        $assignmentB = ConsumableAssignment::where('consumable_id', $consumableB->id)->firstOrFail();

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('consumables.bulkconsume.store'), [
                'ids' => [$assignmentA->id, $assignmentB->id],
            ])
            ->assertRedirect(route('consumables.index'))
            ->assertSessionHas('error');

        $this->assertNull($assignmentA->fresh()->consumed_at);
        $this->assertNull($assignmentB->fresh()->consumed_at);
    }

    public function test_bulk_consume_store_writes_one_log_per_row(): void
    {
        $consumable = Consumable::factory()->checkedOutToUser()->checkedOutToUser()->create();
        $assignments = ConsumableAssignment::where('consumable_id', $consumable->id)->get();
        $before = Actionlog::where('item_type', Consumable::class)
            ->where('action_type', 'consumed')
            ->count();

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('consumables.bulkconsume.store'), [
                'ids' => $assignments->pluck('id')->all(),
            ]);

        $after = Actionlog::where('item_type', Consumable::class)
            ->where('action_type', 'consumed')
            ->count();
        $this->assertSame($before + $assignments->count(), $after);
    }
}
