<?php

namespace Tests\Feature\Consumables\Api;

use App\Models\Consumable;
use App\Models\ConsumableAssignment;
use App\Models\User;
use Tests\TestCase;

/**
 * Covers the API endpoint that backs the consumable show page's
 * Assigned tab datatable. The row shape here drives per-row consume
 * actions and the checkbox column's enabled/disabled state via
 * bulk_selectable. Only outstanding (non-consumed) rows surface
 * because consumed distributions are historical and belong on the
 * History tab, not the "currently assigned to" list.
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
    }

    public function test_endpoint_hides_consumed_rows(): void
    {
        // Mark-consumed is a terminal state. Once a pivot row has a
        // consumed_at stamp, it stops surfacing on the Assigned tab
        // (which lists "currently distributed" rows only). The
        // consume audit log on the History tab preserves the record.
        $consumable = Consumable::factory()->checkedOutToUser()->create();
        $assignment = ConsumableAssignment::where('consumable_id', $consumable->id)->firstOrFail();
        $assignment->consumed_at = now();
        $assignment->save();

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(route('api.consumables.show.users', $consumable->id))
            ->assertOk()
            ->assertJson([
                'total' => 0,
                'rows' => [],
            ]);
    }

    public function test_endpoint_returns_mix_of_outstanding_and_hides_consumed(): void
    {
        $consumable = Consumable::factory()->checkedOutToUser()->checkedOutToUser()->create();
        $assignments = ConsumableAssignment::where('consumable_id', $consumable->id)->get();
        $outstanding = $assignments->first();
        $consumed = $assignments->last();
        $consumed->consumed_at = now();
        $consumed->save();

        $response = $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(route('api.consumables.show.users', $consumable->id))
            ->assertOk();

        $this->assertSame(1, $response->json('total'));
        $this->assertSame($outstanding->id, $response->json('rows.0.id'));
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
