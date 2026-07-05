<?php

namespace Tests\Feature\Checkouts\Api;

use App\Mail\CheckoutConsumableMail;
use App\Models\Actionlog;
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

        $superuser = User::factory()->superuser()->create(['company_id' => null]);
        $consumableInCompanyA = Consumable::factory()->for($companyA)->create(['qty' => 1]);
        $userInCompanyB = User::factory()->for($companyB)->create();

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
}
