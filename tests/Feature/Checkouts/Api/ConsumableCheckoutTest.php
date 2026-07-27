<?php

namespace Tests\Feature\Checkouts\Api;

use App\Mail\CheckoutConsumableMail;
use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\Company;
use App\Models\Consumable;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ConsumableCheckoutTest extends TestCase
{
    public function test_checking_out_consumable_requires_correct_permission()
    {
        $this->actingAsForApi(User::factory()->create())
            ->postJson(route('api.consumables.checkout', Consumable::factory()->create()))
            ->assertForbidden();
    }

    public function test_validation_when_checking_out_consumable()
    {
        $this->actingAsForApi(User::factory()->checkoutConsumables()->create())
            ->postJson(route('api.consumables.checkout', Consumable::factory()->create()), [
                // missing assigned_to
            ])
            ->assertStatusMessageIs('error');
    }

    public function test_consumable_must_be_available_when_checking_out()
    {
        $this->actingAsForApi(User::factory()->checkoutConsumables()->create())
            ->postJson(route('api.consumables.checkout', Consumable::factory()->withoutItemsRemaining()->create()), [
                'assigned_to' => User::factory()->create()->id,
            ])
            ->assertStatusMessageIs('error');
    }

    public function test_consumable_can_be_checked_out()
    {
        $consumable = Consumable::factory()->create();
        $user = User::factory()->create();

        $this->actingAsForApi(User::factory()->checkoutConsumables()->create())
            ->postJson(route('api.consumables.checkout', $consumable), [
                'assigned_to' => $user->id,
            ]);

        $this->assertTrue($user->consumables->contains($consumable));
        $this->assertHasTheseActionLogs($consumable, ['create', 'checkout']);
    }

    public function test_consumable_can_be_checked_out_with_quantity()
    {
        $consumable = Consumable::factory()->create();
        $user = User::factory()->create();

        $this->actingAsForApi(User::factory()->checkoutConsumables()->create())
            ->postJson(route('api.consumables.checkout', $consumable), [
                'assigned_to' => $user->id,
                'checkout_qty' => 2,
            ]);

        $this->assertDatabaseHas('action_logs', [
            'item_type' => Consumable::class,
            'item_id' => $consumable->id,
            'target_type' => User::class,
            'target_id' => $user->id,
            'action_type' => 'checkout',
            'quantity' => 2,
        ]);
    }

    public function test_user_sent_notification_upon_checkout()
    {
        Mail::fake();

        $consumable = Consumable::factory()->requiringAcceptance()->create();

        $user = User::factory()->create();

        $this->actingAsForApi(User::factory()->checkoutConsumables()->create())
            ->postJson(route('api.consumables.checkout', $consumable), [
                'assigned_to' => $user->id,
            ]);

        Mail::assertSent(CheckoutConsumableMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });
    }

    public function test_pivot_row_created_by_is_the_actor_not_the_target()
    {
        // Regression: previously the pivot's created_by was set to $user->id
        // (the checkout target), so audit surfaces that read consumables_users
        // (e.g. "who checked this consumable out to me") would show the target
        // as their own creator. The action_logs stream separately recorded the
        // correct actor, which is why the pivot bug survived.
        $consumable = Consumable::factory()->create();
        $actor = User::factory()->checkoutConsumables()->create();
        $target = User::factory()->create();

        $this->actingAsForApi($actor)
            ->postJson(route('api.consumables.checkout', $consumable), [
                'assigned_to' => $target->id,
                'note' => 'created_by attribution regression',
            ]);

        $this->assertDatabaseHas('consumables_users', [
            'consumable_id' => $consumable->id,
            'assigned_to' => $target->id,
            'created_by' => $actor->id,
        ]);

        $this->assertDatabaseMissing('consumables_users', [
            'consumable_id' => $consumable->id,
            'assigned_to' => $target->id,
            'created_by' => $target->id,
        ]);
    }

    public function test_action_log_created_upon_checkout()
    {
        $consumable = Consumable::factory()->create();
        $actor = User::factory()->checkoutConsumables()->create();
        $user = User::factory()->create();

        $this->actingAsForApi($actor)
            ->postJson(route('api.consumables.checkout', $consumable), [
                'assigned_to' => $user->id,
                'note' => 'oh hi there',
            ]);

        $this->assertEquals(
            1,
            Actionlog::where([
                'action_type' => 'checkout',
                'target_id' => $user->id,
                'target_type' => User::class,
                'item_id' => $consumable->id,
                'item_type' => Consumable::class,
                'created_by' => $actor->id,
                'note' => 'oh hi there',
            ])->count(),
            'Log entry either does not exist or there are more than expected'
        );
    }

    public function test_superuser_cannot_checkout_consumable_to_a_user_in_another_company_when_full_company_support_is_enabled()
    {
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $superuser = User::factory()->superuser()->withoutCompany()->create();
        $consumableInCompanyA = Consumable::factory()->for($companyA)->create(['qty' => 1]);
        $userInCompanyB = User::factory()->forCompany($companyB)->create();

        $this->actingAsForApi($superuser)
            ->postJson(route('api.consumables.checkout', $consumableInCompanyA), [
                'assigned_to' => $userInCompanyB->id,
                'checkout_qty' => 1,
            ])
            ->assertOk()
            ->assertStatusMessageIs('error')
            ->assertMessagesAre(trans('general.error_user_company'));

        $this->assertDatabaseMissing('consumables_users', [
            'consumable_id' => $consumableInCompanyA->id,
            'assigned_to' => $userInCompanyB->id,
        ]);

        $this->assertDatabaseMissing('action_logs', [
            'created_by' => $superuser->id,
            'action_type' => 'checkout',
            'target_type' => User::class,
            'target_id' => $userInCompanyB->id,
            'item_type' => Consumable::class,
            'item_id' => $consumableInCompanyA->id,
        ]);

        $this->assertEquals(1, $consumableInCompanyA->fresh()->numRemaining());
    }

    public function test_user_in_same_company_can_checkout_consumable_when_full_company_support_is_enabled()
    {
        $this->settings->enableMultipleFullCompanySupport();

        $company = Company::factory()->create();
        $consumable = Consumable::factory()->for($company)->create(['qty' => 5]);
        $target = $company->users()->save(User::factory()->make());
        $actor = User::factory()->superuser()->create();

        $this->actingAsForApi($actor)
            ->postJson(route('api.consumables.checkout', $consumable), [
                'assigned_to' => $target->id,
            ])
            ->assertOk()
            ->assertStatusMessageIs('success');
    }

    public function test_user_in_multiple_companies_can_checkout_consumable_from_any_of_their_companies_when_full_company_support_is_enabled()
    {
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();
        $target = User::factory()->create();
        $target->companies()->sync([$companyA->id, $companyB->id]);

        $consumableInA = Consumable::factory()->for($companyA)->create(['qty' => 5]);
        $consumableInB = Consumable::factory()->for($companyB)->create(['qty' => 5]);
        $actor = User::factory()->superuser()->create();

        $this->actingAsForApi($actor)
            ->postJson(route('api.consumables.checkout', $consumableInA), [
                'assigned_to' => $target->id,
            ])
            ->assertOk()
            ->assertStatusMessageIs('success');

        $this->actingAsForApi($actor)
            ->postJson(route('api.consumables.checkout', $consumableInB), [
                'assigned_to' => $target->id,
            ])
            ->assertOk()
            ->assertStatusMessageIs('success');
    }

    /**
     * Security regression pin: the checkout endpoint used to read
     * numRemaining() outside any transaction/lock. Two racing requests
     * for a qty=1 consumable both saw "1 available", both attached
     * pivot rows, and the register landed at -1. The fix wraps the
     * pivot writes in a DB::transaction that begins with a
     * lockForUpdate re-fetch + re-check of numRemaining. This test
     * simulates the "someone else already grabbed the last one"
     * moment by pre-attaching pivot rows to drain the consumable to
     * zero before the checkout request runs, and asserts the endpoint
     * refuses instead of over-allocating.
     */
    public function test_checkout_refuses_when_inventory_is_already_exhausted(): void
    {
        $target = User::factory()->create();
        $consumable = Consumable::factory()->create(['qty' => 1]);

        // Drain the consumable via a direct pivot insert. Same state a
        // concurrent request would have left mid-transaction. assigned_type is
        // set explicitly so the row is a real user checkout visible to the
        // type-filtered users() relation, rather than relying on the column
        // default.
        $consumable->users()->attach($consumable->id, [
            'consumable_id' => $consumable->id,
            'assigned_to' => $target->id,
            'assigned_type' => User::class,
            'created_by' => User::factory()->superuser()->create()->id,
        ]);

        $this->assertSame(0, $consumable->fresh()->numRemaining());

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->postJson(route('api.consumables.checkout', $consumable), [
                'assigned_to' => $target->id,
                'checkout_qty' => 1,
            ])
            ->assertOk()
            ->assertStatusMessageIs('error');

        // The pre-drained row is the only pivot; no second row got added.
        // Count via consumableAssignments (type-agnostic) so this catches an
        // over-allocation regardless of whether the extra row is a user or
        // asset checkout.
        $this->assertSame(1, $consumable->consumableAssignments()->count(), 'A second pivot row would mean the register went negative');
        $this->assertSame(0, $consumable->fresh()->numRemaining());
    }

    public function test_user_checkout_records_user_assigned_type()
    {
        $consumable = Consumable::factory()->create();
        $user = User::factory()->create();
        $actor = User::factory()->checkoutConsumables()->create();

        $this->actingAsForApi($actor)
            ->postJson(route('api.consumables.checkout', $consumable), [
                'assigned_to' => $user->id,
            ])
            ->assertOk()
            ->assertStatusMessageIs('success');

        // The pivot row must record the polymorphic type, not just the id.
        $this->assertDatabaseHas('consumables_users', [
            'consumable_id' => $consumable->id,
            'assigned_to' => $user->id,
            'assigned_type' => User::class,
        ]);
    }

    public function test_consumable_can_be_checked_out_to_an_asset()
    {
        $consumable = Consumable::factory()->create(['qty' => 5]);
        $asset = Asset::factory()->create();
        $actor = User::factory()->checkoutConsumables()->create();

        $this->actingAsForApi($actor)
            ->postJson(route('api.consumables.checkout', $consumable), [
                'checkout_to_type' => 'asset',
                'assigned_asset' => $asset->id,
            ])
            ->assertOk()
            ->assertStatusMessageIs('success');

        $this->assertDatabaseHas('consumables_users', [
            'consumable_id' => $consumable->id,
            'assigned_to' => $asset->id,
            'assigned_type' => Asset::class,
            'created_by' => $actor->id,
        ]);

        $this->assertDatabaseHas('action_logs', [
            'item_type' => Consumable::class,
            'item_id' => $consumable->id,
            'target_type' => Asset::class,
            'target_id' => $asset->id,
            'action_type' => 'checkout',
        ]);

        // The asset row must count against availability (numCheckedOut now
        // counts all assignments, not just users()).
        $this->assertSame(4, $consumable->fresh()->numRemaining());
    }

    public function test_checking_out_consumable_to_nonexistent_asset_fails()
    {
        $consumable = Consumable::factory()->create();

        $this->actingAsForApi(User::factory()->checkoutConsumables()->create())
            ->postJson(route('api.consumables.checkout', $consumable), [
                'checkout_to_type' => 'asset',
                'assigned_asset' => 999999,
            ])
            ->assertOk()
            ->assertStatusMessageIs('error');

        $this->assertDatabaseMissing('consumables_users', [
            'consumable_id' => $consumable->id,
            'assigned_type' => Asset::class,
        ]);
    }

    public function test_superuser_cannot_checkout_consumable_to_an_asset_in_another_company_when_full_company_support_is_enabled()
    {
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $superuser = User::factory()->superuser()->withoutCompany()->create();
        $consumableInCompanyA = Consumable::factory()->for($companyA)->create(['qty' => 1]);
        $assetInCompanyB = Asset::factory()->for($companyB)->create();

        $this->actingAsForApi($superuser)
            ->postJson(route('api.consumables.checkout', $consumableInCompanyA), [
                'checkout_to_type' => 'asset',
                'assigned_asset' => $assetInCompanyB->id,
            ])
            ->assertOk()
            ->assertStatusMessageIs('error')
            ->assertMessagesAre(trans('general.error_user_company'));

        $this->assertDatabaseMissing('consumables_users', [
            'consumable_id' => $consumableInCompanyA->id,
            'assigned_to' => $assetInCompanyB->id,
            'assigned_type' => Asset::class,
        ]);

        $this->assertEquals(1, $consumableInCompanyA->fresh()->numRemaining());
    }
}
