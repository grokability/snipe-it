<?php

namespace Tests\Feature\Accessories\Ui;

use App\Events\CheckoutableCheckedIn;
use App\Models\Accessory;
use App\Models\AccessoryCheckout;
use App\Models\Company;
use App\Models\Location;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

/**
 * Adversarial cross-company tests for every bulk endpoint added or
 * touched in the accessories bulk + archive feature slice. Mirrors
 * BulkComponentsFmcsScopingTest. Belt-and-braces: the controllers
 * route through auto-scoped Accessory queries, so cross-company ids
 * should never reach the per-row handlers. Pin the behavior anyway
 * because any future refactor that bypasses the CompanyableScope
 * would otherwise silently open a cross-tenant hole.
 */
class BulkAccessoriesFmcsScopingTest extends TestCase
{
    public function test_bulk_delete_cannot_delete_cross_company_accessory_under_fmcs(): void
    {
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();
        $actor = $companyA->users()->save(User::factory()->editAccessories()->deleteAccessories()->viewAccessories()->make());
        $target = Accessory::factory()->for($companyB)->create();

        $this->actingAs($actor)
            ->post(route('accessories.bulkdelete.store'), ['ids' => [$target->id]]);

        $this->assertNull($target->fresh()->deleted_at);
    }

    public function test_bulk_edit_dispatcher_does_not_leak_cross_company_accessory_under_fmcs(): void
    {
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();
        $actor = $companyA->users()->save(User::factory()->viewAccessories()->editAccessories()->checkinAccessories()->make());

        $visible = Accessory::factory()->for($companyA)->create(['name' => 'Visible']);
        $hidden = Accessory::factory()->for($companyB)->create(['name' => 'Hidden']);

        $response = $this->actingAs($actor)
            ->post(route('accessories.bulkedit.show'), [
                'ids' => [$visible->id, $hidden->id],
                'bulk_actions' => 'edit',
            ])
            ->assertOk()
            ->assertViewIs('accessories.bulk-edit');

        $accessories = $response->viewData('accessories');
        $this->assertTrue($accessories->contains('id', $visible->id));
        $this->assertFalse($accessories->contains('id', $hidden->id));
    }

    public function test_bulk_update_cannot_write_cross_company_accessory_under_fmcs(): void
    {
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();
        $actor = $companyA->users()->save(User::factory()->editAccessories()->viewAccessories()->make());

        $target = Accessory::factory()->for($companyB)->create();
        $originalName = $target->name;

        $this->actingAs($actor)
            ->post(route('accessories.bulkedit.store'), [
                'ids' => [$target->id],
                'archived' => '1',
            ]);

        $fresh = $target->fresh();
        $this->assertSame($originalName, $fresh->name);
        $this->assertFalse((bool) $fresh->archived);
    }

    public function test_bulk_edit_rejects_write_to_cross_company_company_id_under_fmcs(): void
    {
        // Even if a non-superuser hand-crafted a POST with a company_id
        // outside their pivot set, the fmcs_company validator + policy
        // layer should block the write.
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();
        $actor = $companyA->users()->save(User::factory()->editAccessories()->viewAccessories()->make());
        $target = Accessory::factory()->for($companyA)->create();

        $this->actingAs($actor)
            ->post(route('accessories.bulkedit.store'), [
                'ids' => [$target->id],
                'company_id' => (string) $companyB->id,
            ]);

        $this->assertSame($companyA->id, $target->fresh()->company_id);
    }

    public function test_bulk_edit_rejects_cross_company_location_under_scope_locations_fmcs(): void
    {
        // scope_locations_fmcs=1 means a location "belongs" to the
        // companies in its parent chain. fmcs_location validator must
        // reject a bulk-edit that pairs location X with company Y when
        // X doesn't live in Y.
        [$companyA, $companyB] = Company::factory()->count(2)->create();
        $actor = $companyA->users()->save(User::factory()->editAccessories()->viewAccessories()->make());
        $homeLocation = Location::factory()->for($companyA)->create();
        $target = Accessory::factory()->for($companyA)->create(['location_id' => $homeLocation->id]);
        $foreignLocation = Location::factory()->for($companyB)->create();

        // Flip scope_locations_fmcs AFTER factory setup so cross-company
        // location-vs-company validation is in force only for the actual
        // bulk-update request, not during the test's own data seeding.
        $this->settings->enableScopedLocationsWithFullMultipleCompanySupport();

        $this->actingAs($actor)
            ->post(route('accessories.bulkedit.store'), [
                'ids' => [$target->id],
                'location_id' => (string) $foreignLocation->id,
            ]);

        $this->assertSame($homeLocation->id, $target->fresh()->location_id);
    }

