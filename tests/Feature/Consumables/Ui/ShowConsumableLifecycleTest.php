<?php

namespace Tests\Feature\Consumables\Ui;

use App\Models\Consumable;
use App\Models\ConsumableAssignment;
use App\Models\User;
use Tests\TestCase;

/**
 * Pins the view-level reads of the outstanding count against the
 * data model so a stray numCheckedOut() call (which counts
 * historical consumed distributions too) can't sneak back in and
 * desync the tab badge or the archive-blocked checkbox state from
 * what the Assigned table actually shows.
 */
class ShowConsumableLifecycleTest extends TestCase
{
    public function test_assigned_tab_badge_reflects_outstanding_not_total_distributions(): void
    {
        $consumable = Consumable::factory()->checkedOutToUser()->checkedOutToUser()->create();
        $assignments = ConsumableAssignment::where('consumable_id', $consumable->id)->get();
        $this->assertCount(2, $assignments);

        $toConsume = $assignments->first();
        $toConsume->consumed_at = now();
        $toConsume->save();

        $html = $this->actingAs(User::factory()->superuser()->create())
            ->get(route('consumables.show', $consumable))
            ->assertOk()
            ->getContent();

        // Badge should read 1 (the one still outstanding), not 2
        // (total ever distributed). Match the href + the digit so
        // we're reading the actual Assigned tab's count span.
        $this->assertMatchesRegularExpression(
            '/href="[^"]*#assigned"[^>]*>.*?<span[^>]*>1<\/span>/s',
            $html,
            'Assigned tab badge must reflect numOutstanding, not numCheckedOut.',
        );
        $this->assertDoesNotMatchRegularExpression(
            '/href="[^"]*#assigned"[^>]*>.*?<span[^>]*>2<\/span>/s',
            $html,
            'Badge must not include consumed rows in its count.',
        );
    }

    public function test_edit_view_enables_archive_checkbox_once_all_distributions_consumed(): void
    {
        // Regression pin: the archive checkbox's :disabled used to
        // read $item->users()->count() (every pivot row ever), which
        // stayed > 0 even after admin bulk-consumed everything, so
        // the admin couldn't flip the box. It must track
        // numOutstanding() instead.
        $consumable = Consumable::factory()->checkedOutToUser()->create();
        ConsumableAssignment::query()
            ->where('consumable_id', $consumable->id)
            ->update(['consumed_at' => now()]);

        $html = $this->actingAs(User::factory()->superuser()->create())
            ->get(route('consumables.edit', $consumable))
            ->assertOk()
            ->getContent();

        $this->assertMatchesRegularExpression(
            '/<input[^>]*name="archived"[^>]*(?<!disabled=")(?<!disabled)>/',
            $html,
            'Archived checkbox must be enabled when nothing is outstanding.',
        );
    }

    public function test_edit_view_disables_archive_checkbox_while_distributions_outstanding(): void
    {
        $consumable = Consumable::factory()->checkedOutToUser()->create();

        $html = $this->actingAs(User::factory()->superuser()->create())
            ->get(route('consumables.edit', $consumable))
            ->assertOk()
            ->getContent();

        $this->assertMatchesRegularExpression(
            '/<input[^>]*name="archived"[^>]*disabled/',
            $html,
            'Archived checkbox must be disabled while an outstanding distribution exists.',
        );
    }

    public function test_show_page_loads_for_soft_deleted_consumable(): void
    {
        // Regression pin: the resource route needs withTrashed(['show'])
        // AND the show() method's internal re-fetch has to withTrashed()
        // too. Without both, the Deleted-index "View" link bounces back
        // with "That Consumable was not found" for an admin who can see
        // the deleted row in the first place.
        $consumable = Consumable::factory()->create();
        $consumable->delete();

        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('consumables.show', $consumable->id))
            ->assertOk();
    }
}
