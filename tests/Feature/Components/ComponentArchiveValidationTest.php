<?php

namespace Tests\Feature\Components;

use App\Enums\CheckoutRequestState;
use App\Models\Asset;
use App\Models\Category;
use App\Models\CheckoutRequest;
use App\Models\Component;
use App\Models\User;
use Tests\TestCase;

/**
 * Exercises Watson's integration of the archive-related rules on the
 * Component model (the mutex plus the no-archive-with-active-checkouts
 * guard). These fire through getRules() so test the full save path
 * end-to-end rather than hitting the Rule classes in isolation.
 */
class ComponentArchiveValidationTest extends TestCase
{
    public function test_component_can_save_as_requestable_without_archived(): void
    {
        $component = Component::factory()->create();

        $component->requestable = 1;
        $component->archived = 0;

        $this->assertTrue($component->save());
    }

    public function test_component_can_save_as_archived_without_requestable(): void
    {
        $component = Component::factory()->create();

        $component->archived = 1;
        $component->requestable = 0;

        $this->assertTrue($component->save());
    }

    public function test_component_cannot_save_as_both_requestable_and_archived(): void
    {
        $component = Component::factory()->create();

        $component->requestable = 1;
        $component->archived = 1;

        $this->assertFalse($component->save());
        $this->assertArrayHasKey('archived', $component->getErrors()->toArray());
        $this->assertArrayHasKey('requestable', $component->getErrors()->toArray());
    }

    public function test_component_with_active_checkouts_cannot_be_archived(): void
    {
        $component = Component::factory()->create(['qty' => 10]);
        $asset = Asset::factory()->create();
        $component->assets()->attach($asset->id, [
            'assigned_qty' => 2,
            'created_at' => now(),
            'created_by' => 1,
        ]);

        $component->archived = 1;

        $this->assertFalse($component->save());
        $this->assertSame(
            trans('general.archived_blocked_by_checkouts', ['item_type' => trans('general.component')]),
            $component->getErrors()->get('archived')[0],
        );
    }

    public function test_component_without_active_checkouts_can_be_archived(): void
    {
        $component = Component::factory()->create(['qty' => 10]);

        $component->archived = 1;

        $this->assertTrue($component->save());
    }

    public function test_archiving_component_auto_cancels_pending_requests(): void
    {
        // Pending requests for a just-archived component would otherwise
        // sit in /requests as un-fulfillable rows and in the user's
        // /account/requested list with a dead link. Observer closes them
        // out so neither surface shows the orphaned row.
        $component = Component::factory()->create();
        $pending = CheckoutRequest::factory()->create([
            'user_id' => User::factory()->create()->id,
            'requestable_id' => $component->id,
            'requestable_type' => Component::class,
        ]);

        $component->archived = 1;
        $component->save();

        $pending->refresh();
        $this->assertNotNull($pending->canceled_at);
        $this->assertSame(CheckoutRequestState::Canceled, $pending->state);
    }

    public function test_deleting_component_auto_cancels_pending_requests(): void
    {
        $component = Component::factory()->create();
        $pending = CheckoutRequest::factory()->create([
            'user_id' => User::factory()->create()->id,
            'requestable_id' => $component->id,
            'requestable_type' => Component::class,
        ]);

        $component->delete();

        $pending->refresh();
        $this->assertNotNull($pending->canceled_at);
        $this->assertSame(CheckoutRequestState::Canceled, $pending->state);
    }

    public function test_archiving_does_not_touch_already_canceled_requests(): void
    {
        $component = Component::factory()->create();
        $previousCancelAt = now()->subDay();
        $alreadyCanceled = CheckoutRequest::factory()->create([
            'user_id' => User::factory()->create()->id,
            'requestable_id' => $component->id,
            'requestable_type' => Component::class,
            'canceled_at' => $previousCancelAt,
        ]);

        $component->archived = 1;
        $component->save();

        $alreadyCanceled->refresh();
        $this->assertEquals($previousCancelAt->toDateTimeString(), $alreadyCanceled->canceled_at->toDateTimeString());
    }

    public function test_un_archiving_does_not_touch_requests(): void
    {
        $component = Component::factory()->archived()->create();
        $request = CheckoutRequest::factory()->create([
            'user_id' => User::factory()->create()->id,
            'requestable_id' => $component->id,
            'requestable_type' => Component::class,
        ]);

        $component->archived = 0;
        $component->save();

        $request->refresh();
        $this->assertNull($request->canceled_at);
    }

    public function test_new_component_can_be_created_as_archived(): void
    {
        // Regression check: ComponentsController::store() was setting
        // every attribute by hand and skipping `archived`, so a form
        // POST with `archived=1` persisted as 0. Pin so a future refactor
        // can't silently drop the field again.
        $component = new Component([
            'name' => 'archived at birth',
            'qty' => 1,
            'category_id' => Category::factory()->create()->id,
        ]);
        $component->archived = 1;

        $this->assertTrue($component->save());
        $this->assertTrue((bool) $component->fresh()->archived);
    }
}