    public function test_bulk_checkin_cannot_check_in_cross_company_checkout_under_fmcs(): void
    {
        // Floater off: company A actor should not be able to check in
        // checkouts whose accessory belongs to company B, even if they
        // post the checkout id by hand (the row isn't in their table).
        Event::fake([CheckoutableCheckedIn::class]);

        $this->settings->enableMultipleFullCompanySupport();
        $this->settings->disableFloaterMode();

        [$companyA, $companyB] = Company::factory()->count(2)->create();
        $actor = $companyA->users()->save(User::factory()->viewAccessories()->editAccessories()->checkinAccessories()->make());

        $foreignAccessory = Accessory::factory()->for($companyB)->create();
        $foreignCheckout = AccessoryCheckout::factory()->for($foreignAccessory, 'accessory')->create();

        $this->actingAs($actor)
            ->post(route('accessories.bulkcheckin.store'), ['ids' => [$foreignCheckout->id]]);

        $this->assertDatabaseHas('accessories_checkout', ['id' => $foreignCheckout->id]);
        Event::assertNothingDispatched();
    }

    public function test_bulk_checkin_show_does_not_leak_cross_company_checkout_under_fmcs(): void
    {
        // The interstitial view must also filter the selection: posting
        // a cross-tenant id to .show should either redirect with a
        // warning or render an interstitial WITHOUT the foreign row.
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();
        $actor = $companyA->users()->save(User::factory()->viewAccessories()->checkinAccessories()->make());

        $foreignAccessory = Accessory::factory()->for($companyB)->create();
        $foreignCheckout = AccessoryCheckout::factory()->for($foreignAccessory, 'accessory')->create();

        $this->actingAs($actor)
            ->post(route('accessories.bulkcheckin.show'), ['ids' => [$foreignCheckout->id]])
            ->assertRedirect()
            ->assertSessionHas('warning');
    }

    public function test_bulk_checkin_floater_can_checkin_null_company_checkout(): void
    {
        // Floater ON: company A actor is allowed to touch null-company
        // (floater) accessories. Pin so a future refactor that over-
        // tightens the subquery doesn't block a legitimate flow.
        Event::fake([CheckoutableCheckedIn::class]);

        $this->settings->enableFloaterMode();

        $companyA = Company::factory()->create();
        $actor = $companyA->users()->save(User::factory()->viewAccessories()->editAccessories()->checkinAccessories()->make());

        $floater = Accessory::factory()->create(['company_id' => null]);
        $checkout = AccessoryCheckout::factory()->for($floater, 'accessory')->create();

        $this->actingAs($actor)
            ->post(route('accessories.bulkcheckin.store'), ['ids' => [$checkout->id]])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('accessories_checkout', ['id' => $checkout->id]);
        Event::assertDispatched(CheckoutableCheckedIn::class);
    }

    public function test_unarchive_cannot_touch_cross_company_accessory_under_fmcs(): void
    {
        // postUnarchive relies on route-model binding (which runs the
        // Accessory CompanyableScope) + $this->authorize('update', $accessory).
        // A cross-tenant target is either scoped out at binding time or
        // forbidden by the policy. Either way the archived bit must not
        // flip - that's the actual contract.
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();
        $actor = $companyA->users()->save(User::factory()->editAccessories()->viewAccessories()->make());
        $foreign = Accessory::factory()->for($companyB)->create(['archived' => true]);

        $this->actingAs($actor)
            ->post(route('accessories.unarchive', $foreign));

        $this->assertTrue((bool) $foreign->fresh()->archived);
    }
}
