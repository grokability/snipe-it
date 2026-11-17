<?php

namespace Tests\Feature\Consumables;

use App\Enums\CheckoutRequestState;
use App\Models\CheckoutRequest;
use App\Models\Consumable;
use App\Models\ConsumableAssignment;
use App\Models\User;
use Tests\TestCase;

class ConsumableArchiveValidationTest extends TestCase
{
    public function test_consumable_can_save_as_requestable_without_archived(): void
    {
        $consumable = Consumable::factory()->create();

        $consumable->requestable = 1;
        $consumable->archived = 0;

        $this->assertTrue($consumable->save());
    }

    public function test_consumable_can_save_as_archived_without_requestable(): void
    {
        $consumable = Consumable::factory()->create();

        $consumable->archived = 1;
        $consumable->requestable = 0;

        $this->assertTrue($consumable->save());
    }

    public function test_consumable_cannot_save_as_both_requestable_and_archived(): void
    {
        $consumable = Consumable::factory()->create();

        $consumable->requestable = 1;
        $consumable->archived = 1;

        $this->assertFalse($consumable->save());
        $this->assertArrayHasKey('archived', $consumable->getErrors()->toArray());
        $this->assertArrayHasKey('requestable', $consumable->getErrors()->toArray());
    }

    public function test_consumable_with_outstanding_distributions_cannot_be_archived(): void
    {
        $consumable = Consumable::factory()->checkedOutToUser()->create();
        $consumable->refresh();

        $consumable->archived = 1;

        $this->assertFalse($consumable->save());
        $this->assertSame(
            trans('general.archived_blocked_by_outstanding', ['item_type' => trans('general.consumable')]),
            $consumable->getErrors()->get('archived')[0],
        );
    }

    public function test_consumable_without_outstanding_distributions_can_be_archived(): void
    {
        $consumable = Consumable::factory()->create();

        $consumable->archived = 1;

        $this->assertTrue($consumable->save());
    }

    public function test_consumable_with_all_consumed_distributions_can_be_archived(): void
    {
        // Backfill behavior test: after every pivot row has been marked
        // consumed, the archive guard no longer fires because
        // numOutstanding() returns 0. Historical distributions still
        // sit in the pivot table, they just don't gate the archive.
        $consumable = Consumable::factory()->checkedOutToUser()->create();
        $consumable->refresh();
        ConsumableAssignment::query()
            ->where('consumable_id', $consumable->id)
            ->update(['consumed_at' => now()]);

        $consumable->archived = 1;

        $this->assertTrue($consumable->save());
    }

    public function test_archiving_consumable_auto_cancels_pending_requests(): void
    {
        $consumable = Consumable::factory()->create();
        $pending = CheckoutRequest::factory()->create([
            'user_id' => User::factory()->create()->id,
            'requestable_id' => $consumable->id,
            'requestable_type' => Consumable::class,
        ]);

        $consumable->archived = 1;
        $consumable->save();

        $pending->refresh();
        $this->assertNotNull($pending->canceled_at);
        $this->assertSame(CheckoutRequestState::Canceled, $pending->state);
    }

    public function test_deleting_consumable_auto_cancels_pending_requests(): void
    {
        $consumable = Consumable::factory()->create();
        $pending = CheckoutRequest::factory()->create([
            'user_id' => User::factory()->create()->id,
            'requestable_id' => $consumable->id,
            'requestable_type' => Consumable::class,
        ]);

        $consumable->delete();

        $pending->refresh();
        $this->assertNotNull($pending->canceled_at);
        $this->assertSame(CheckoutRequestState::Canceled, $pending->state);
    }

    public function test_archiving_does_not_touch_already_canceled_requests(): void
    {
        $consumable = Consumable::factory()->create();
        $previousCancelAt = now()->subDay();
        $alreadyCanceled = CheckoutRequest::factory()->create([
            'user_id' => User::factory()->create()->id,
            'requestable_id' => $consumable->id,
            'requestable_type' => Consumable::class,
            'canceled_at' => $previousCancelAt,
        ]);

        $consumable->archived = 1;
        $consumable->save();

        $alreadyCanceled->refresh();
        $this->assertEquals($previousCancelAt->toDateTimeString(), $alreadyCanceled->canceled_at->toDateTimeString());
    }

    public function test_un_archiving_does_not_touch_requests(): void
    {
        $consumable = Consumable::factory()->archived()->create();
        $request = CheckoutRequest::factory()->create([
            'user_id' => User::factory()->create()->id,
            'requestable_id' => $consumable->id,
            'requestable_type' => Consumable::class,
        ]);

        $consumable->archived = 0;
        $consumable->save();

        $request->refresh();
        $this->assertNull($request->canceled_at);
    }

    public function test_set_archived_attribute_normalizes_empty_string(): void
    {
        $consumable = Consumable::factory()->create();

        $consumable->archived = '';

        $this->assertFalse($consumable->archived);
    }
}
