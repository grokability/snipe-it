<?php

namespace Tests\Feature\Accessories;

use App\Enums\CheckoutRequestState;
use App\Models\Accessory;
use App\Models\CheckoutRequest;
use App\Models\User;
use Tests\TestCase;

class AccessoryArchiveValidationTest extends TestCase
{
    public function test_accessory_can_save_as_requestable_without_archived(): void
    {
        $accessory = Accessory::factory()->create();

        $accessory->requestable = 1;
        $accessory->archived = 0;

        $this->assertTrue($accessory->save());
    }

    public function test_accessory_can_save_as_archived_without_requestable(): void
    {
        $accessory = Accessory::factory()->create();

        $accessory->archived = 1;
        $accessory->requestable = 0;

        $this->assertTrue($accessory->save());
    }

    public function test_accessory_cannot_save_as_both_requestable_and_archived(): void
    {
        $accessory = Accessory::factory()->create();

        $accessory->requestable = 1;
        $accessory->archived = 1;

        $this->assertFalse($accessory->save());
        $this->assertArrayHasKey('archived', $accessory->getErrors()->toArray());
        $this->assertArrayHasKey('requestable', $accessory->getErrors()->toArray());
    }

    public function test_accessory_with_active_checkouts_cannot_be_archived(): void
    {
        $accessory = Accessory::factory()->checkedOutToUser()->create();
        $accessory->refresh();

        $accessory->archived = 1;

        $this->assertFalse($accessory->save());
        $this->assertSame(
            trans('general.archived_blocked_by_checkouts', ['item_type' => trans('general.accessory')]),
            $accessory->getErrors()->get('archived')[0],
        );
    }

    public function test_accessory_without_active_checkouts_can_be_archived(): void
    {
        $accessory = Accessory::factory()->create();

        $accessory->archived = 1;

        $this->assertTrue($accessory->save());
    }

    public function test_archiving_accessory_auto_cancels_pending_requests(): void
    {
        $accessory = Accessory::factory()->create();
        $pending = CheckoutRequest::factory()->create([
            'user_id' => User::factory()->create()->id,
            'requestable_id' => $accessory->id,
            'requestable_type' => Accessory::class,
        ]);

        $accessory->archived = 1;
        $accessory->save();

        $pending->refresh();
        $this->assertNotNull($pending->canceled_at);
        $this->assertSame(CheckoutRequestState::Canceled, $pending->state);
    }

    public function test_deleting_accessory_auto_cancels_pending_requests(): void
    {
        $accessory = Accessory::factory()->create();
        $pending = CheckoutRequest::factory()->create([
            'user_id' => User::factory()->create()->id,
            'requestable_id' => $accessory->id,
            'requestable_type' => Accessory::class,
        ]);

        $accessory->delete();

        $pending->refresh();
        $this->assertNotNull($pending->canceled_at);
        $this->assertSame(CheckoutRequestState::Canceled, $pending->state);
    }

    public function test_archiving_does_not_touch_already_canceled_requests(): void
    {
        $accessory = Accessory::factory()->create();
        $previousCancelAt = now()->subDay();
        $alreadyCanceled = CheckoutRequest::factory()->create([
            'user_id' => User::factory()->create()->id,
            'requestable_id' => $accessory->id,
            'requestable_type' => Accessory::class,
            'canceled_at' => $previousCancelAt,
        ]);

        $accessory->archived = 1;
        $accessory->save();

        $alreadyCanceled->refresh();
        $this->assertEquals($previousCancelAt->toDateTimeString(), $alreadyCanceled->canceled_at->toDateTimeString());
    }

    public function test_un_archiving_does_not_touch_requests(): void
    {
        $accessory = Accessory::factory()->archived()->create();
        $request = CheckoutRequest::factory()->create([
            'user_id' => User::factory()->create()->id,
            'requestable_id' => $accessory->id,
            'requestable_type' => Accessory::class,
        ]);

        $accessory->archived = 0;
        $accessory->save();

        $request->refresh();
        $this->assertNull($request->canceled_at);
    }
}
