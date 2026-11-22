<?php

namespace Tests\Feature\Checkouts\Ui;

use App\Enums\CheckoutRequestState;
use App\Events\CheckoutableCheckedOut;
use App\Models\Accessory;
use App\Models\Asset;
use App\Models\CheckoutRequest;
use App\Models\Company;
use App\Models\Location;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class BulkAccessoryCheckoutTest extends TestCase
{
    public function test_bulk_checkout_requires_checkout_permission(): void
    {
        $this->actingAs(User::factory()->viewAccessories()->create())
            ->post(route('accessories.bulkcheckout.store'), [
                'ids' => [Accessory::factory()->create()->id],
                'checkout_to_type' => 'user',
                'assigned_user' => User::factory()->create()->id,
            ])
            ->assertForbidden();
    }

    public function test_dispatcher_renders_bulk_checkout_view(): void
    {
        $accessories = Accessory::factory()->count(3)->create(['qty' => 5]);

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('accessories.bulkedit.show'), [
                'ids' => $accessories->pluck('id')->all(),
                'bulk_actions' => 'checkout',
            ])
            ->assertOk()
            ->assertViewIs('accessories.bulk-checkout')
            ->assertViewHas('accessories', fn ($v) => $v->count() === 3)
            ->assertViewHas('excluded', fn ($v) => $v->isEmpty());
    }

    public function test_dispatcher_partitions_archived_and_zero_remaining_into_excluded(): void
    {
        $live = Accessory::factory()->create(['qty' => 3]);
        $archived = Accessory::factory()->archived()->create(['qty' => 3]);
        $none = Accessory::factory()->withoutItemsRemaining()->create();

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('accessories.bulkedit.show'), [
                'ids' => [$live->id, $archived->id, $none->id],
                'bulk_actions' => 'checkout',
            ])
            ->assertOk()
            ->assertViewHas('accessories', fn ($v) => $v->count() === 1 && $v->first()->id === $live->id)
            ->assertViewHas('excluded', fn ($v) => $v->count() === 2);
    }

    public function test_dispatcher_redirects_when_nothing_is_selectable(): void
    {
        $archived = Accessory::factory()->archived()->create(['qty' => 3]);
        $none = Accessory::factory()->withoutItemsRemaining()->create();

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('accessories.bulkedit.show'), [
                'ids' => [$archived->id, $none->id],
                'bulk_actions' => 'checkout',
            ])
            ->assertRedirect(route('accessories.index'))
            ->assertSessionHas('error');
    }

    public function test_bulk_checkout_to_user_checks_out_each_accessory_with_qty_one(): void
    {
        Event::fake([CheckoutableCheckedOut::class]);

        $accessories = Accessory::factory()->count(3)->create(['qty' => 5]);
        $user = User::factory()->create();

        $this->actingAs(User::factory()->checkoutAccessories()->create())
            ->post(route('accessories.bulkcheckout.store'), [
                'ids' => $accessories->pluck('id')->all(),
                'checkout_to_type' => 'user',
                'assigned_user' => $user->id,
                'note' => 'onboarding',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        foreach ($accessories as $accessory) {
            $this->assertEquals(1, $accessory->checkouts()->where('assigned_to', $user->id)->where('assigned_type', User::class)->count());
        }

        Event::assertDispatchedTimes(CheckoutableCheckedOut::class, 3);
    }

    public function test_bulk_checkout_to_asset_writes_asset_pivot_rows(): void
    {
        $accessories = Accessory::factory()->count(2)->create(['qty' => 5]);
        $asset = Asset::factory()->create();

        $this->actingAs(User::factory()->checkoutAccessories()->create())
            ->post(route('accessories.bulkcheckout.store'), [
                'ids' => $accessories->pluck('id')->all(),
                'checkout_to_type' => 'asset',
                'assigned_asset' => $asset->id,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        foreach ($accessories as $accessory) {
            $this->assertEquals(1, $accessory->checkouts()->where('assigned_to', $asset->id)->where('assigned_type', Asset::class)->count());
        }
    }

    public function test_bulk_checkout_to_location_writes_location_pivot_rows(): void
    {
        $accessories = Accessory::factory()->count(2)->create(['qty' => 5]);
        $location = Location::factory()->create();

        $this->actingAs(User::factory()->checkoutAccessories()->create())
            ->post(route('accessories.bulkcheckout.store'), [
                'ids' => $accessories->pluck('id')->all(),
                'checkout_to_type' => 'location',
                'assigned_location' => $location->id,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        foreach ($accessories as $accessory) {
            $this->assertEquals(1, $accessory->checkouts()->where('assigned_to', $location->id)->where('assigned_type', Location::class)->count());
        }
    }

    public function test_controller_defensively_skips_archived_row_even_if_posted_directly(): void
    {
        // Row-level bulk_selectable.checkout=false disables the checkbox in
        // the UI, but tampering past the disabled state is still rejected here.
        Event::fake([CheckoutableCheckedOut::class]);

        $good = Accessory::factory()->create(['qty' => 5]);
        $archived = Accessory::factory()->archived()->create(['qty' => 5]);
        $user = User::factory()->create();

        $this->actingAs(User::factory()->checkoutAccessories()->create())
            ->post(route('accessories.bulkcheckout.store'), [
                'ids' => [$good->id, $archived->id],
                'checkout_to_type' => 'user',
                'assigned_user' => $user->id,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertEquals(1, $good->checkouts()->where('assigned_to', $user->id)->count());
        $this->assertEquals(0, $archived->checkouts()->where('assigned_to', $user->id)->count());
        Event::assertDispatchedTimes(CheckoutableCheckedOut::class, 1);
    }

    public function test_controller_defensively_skips_over_allocated_row(): void
    {
        $good = Accessory::factory()->create(['qty' => 5]);
        $full = Accessory::factory()->withoutItemsRemaining()->create();
        $user = User::factory()->create();

        $this->actingAs(User::factory()->checkoutAccessories()->create())
            ->post(route('accessories.bulkcheckout.store'), [
                'ids' => [$good->id, $full->id],
                'checkout_to_type' => 'user',
                'assigned_user' => $user->id,
            ]);

        $this->assertEquals(1, $good->checkouts()->where('assigned_to', $user->id)->count());
        $this->assertEquals(0, $full->checkouts()->where('assigned_to', $user->id)->where('assigned_type', User::class)->count());
    }

    public function test_bulk_checkout_skips_cross_company_accessory_under_strict_fmcs(): void
    {
        $this->settings->enableMultipleFullCompanySupport();
        $this->settings->disableFloaterMode();

        [$companyA, $companyB] = Company::factory()->count(2)->create();
        $actor = $companyA->users()->save(User::factory()->viewAccessories()->checkoutAccessories()->make());
        $targetUser = $companyA->users()->save(User::factory()->make());

        $own = Accessory::factory()->for($companyA)->create(['qty' => 5]);
        $foreign = Accessory::factory()->for($companyB)->create(['qty' => 5]);

        $this->actingAs($actor)
            ->post(route('accessories.bulkcheckout.store'), [
                'ids' => [$own->id, $foreign->id],
                'checkout_to_type' => 'user',
                'assigned_user' => $targetUser->id,
            ]);

        // Own-company accessory was checked out, cross-company was scoped
        // out before reaching the per-row loop at all (CompanyableScope).
        $this->assertEquals(1, $own->checkouts()->where('assigned_to', $targetUser->id)->count());
        $this->assertEquals(0, $foreign->checkouts()->where('assigned_to', $targetUser->id)->count());
    }

    public function test_bulk_checkout_dispatcher_does_not_leak_cross_company_rows_under_fmcs(): void
    {
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();
        $actor = $companyA->users()->save(User::factory()->viewAccessories()->checkoutAccessories()->make());

        $visible = Accessory::factory()->for($companyA)->create(['qty' => 5, 'name' => 'Visible']);
        $hidden = Accessory::factory()->for($companyB)->create(['qty' => 5, 'name' => 'Hidden']);

        $response = $this->actingAs($actor)
            ->post(route('accessories.bulkedit.show'), [
                'ids' => [$visible->id, $hidden->id],
                'bulk_actions' => 'checkout',
            ])
            ->assertOk()
            ->assertViewIs('accessories.bulk-checkout');

        $selectable = $response->viewData('accessories');
        $this->assertTrue($selectable->contains('id', $visible->id));
        $this->assertFalse($selectable->contains('id', $hidden->id));
    }

    public function test_bulk_checkout_floater_can_checkout_null_company_accessory(): void
    {
        $this->settings->enableFloaterMode();

        $companyA = Company::factory()->create();
        $actor = $companyA->users()->save(User::factory()->viewAccessories()->checkoutAccessories()->make());
        $targetUser = $companyA->users()->save(User::factory()->make());

        $floater = Accessory::factory()->create(['company_id' => null, 'qty' => 5]);

        $this->actingAs($actor)
            ->post(route('accessories.bulkcheckout.store'), [
                'ids' => [$floater->id],
                'checkout_to_type' => 'user',
                'assigned_user' => $targetUser->id,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertEquals(1, $floater->checkouts()->where('assigned_to', $targetUser->id)->count());
    }

    public function test_bulk_checkout_fulfills_open_request_for_target_user_and_accessory(): void
    {
        // Target user had an open request for one of the accessories in
        // the batch. The CheckoutableCheckedOut listener auto-closes
        // matching pending requests, so this should be covered as long
        // as the bulk flow keeps firing one event per successful row.
        $accessoryWithRequest = Accessory::factory()->create(['qty' => 5]);
        $otherAccessory = Accessory::factory()->create(['qty' => 5]);
        $user = User::factory()->create();

        $openRequest = CheckoutRequest::factory()->create([
            'requestable_type' => Accessory::class,
            'requestable_id' => $accessoryWithRequest->id,
            'user_id' => $user->id,
            'quantity' => 1,
        ]);

        $this->actingAs(User::factory()->checkoutAccessories()->create())
            ->post(route('accessories.bulkcheckout.store'), [
                'ids' => [$accessoryWithRequest->id, $otherAccessory->id],
                'checkout_to_type' => 'user',
                'assigned_user' => $user->id,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertNotSame(
            CheckoutRequestState::Pending,
            $openRequest->fresh()->state,
            'Open CheckoutRequest should no longer be pending after matching bulk-checkout.'
        );
    }

    public function test_bulk_checkout_skips_cross_company_location_target_under_scope_locations_fmcs(): void
    {
        [$companyA, $companyB] = Company::factory()->count(2)->create();
        $actor = $companyA->users()->save(User::factory()->viewAccessories()->checkoutAccessories()->make());
        $ownLocation = Location::factory()->for($companyA)->create();
        $foreignLocation = Location::factory()->for($companyB)->create();
        $accessory = Accessory::factory()->for($companyA)->create(['qty' => 5]);

        $this->settings->enableScopedLocationsWithFullMultipleCompanySupport();

        $this->actingAs($actor)
            ->post(route('accessories.bulkcheckout.store'), [
                'ids' => [$accessory->id],
                'checkout_to_type' => 'location',
                'assigned_location' => $foreignLocation->id,
            ]);

        $this->assertEquals(0, $accessory->checkouts()->where('assigned_type', Location::class)->where('assigned_to', $foreignLocation->id)->count());

        // Own-company location goes through fine, pinning that the
        // scoping didn't just block everything.
        $this->actingAs($actor)
            ->post(route('accessories.bulkcheckout.store'), [
                'ids' => [$accessory->id],
                'checkout_to_type' => 'location',
                'assigned_location' => $ownLocation->id,
            ])
            ->assertSessionHas('success');

        $this->assertEquals(1, $accessory->checkouts()->where('assigned_type', Location::class)->where('assigned_to', $ownLocation->id)->count());
    }
}
