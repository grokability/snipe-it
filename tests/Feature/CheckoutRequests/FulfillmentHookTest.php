<?php

namespace Tests\Feature\CheckoutRequests;

use App\Enums\CheckoutRequestState;
use App\Events\CheckoutableCheckedOut;
use App\Models\Accessory;
use App\Models\Asset;
use App\Models\CheckoutRequest;
use App\Models\Component;
use App\Models\Consumable;
use App\Models\License;
use App\Models\LicenseSeat;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * CheckoutableCheckedOutLogCheckout closes the fulfillment loop when a
 * checkout event fires. Runs for every checkoutable type via the
 * shared CheckoutableCheckedOut event, so each polymorphic case
 * gets its own assertion here.
 *
 * The listener maps LicenseSeat -> License for the request lookup
 * because requesters ask for a License (not a specific seat) at this
 * time (though that could change down the line).
 */
class FulfillmentHookTest extends TestCase
{
    public function test_checkout_fulfills_matching_open_asset_request(): void
    {
        $user = User::factory()->create();
        $asset = Asset::factory()->assignedToUser()->create();
        $asset->assigned_to = null;
        $asset->assigned_type = null;
        $asset->save();
        $request = CheckoutRequest::factory()->create([
            'user_id' => $user->id,
            'requestable_id' => $asset->id,
            'requestable_type' => Asset::class,
        ]);

        $asset->checkOut($user, User::factory()->create());

        $this->assertSame(CheckoutRequestState::Fulfilled, $request->fresh()->state);
        $this->assertNotNull($request->fresh()->fulfilled_at);
    }

    public function test_checkout_fulfills_matching_open_accessory_request(): void
    {
        $user = User::factory()->create();
        $accessory = Accessory::factory()->create();
        $request = CheckoutRequest::factory()->create([
            'user_id' => $user->id,
            'requestable_id' => $accessory->id,
            'requestable_type' => Accessory::class,
        ]);

        event(new CheckoutableCheckedOut($accessory, $user, User::factory()->create(), null));

        $this->assertSame(CheckoutRequestState::Fulfilled, $request->fresh()->state);
    }

    public function test_checkout_fulfills_matching_open_consumable_request(): void
    {
        $user = User::factory()->create();
        $consumable = Consumable::factory()->create();
        $request = CheckoutRequest::factory()->create([
            'user_id' => $user->id,
            'requestable_id' => $consumable->id,
            'requestable_type' => Consumable::class,
        ]);

        event(new CheckoutableCheckedOut($consumable, $user, User::factory()->create(), null));

        $this->assertSame(CheckoutRequestState::Fulfilled, $request->fresh()->state);
    }

    public function test_checkout_links_current_log_and_fulfills_only_event_quantity(): void
    {
        Mail::fake();

        $user = User::factory()->create();
        $actor = User::factory()->create();
        $accessory = Accessory::factory()->create();

        event(new CheckoutableCheckedOut($accessory, $user, $actor, 'Previous checkout'));

        $request = CheckoutRequest::factory()->create([
            'user_id' => $user->id,
            'requestable_id' => $accessory->id,
            'requestable_type' => Accessory::class,
            'quantity' => 3,
        ]);

        event(new CheckoutableCheckedOut($accessory, $user, $actor, 'Current checkout', [], 1));

        $request->refresh();

        $this->assertSame(CheckoutRequestState::Pending, $request->state);
        $this->assertSame(1, $request->fulfilled_quantity);

        event(new CheckoutableCheckedOut($accessory, $user, $actor, 'Completing checkout', [], 2));

        $request->refresh();

        $this->assertSame(CheckoutRequestState::Fulfilled, $request->state);
        $this->assertSame(3, $request->fulfilled_quantity);
        $this->assertNotNull($request->checkout_actionlog_id);
        $this->assertDatabaseHas('action_logs', [
            'id' => $request->checkout_actionlog_id,
            'action_type' => 'checkout',
            'item_type' => Accessory::class,
            'item_id' => $accessory->id,
            'target_type' => User::class,
            'target_id' => $user->id,
            'note' => 'Completing checkout',
        ]);
    }

