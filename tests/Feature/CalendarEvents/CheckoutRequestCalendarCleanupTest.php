<?php

namespace Tests\Feature\CalendarEvents;

use App\Enums\CheckoutRequestState;
use App\Models\Accessory;
use App\Models\Asset;
use App\Models\CalendarEvent;
use App\Models\CheckoutRequest;
use App\Models\Component;
use App\Models\Consumable;
use App\Models\User;
use Tests\TestCase;

/**
 * A canceled CheckoutRequest must stop showing up on the reservation
 * calendar. The HasCalendarEvents trait keys off the model's updated
 * event, and the previous cancel paths (user-initiated via
 * Requestable::cancelRequest and archive-time via the observer
 * saveQuietly cleanup) both bypassed Eloquent events, leaving the
 * calendar_events row behind with the Pending-state start/end dates.
 */
class CheckoutRequestCalendarCleanupTest extends TestCase
{
    private function assertHasCalendarEvent(CheckoutRequest $request): void
    {
        $this->assertDatabaseHas('calendar_events', [
            'source_type' => CheckoutRequest::class,
            'source_id' => $request->id,
            'source_field' => 'calendar_start',
        ]);
    }

    private function assertMissingCalendarEvent(CheckoutRequest $request): void
    {
        // CalendarEvent uses SoftDeletes, so the trait's sync path
        // soft-deletes the row when calendar_start resolves to null
        // (state != pending). Assert the live row is gone, not the
        // trashed one, matching how CalendarEventsSyncTest checks
        // the soft-delete lifecycle.
        $this->assertDatabaseMissing('calendar_events', [
            'source_type' => CheckoutRequest::class,
            'source_id' => $request->id,
            'source_field' => 'calendar_start',
            'deleted_at' => null,
        ]);
    }

    public function test_user_initiated_cancel_removes_calendar_event(): void
    {
        $user = User::factory()->create();
        $asset = Asset::factory()->create();
        $request = CheckoutRequest::factory()->create([
            'user_id' => $user->id,
            'requestable_id' => $asset->id,
            'requestable_type' => Asset::class,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDays(3),
        ]);

        $this->assertHasCalendarEvent($request);

        $affected = $asset->cancelRequest($user->id);

        $this->assertSame(1, $affected);
        $this->assertSame(CheckoutRequestState::Canceled, $request->fresh()->state);
        $this->assertMissingCalendarEvent($request);
    }

    public function test_cancel_and_sync_on_the_request_removes_calendar_event(): void
    {
        $request = CheckoutRequest::factory()->create([
            'user_id' => User::factory()->create()->id,
            'requestable_id' => Asset::factory()->create()->id,
            'requestable_type' => Asset::class,
            'start_date' => now(),
            'end_date' => now()->addDay(),
        ]);

        $this->assertHasCalendarEvent($request);

        $request->cancelAndSync();

        $this->assertSame(CheckoutRequestState::Canceled, $request->fresh()->state);
        $this->assertNotNull($request->fresh()->canceled_at);
        $this->assertMissingCalendarEvent($request);
    }

    public function test_archiving_an_accessory_removes_calendar_events_for_its_requests(): void
    {
        $accessory = Accessory::factory()->create();
        $user = User::factory()->create();

        // Requestable::openRequests reads request rows joined through
        // the accessory's own HasMany to CheckoutRequest, so building
        // the request via factory with a direct morph pointer is enough.
        $request = CheckoutRequest::factory()->create([
            'user_id' => $user->id,
            'requestable_id' => $accessory->id,
            'requestable_type' => Accessory::class,
            'start_date' => now(),
            'end_date' => now()->addWeek(),
        ]);

        $this->assertHasCalendarEvent($request);

        $accessory->update(['archived' => 1]);

        $this->assertSame(CheckoutRequestState::Canceled, $request->fresh()->state);
        $this->assertMissingCalendarEvent($request);
    }

    public function test_deleting_a_consumable_removes_calendar_events_for_its_requests(): void
    {
        $consumable = Consumable::factory()->create();
        $user = User::factory()->create();

        $request = CheckoutRequest::factory()->create([
            'user_id' => $user->id,
            'requestable_id' => $consumable->id,
            'requestable_type' => Consumable::class,
            'start_date' => now(),
            'end_date' => now()->addWeek(),
        ]);

        $this->assertHasCalendarEvent($request);

        $consumable->delete();

        $this->assertSame(CheckoutRequestState::Canceled, $request->fresh()->state);
        $this->assertMissingCalendarEvent($request);
    }

    public function test_archiving_a_component_removes_calendar_events_for_its_requests(): void
    {
        $component = Component::factory()->create();
        $user = User::factory()->create();

        $request = CheckoutRequest::factory()->create([
            'user_id' => $user->id,
            'requestable_id' => $component->id,
            'requestable_type' => Component::class,
            'start_date' => now(),
            'end_date' => now()->addWeek(),
        ]);

        $this->assertHasCalendarEvent($request);

        $component->update(['archived' => 1]);

        $this->assertSame(CheckoutRequestState::Canceled, $request->fresh()->state);
        $this->assertMissingCalendarEvent($request);
    }

    public function test_reconcile_migration_cleans_up_pre_existing_stale_rows(): void
    {
        // Simulate the historical bug: a terminal CheckoutRequest row
        // with a live calendar_events entry still pointing at it.
        $request = CheckoutRequest::factory()->create([
            'user_id' => User::factory()->create()->id,
            'requestable_id' => Asset::factory()->create()->id,
            'requestable_type' => Asset::class,
            'start_date' => now(),
            'end_date' => now()->addDay(),
        ]);

        $this->assertHasCalendarEvent($request);

        // Push the request into terminal state via query builder so
        // the HasCalendarEvents observer does not fire, matching the
        // pre-fix cancel path. The calendar_events row survives.
        CheckoutRequest::query()
            ->where('id', $request->id)
            ->update([
                'state' => CheckoutRequestState::Canceled->value,
                'canceled_at' => now(),
            ]);

        $this->assertHasCalendarEvent($request);

        // Re-run the one-shot reconcile migration.
        $migration = include database_path('migrations/2026_10_29_140345_purge_stale_calendar_events_for_terminal_checkout_requests.php');
        $migration->up();

        $this->assertMissingCalendarEvent($request);
    }
}
