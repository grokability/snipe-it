<?php

namespace Tests\Feature\Accessories\Ui;

use App\Events\CheckoutableCheckedIn;
use App\Models\Accessory;
use App\Models\AccessoryCheckout;
use App\Models\Actionlog;
use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class BulkCheckinAccessoriesTest extends TestCase
{
    public function test_requires_checkin_permission_on_show(): void
    {
        $accessory = Accessory::factory()->create();
        $checkout = AccessoryCheckout::factory()->for($accessory, 'accessory')->create();

        $this->actingAs(User::factory()->viewAccessories()->create())
            ->post(route('accessories.bulkcheckin.show'), ['ids' => [$checkout->id]])
            ->assertForbidden();

        $this->assertDatabaseHas('accessories_checkout', ['id' => $checkout->id]);
    }

    public function test_requires_checkin_permission_on_store(): void
    {
        $accessory = Accessory::factory()->create();
        $checkout = AccessoryCheckout::factory()->for($accessory, 'accessory')->create();

        $this->actingAs(User::factory()->viewAccessories()->create())
            ->post(route('accessories.bulkcheckin.store'), ['ids' => [$checkout->id]])
            ->assertForbidden();

        $this->assertDatabaseHas('accessories_checkout', ['id' => $checkout->id]);
    }

    public function test_show_renders_interstitial_with_selected_rows(): void
    {
        $accessory = Accessory::factory()->create();
        $checkoutA = AccessoryCheckout::factory()->for($accessory, 'accessory')->create();
        $checkoutB = AccessoryCheckout::factory()->for($accessory, 'accessory')->create();

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('accessories.bulkcheckin.show'), ['ids' => [$checkoutA->id, $checkoutB->id]])
            ->assertOk()
            ->assertViewIs('accessories.bulk-checkin')
            ->assertViewHas('ids', [$checkoutA->id, $checkoutB->id]);

        $this->assertDatabaseHas('accessories_checkout', ['id' => $checkoutA->id]);
        $this->assertDatabaseHas('accessories_checkout', ['id' => $checkoutB->id]);
    }

    public function test_show_warns_when_no_rows_selected(): void
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('accessories.bulkcheckin.show'), ['ids' => []])
            ->assertRedirect()
            ->assertSessionHas('warning');
    }

    public function test_store_removes_checkout_rows_and_fires_event(): void
    {
        Event::fake([CheckoutableCheckedIn::class]);

        $accessory = Accessory::factory()->create();
        $checkoutA = AccessoryCheckout::factory()->for($accessory, 'accessory')->create();
        $checkoutB = AccessoryCheckout::factory()->for($accessory, 'accessory')->create();

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('accessories.bulkcheckin.store'), ['ids' => [$checkoutA->id, $checkoutB->id]])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('accessories_checkout', ['id' => $checkoutA->id]);
        $this->assertDatabaseMissing('accessories_checkout', ['id' => $checkoutB->id]);

        Event::assertDispatchedTimes(CheckoutableCheckedIn::class, 2);
    }

    public function test_store_warns_when_no_rows_selected(): void
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('accessories.bulkcheckin.store'), ['ids' => []])
            ->assertRedirect()
            ->assertSessionHas('warning');
    }

    public function test_success_message_reports_count(): void
    {
        $accessory = Accessory::factory()->create();
        $a = AccessoryCheckout::factory()->for($accessory, 'accessory')->create();
        $b = AccessoryCheckout::factory()->for($accessory, 'accessory')->create();
        $c = AccessoryCheckout::factory()->for($accessory, 'accessory')->create();

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('accessories.bulkcheckin.store'), ['ids' => [$a->id, $b->id, $c->id]])
            ->assertRedirect()
            ->assertSessionHas('success', trans_choice(
                'admin/accessories/message.bulk.checkin_selected.success',
                3,
                ['count' => 3],
            ));
    }

    public function test_success_message_handles_single_row(): void
    {
        $accessory = Accessory::factory()->create();
        $checkout = AccessoryCheckout::factory()->for($accessory, 'accessory')->create();

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('accessories.bulkcheckin.store'), ['ids' => [$checkout->id]])
            ->assertRedirect()
            ->assertSessionHas('success', trans_choice(
                'admin/accessories/message.bulk.checkin_selected.success',
                1,
                ['count' => 1],
            ));
    }

    public function test_shared_note_lands_on_every_action_log(): void
    {
        // Regression test: the whole reason for the interstitial is to
        // capture a shared note. The posted `note` must thread into
        // every per-row CheckoutableCheckedIn event so each resulting
        // action_log row carries it.
        $accessory = Accessory::factory()->create();
        $a = AccessoryCheckout::factory()->for($accessory, 'accessory')->create();
        $b = AccessoryCheckout::factory()->for($accessory, 'accessory')->create();

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('accessories.bulkcheckin.store'), [
                'ids' => [$a->id, $b->id],
                'note' => 'End-of-lease returns, batch 2026-Q4',
            ])
            ->assertRedirect();

        $logs = Actionlog::where('item_type', Accessory::class)
            ->where('item_id', $accessory->id)
            ->where('action_type', 'checkin from')
            ->get();

        $this->assertCount(2, $logs);
        foreach ($logs as $log) {
            $this->assertSame('End-of-lease returns, batch 2026-Q4', $log->note);
        }
    }

    public function test_unknown_checkout_ids_are_silently_skipped(): void
    {
        // Stale rows posted from a dropdown-swap race just get skipped
        // so the real selections still check in cleanly.
        Event::fake([CheckoutableCheckedIn::class]);

        $accessory = Accessory::factory()->create();
        $checkout = AccessoryCheckout::factory()->for($accessory, 'accessory')->create();

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('accessories.bulkcheckin.store'), ['ids' => [$checkout->id, 999999]])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('accessories_checkout', ['id' => $checkout->id]);
        Event::assertDispatchedTimes(CheckoutableCheckedIn::class, 1);
    }

    public function test_cross_company_checkout_rows_are_filtered_under_fmcs(): void
    {
        // Regression test: AccessoryCheckout has no CompanyableTrait,
        // so defense in depth lives on the controller's accessory_id
        // subquery against Accessory's CompanyableScope. A hand-crafted
        // POST referencing a cross-tenant checkout must not succeed.
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();
        $actor = $companyA->users()->save(User::factory()->checkinAccessories()->viewAccessories()->make());
        $foreignAccessory = Accessory::factory()->for($companyB)->create();
        $foreignCheckout = AccessoryCheckout::factory()->for($foreignAccessory, 'accessory')->create();

        $this->actingAs($actor)
            ->post(route('accessories.bulkcheckin.store'), ['ids' => [$foreignCheckout->id]])
            ->assertRedirect();

        $this->assertDatabaseHas('accessories_checkout', ['id' => $foreignCheckout->id]);
    }
}
