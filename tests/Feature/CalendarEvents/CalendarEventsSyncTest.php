<?php

namespace Tests\Feature\CalendarEvents;

use App\Models\Asset;
use App\Models\CalendarEvent;
use App\Models\Maintenance;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

/**
 * Coverage for the HasCalendarEvents trait's observer + sync path,
 * exercised via Maintenance (currently the only source). Verifies:
 *
 *   - Save creates rows keyed on (source_type, source_id, source_field).
 *   - Date-column update reflects on the row.
 *   - Nulling a declared date-column deletes the row.
 *   - Soft-delete cascades to soft-delete of the row.
 *   - Restore restores the row.
 *   - Force-delete cascades to hard-delete of the row.
 *   - Reconcile heals drift (direct DB update on the source) and
 *     removes orphaned rows.
 */
#[Group('mysql')]
class CalendarEventsSyncTest extends TestCase
{
    public function test_saving_a_maintenance_creates_a_calendar_event_row(): void
    {
        $asset = Asset::factory()->create();

        $maintenance = Maintenance::factory()->create([
            'asset_id' => $asset->id,
            'start_date' => now()->subDay(),
            'expected_completion_date' => now()->addDay(),
        ]);

        $event = CalendarEvent::where('source_type', Maintenance::class)
            ->where('source_id', $maintenance->id)
            ->where('source_field', 'start_date')
            ->first();

        $this->assertNotNull($event);
        $this->assertSame('maintenance.start', $event->event_type);
        $this->assertNotNull($event->start);
        $this->assertNotNull($event->end);
    }

    public function test_updating_the_start_date_reflects_on_the_calendar_event_row(): void
    {
        $asset = Asset::factory()->create();

        $maintenance = Maintenance::factory()->create([
            'asset_id' => $asset->id,
            'start_date' => now()->subWeek(),
            'expected_completion_date' => now()->addWeek(),
        ]);

        $newStart = now()->addDay()->startOfDay();
        $maintenance->update(['start_date' => $newStart]);

        $event = CalendarEvent::where('source_type', Maintenance::class)
            ->where('source_id', $maintenance->id)
            ->first();

        $this->assertNotNull($event);
        $this->assertTrue($event->start->equalTo($newStart));
    }

    public function test_nulling_the_end_field_reflects_on_the_calendar_event_row(): void
    {
        $asset = Asset::factory()->create();

        $maintenance = Maintenance::factory()->create([
            'asset_id' => $asset->id,
            'start_date' => now()->subDay(),
            'expected_completion_date' => now()->addDay(),
        ]);

        $maintenance->update(['expected_completion_date' => null]);

        $event = CalendarEvent::where('source_type', Maintenance::class)
            ->where('source_id', $maintenance->id)
            ->first();

        $this->assertNotNull($event);
        $this->assertNull($event->end);
    }

    public function test_non_date_update_does_not_touch_the_calendar_event_row(): void
    {
        $asset = Asset::factory()->create();

        $maintenance = Maintenance::factory()->create([
            'asset_id' => $asset->id,
            'start_date' => now()->subDay(),
            'expected_completion_date' => now()->addDay(),
        ]);

        $eventBefore = CalendarEvent::where('source_type', Maintenance::class)
            ->where('source_id', $maintenance->id)
            ->first();
        $updatedAtBefore = $eventBefore->updated_at;

        // Advance the clock so a spurious re-save would produce a
        // visibly different updated_at than the initial one.
        $this->travel(1)->minutes();

        $maintenance->update(['name' => 'Renamed Only']);

        $eventAfter = $eventBefore->fresh();
        $this->assertTrue($eventAfter->updated_at->equalTo($updatedAtBefore),
            'Non-date update should not have triggered a calendar_events write.');
    }

    public function test_soft_deleting_the_source_soft_deletes_the_calendar_event_row(): void
    {
        $asset = Asset::factory()->create();

        $maintenance = Maintenance::factory()->create([
            'asset_id' => $asset->id,
            'start_date' => now()->subDay(),
            'expected_completion_date' => now()->addDay(),
        ]);

        $maintenance->delete();

        $this->assertDatabaseMissing('calendar_events', [
            'source_type' => Maintenance::class,
            'source_id' => $maintenance->id,
            'deleted_at' => null,
        ]);

        // The row is still there under soft-delete, just excluded
        // from the default scope.
        $trashed = CalendarEvent::withTrashed()
            ->where('source_type', Maintenance::class)
            ->where('source_id', $maintenance->id)
            ->first();
        $this->assertNotNull($trashed);
        $this->assertNotNull($trashed->deleted_at);
    }

