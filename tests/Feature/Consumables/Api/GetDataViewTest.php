<?php

namespace Tests\Feature\Consumables\Api;

use App\Models\Consumable;
use App\Models\ConsumableAssignment;
use App\Models\User;
use Tests\TestCase;

/**
 * Covers the API endpoint that backs the consumable show page's
 * Assigned tab datatable. The row shape here drives the per-row
 * consume action, the per-row status badge, and the checkbox
 * column's enabled/disabled state via bulk_selectable.
 *
 * Every pivot row surfaces regardless of consumed_at status so the
 * Assigned tab stays a lifetime record of who received a unit. The
 * per-row status + available_actions tell the table which rows can
 * still be consumed and which are terminal.
 */
class GetDataViewTest extends TestCase
{
    public function test_endpoint_returns_outstanding_rows(): void
    {
        $consumable = Consumable::factory()->checkedOutToUser()->create();
        $assignment = ConsumableAssignment::where('consumable_id', $consumable->id)->firstOrFail();

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(route('api.consumables.show.users', $consumable->id))
            ->assertOk()
            ->assertJson([
                'total' => 1,
                'rows' => [
                    [
                        'id' => $assignment->id,
                        'consumable_id' => $consumable->id,
                        'status' => 'outstanding',
                    ],
                ],
            ]);
    }

    public function test_row_marks_consume_action_available_when_outstanding(): void
    {
        $consumable = Consumable::factory()->checkedOutToUser()->create();

        $response = $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(route('api.consumables.show.users', $consumable->id))
            ->assertOk();

        $this->assertTrue($response->json('rows.0.available_actions.consume'));
        $this->assertTrue($response->json('rows.0.available_actions.bulk_selectable.consume'));
        $this->assertNull($response->json('rows.0.consumed_at'));
    }

    public function test_endpoint_surfaces_consumed_rows_with_terminal_status(): void
    {
        // Consumed is a terminal state, but the row stays on the
        // Assigned tab so admins can read who received a given unit
        // without digging through the History tab. The status flag
        // and the false available_actions.consume keep the mark-
        // consumed action from reappearing on terminal rows.
        $consumable = Consumable::factory()->checkedOutToUser()->create();
        $assignment = ConsumableAssignment::where('consumable_id', $consumable->id)->firstOrFail();
        $assignment->consumed_at = now();
        $assignment->save();

        $response = $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(route('api.consumables.show.users', $consumable->id))
            ->assertOk();

        $this->assertSame(1, $response->json('total'));
        $this->assertSame('consumed', $response->json('rows.0.status'));
        $this->assertNotNull($response->json('rows.0.consumed_at'));
        $this->assertFalse($response->json('rows.0.available_actions.consume'));
        $this->assertFalse($response->json('rows.0.available_actions.bulk_selectable.consume'));
    }

    public function test_endpoint_returns_mix_of_outstanding_and_consumed(): void
    {
        $consumable = Consumable::factory()->checkedOutToUser()->checkedOutToUser()->create();
        $assignments = ConsumableAssignment::where('consumable_id', $consumable->id)->get();
        $consumed = $assignments->last();
        $consumed->consumed_at = now();
        $consumed->save();

        $response = $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(route('api.consumables.show.users', $consumable->id))
            ->assertOk();

        $this->assertSame(2, $response->json('total'));
        $statuses = collect($response->json('rows'))->pluck('status')->sort()->values()->all();
        $this->assertSame(['consumed', 'outstanding'], $statuses);
    }

    public function test_status_outstanding_filter_excludes_consumed_rows(): void
    {
        $consumable = Consumable::factory()->checkedOutToUser()->checkedOutToUser()->create();
        $assignments = ConsumableAssignment::where('consumable_id', $consumable->id)->get();
        $consumed = $assignments->last();
        $consumed->consumed_at = now();
        $consumed->save();

        $response = $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(route('api.consumables.show.users', $consumable->id).'?status=outstanding')
            ->assertOk();

        $this->assertSame(1, $response->json('total'));
        $this->assertSame('outstanding', $response->json('rows.0.status'));
    }

    public function test_status_consumed_filter_excludes_outstanding_rows(): void
    {
        $consumable = Consumable::factory()->checkedOutToUser()->checkedOutToUser()->create();
        $assignments = ConsumableAssignment::where('consumable_id', $consumable->id)->get();
        $consumed = $assignments->last();
        $consumed->consumed_at = now();
        $consumed->save();

        $response = $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(route('api.consumables.show.users', $consumable->id).'?status=consumed')
            ->assertOk();

        $this->assertSame(1, $response->json('total'));
        $this->assertSame('consumed', $response->json('rows.0.status'));
    }

    public function test_row_hides_consume_action_when_caller_lacks_checkout_permission(): void
    {
        // Regression pin: Gate::allows('checkout', $consumable) is
        // computed once at the top of getDataView and threaded through
        // every row, so a view-only caller sees consume=false on all
        // outstanding rows.
        $consumable = Consumable::factory()->checkedOutToUser()->create();

        $response = $this->actingAsForApi(User::factory()->viewConsumables()->create())
            ->getJson(route('api.consumables.show.users', $consumable->id))
            ->assertOk();

        $this->assertFalse($response->json('rows.0.available_actions.consume'));
    }
}
