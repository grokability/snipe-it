<?php

namespace Tests\Feature\Requests\Ui;

use App\Models\Accessory;
use App\Models\CheckoutRequest;
use App\Models\User;
use Tests\TestCase;

class AccessoryRequestTest extends TestCase
{
    public function test_requestable_index_lists_requestable_accessories(): void
    {
        // The accessories tab on /account/requestable is now
        // API-backed via api.accessories.requestable. Shell page still
        // needs a non-empty $accessories view var so the tab-badge
        // count renders (0 hides the tab entirely), and the API row
        // shape has to include the requestable one + omit the
        // non-requestable one.
        $requestableAccessory = Accessory::factory()->create(['requestable' => true]);
        $nonRequestable = Accessory::factory()->create(['requestable' => false]);

        $this->actingAs(User::factory()->create())
            ->get(route('account.requestable'))
            ->assertOk()
            ->assertViewHas('counts', fn ($counts) => ($counts['accessories'] ?? 0) > 0);

        $rows = $this->actingAsForApi(User::factory()->viewRequestableAssets()->create())
            ->getJson(route('api.accessories.requestable'))
            ->assertOk()
            ->json('rows');

        $ids = collect($rows)->pluck('id')->all();
        $this->assertContains($requestableAccessory->id, $ids);
        $this->assertNotContains($nonRequestable->id, $ids);
    }

    public function test_user_can_request_a_requestable_accessory(): void
    {
        $accessory = Accessory::factory()->create(['requestable' => true]);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('account/request-item', [
                'itemType' => 'accessory',
                'itemId' => $accessory->id,
            ]), ['request-quantity' => 3])
            ->assertRedirect();

        $request = CheckoutRequest::where('requestable_id', $accessory->id)
            ->where('requestable_type', Accessory::class)
            ->where('user_id', $user->id)
            ->first();

        $this->assertNotNull($request, 'A checkout request should have been created for the accessory.');
        $this->assertEquals(3, $request->quantity, 'The requested quantity should be persisted.');
        $this->assertNull($request->canceled_at);
    }

    public function test_user_cannot_request_an_accessory_that_is_not_requestable(): void
    {
        $accessory = Accessory::factory()->create(['requestable' => false]);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('account/request-item', [
                'itemType' => 'accessory',
                'itemId' => $accessory->id,
            ]))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertNull(
            CheckoutRequest::where('requestable_id', $accessory->id)
                ->where('requestable_type', Accessory::class)
                ->first()
        );
    }

    public function test_user_can_cancel_their_own_accessory_request(): void
    {
        $accessory = Accessory::factory()->create(['requestable' => true]);
        $user = User::factory()->create();
        CheckoutRequest::factory()->create([
            'requestable_id' => $accessory->id,
            'requestable_type' => Accessory::class,
            'user_id' => $user->id,
        ]);

        $this->actingAs($user)
            ->post(route('account/request-item', [
                'itemType' => 'accessory',
                'itemId' => $accessory->id,
            ]))
            ->assertRedirect();

        $this->assertNotNull(
            CheckoutRequest::where('requestable_id', $accessory->id)
                ->where('requestable_type', Accessory::class)
                ->where('user_id', $user->id)
                ->whereNotNull('canceled_at')
                ->first()
        );
    }

    public function test_user_can_request_an_accessory_with_a_reservation_window(): void
    {
        // Reservation dates are optional (persist the "start blank ->
        // no window" story), but when supplied both should land on the
        // CheckoutRequest row so the future calendar-events pipeline
        // has something to hydrate from.
        $accessory = Accessory::factory()->create(['requestable' => true]);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('account/request-item', [
                'itemType' => 'accessory',
                'itemId' => $accessory->id,
            ]), [
                'request-quantity' => 2,
                'start_date' => '2026-09-01',
                'end_date' => '2026-09-05',
            ])
            ->assertRedirect();

        $request = CheckoutRequest::where('requestable_id', $accessory->id)
            ->where('requestable_type', Accessory::class)
            ->where('user_id', $user->id)
            ->first();

        $this->assertNotNull($request);
        $this->assertSame('2026-09-01', $request->start_date->toDateString());
        $this->assertSame('2026-09-05', $request->end_date->toDateString());
    }

    public function test_user_can_attach_notes_to_an_accessory_request(): void
    {
        // Notes are optional but persist end-to-end when supplied: on
        // the row for admin-queue rendering and via the notification
        // for the fulfillment email/slack.
        $accessory = Accessory::factory()->create(['requestable' => true]);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('account/request-item', [
                'itemType' => 'accessory',
                'itemId' => $accessory->id,
            ]), [
                'request-quantity' => 1,
                'notes' => 'Needed for the Q3 offsite',
            ])
            ->assertRedirect();

        $request = CheckoutRequest::where('requestable_id', $accessory->id)
            ->where('requestable_type', Accessory::class)
            ->where('user_id', $user->id)
            ->first();

        $this->assertNotNull($request);
        $this->assertSame('Needed for the Q3 offsite', $request->notes);
    }

    public function test_blank_notes_do_not_persist_as_empty_string(): void
    {
        // An untouched textarea shouldn't create an empty "Additional
        // Notes" block in the admin's email. Controller coerces the
        // empty string to null.
        $accessory = Accessory::factory()->create(['requestable' => true]);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('account/request-item', [
                'itemType' => 'accessory',
                'itemId' => $accessory->id,
            ]), [
                'request-quantity' => 1,
                'notes' => '',
            ])
            ->assertRedirect();

        $request = CheckoutRequest::where('requestable_id', $accessory->id)
            ->where('requestable_type', Accessory::class)
            ->where('user_id', $user->id)
            ->first();

        $this->assertNotNull($request);
        $this->assertNull($request->notes);
    }

    public function test_request_is_rejected_when_end_date_precedes_start_date(): void
    {
        $accessory = Accessory::factory()->create(['requestable' => true]);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('account/request-item', [
                'itemType' => 'accessory',
                'itemId' => $accessory->id,
            ]), [
                'request-quantity' => 1,
                'start_date' => '2026-09-10',
                'end_date' => '2026-09-05',
            ])
            ->assertRedirect()
            ->assertSessionHasErrors('end_date');

        $this->assertNull(
            CheckoutRequest::where('requestable_id', $accessory->id)
                ->where('requestable_type', Accessory::class)
                ->first(),
            'A request with an inverted date range must not persist.'
        );
    }

    public function test_admin_requested_api_endpoint_includes_accessory_requests(): void
    {
        // The admin "Requested" queue is a polymorphic list of all
        // checkout requests. It renders from GET /api/v1/requests
        // (the /hardware/requested Blade page is now a shell that
        // hydrates from the same endpoint), so this test asserts on
        // the API row rather than the server-rendered HTML.
        $accessory = Accessory::factory()->create(['requestable' => true]);
        $request = CheckoutRequest::factory()->create([
            'requestable_id' => $accessory->id,
            'requestable_type' => Accessory::class,
            'user_id' => User::factory()->create()->id,
        ]);

        $rows = $this->actingAsForApi(User::factory()->checkoutAccessories()->create())
            ->getJson(route('api.requests.index'))
            ->assertOk()
            ->json('rows');

        $row = collect($rows)->firstWhere('id', $request->id);
        $this->assertNotNull($row);
        $this->assertSame($accessory->name, $row['requestable']['name']);
        $this->assertSame('Accessory', $row['requestable']['type']);
    }
}