    public function test_checkout_fulfills_matching_open_component_request(): void
    {
        // Components check out to Assets in the normal flow, but
        // the listener only fires for user-target checkouts (a
        // request is always user-owned). Simulate the user-target
        // shape a direct event dispatch produces.
        $user = User::factory()->create();
        $component = Component::factory()->create();
        $request = CheckoutRequest::factory()->create([
            'user_id' => $user->id,
            'requestable_id' => $component->id,
            'requestable_type' => Component::class,
        ]);

        event(new CheckoutableCheckedOut($component, $user, User::factory()->create(), null));

        $this->assertSame(CheckoutRequestState::Fulfilled, $request->fresh()->state);
    }

    public function test_license_seat_checkout_fulfills_matching_open_license_request(): void
    {
        // License requests are filed against License, not
        // LicenseSeat. The listener has to translate the event's
        // LicenseSeat back to its parent License for the lookup to
        // hit.
        $user = User::factory()->create();
        $license = License::factory()->create(['seats' => 5]);
        $licenseSeat = LicenseSeat::where('license_id', $license->id)->first();
        $request = CheckoutRequest::factory()->create([
            'user_id' => $user->id,
            'requestable_id' => $license->id,
            'requestable_type' => License::class,
        ]);

        event(new CheckoutableCheckedOut($licenseSeat, $user, User::factory()->create(), null));

        $this->assertSame(CheckoutRequestState::Fulfilled, $request->fresh()->state);
    }

    public function test_checkout_to_different_user_does_not_fulfill_request(): void
    {
        // The listener matches on user_id. A checkout to someone
        // OTHER than the requester must leave the request open so
        // the requester can still get their turn.
        $requester = User::factory()->create();
        $someoneElse = User::factory()->create();
        $accessory = Accessory::factory()->create();
        $request = CheckoutRequest::factory()->create([
            'user_id' => $requester->id,
            'requestable_id' => $accessory->id,
            'requestable_type' => Accessory::class,
        ]);

        event(new CheckoutableCheckedOut($accessory, $someoneElse, User::factory()->create(), null));

        $this->assertSame(CheckoutRequestState::Pending, $request->fresh()->state);
    }

    public function test_checkout_to_location_does_not_fulfill_any_request(): void
    {
        // Location-target checkouts don't correspond to a
        // requester. Guard prevents a checkout to Location #5 from
        // silently closing every request whose user_id happens to
        // equal 5.
        $user = User::factory()->create();
        $asset = Asset::factory()->create();
        $request = CheckoutRequest::factory()->create([
            'user_id' => $user->id,
            'requestable_id' => $asset->id,
            'requestable_type' => Asset::class,
        ]);

        $location = \App\Models\Location::factory()->create();
        event(new CheckoutableCheckedOut($asset, $location, User::factory()->create(), null));

        $this->assertSame(CheckoutRequestState::Pending, $request->fresh()->state);
    }

    public function test_canceled_request_is_not_resurrected_by_a_later_checkout(): void
    {
        // Terminal-state guard test at the listener level. A user
        // cancels their request, then admin later checks out to
        // them anyway (unrelated to the request). The canceled row
        // must stay canceled - the listener's fulfillment lookup
        // filters on state=pending so it doesn't even reach the
        // canceled row.
        $user = User::factory()->create();
        $accessory = Accessory::factory()->create();
        $request = CheckoutRequest::factory()->create([
            'user_id' => $user->id,
            'requestable_id' => $accessory->id,
            'requestable_type' => Accessory::class,
        ]);
        $accessory->cancelRequest($user->id);

        event(new CheckoutableCheckedOut($accessory, $user, User::factory()->create(), null));

        $this->assertSame(CheckoutRequestState::Canceled, $request->fresh()->state);
    }
}