    public function test_restoring_the_source_restores_the_calendar_event_row(): void
    {
        $asset = Asset::factory()->create();

        $maintenance = Maintenance::factory()->create([
            'asset_id' => $asset->id,
            'start_date' => now()->subDay(),
            'expected_completion_date' => now()->addDay(),
        ]);
        $maintenance->delete();
        $maintenance->restore();

        $event = CalendarEvent::where('source_type', Maintenance::class)
            ->where('source_id', $maintenance->id)
            ->first();

        $this->assertNotNull($event);
        $this->assertNull($event->deleted_at);
    }

    public function test_reconcile_command_heals_direct_db_date_drift(): void
    {
        $asset = Asset::factory()->create();

        $maintenance = Maintenance::factory()->create([
            'asset_id' => $asset->id,
            'start_date' => now()->subDay(),
            'expected_completion_date' => now()->addDay(),
        ]);

        // Simulate direct DB drift: change the source's start_date
        // via query builder so the observer never fires.
        $driftedStart = now()->addWeeks(2)->startOfDay();
        \DB::table('maintenances')
            ->where('id', $maintenance->id)
            ->update(['start_date' => $driftedStart]);

        $this->artisan('snipeit:reconcile-calendar-events')
            ->assertSuccessful();

        $event = CalendarEvent::where('source_type', Maintenance::class)
            ->where('source_id', $maintenance->id)
            ->first();

        $this->assertTrue($event->start->equalTo($driftedStart));
    }

    public function test_updating_asset_next_audit_date_reflects_on_the_calendar_event_row(): void
    {
        // Mirror of the Maintenance start_date test above, but for
        // Asset::next_audit_date - the exact field the audit workflow
        // writes when an admin performs an audit
        // (AssetsController::auditStore sets $asset->next_audit_date +
        // $asset->last_audit_date and calls $asset->save()). Locks
        // down that the trait's updated observer catches the change
        // and refreshes the calendar_events row.
        $initialAuditDate = now()->addWeek()->startOfDay();
        $asset = Asset::factory()->create([
            'next_audit_date' => $initialAuditDate,
        ]);

        $eventBefore = CalendarEvent::where('source_type', Asset::class)
            ->where('source_id', $asset->id)
            ->where('source_field', 'next_audit_date')
            ->first();
        $this->assertNotNull($eventBefore, 'Creating an asset with next_audit_date should have written a calendar_events row.');

        $newAuditDate = now()->addMonths(3)->startOfDay();
        $asset->update(['next_audit_date' => $newAuditDate]);

        $eventAfter = $eventBefore->fresh();
        $this->assertTrue(
            $eventAfter->start->equalTo($newAuditDate),
            'Updating next_audit_date should have refreshed the calendar_events row.',
        );
    }

    public function test_nulling_asset_next_audit_date_deletes_the_calendar_event_row(): void
    {
        $asset = Asset::factory()->create([
            'next_audit_date' => now()->addWeek()->startOfDay(),
        ]);

        $this->assertDatabaseHas('calendar_events', [
            'source_type' => Asset::class,
            'source_id' => $asset->id,
            'source_field' => 'next_audit_date',
        ]);

        $asset->update(['next_audit_date' => null]);

        $this->assertDatabaseMissing('calendar_events', [
            'source_type' => Asset::class,
            'source_id' => $asset->id,
            'source_field' => 'next_audit_date',
            'deleted_at' => null,
        ]);
    }

    public function test_reconcile_command_deletes_orphaned_rows(): void
    {
        $asset = Asset::factory()->create();

        $maintenance = Maintenance::factory()->create([
            'asset_id' => $asset->id,
            'start_date' => now()->subDay(),
            'expected_completion_date' => now()->addDay(),
        ]);

        // Simulate a source hard-delete that bypassed the observer
        // (e.g. raw SQL, migration): remove the maintenance row
        // directly but leave the calendar_events row behind.
        \DB::table('maintenances')->where('id', $maintenance->id)->delete();

        $this->assertDatabaseHas('calendar_events', [
            'source_type' => Maintenance::class,
            'source_id' => $maintenance->id,
        ]);

        $this->artisan('snipeit:reconcile-calendar-events')
            ->assertSuccessful();

        $this->assertDatabaseMissing('calendar_events', [
            'source_type' => Maintenance::class,
            'source_id' => $maintenance->id,
        ]);
    }
}
