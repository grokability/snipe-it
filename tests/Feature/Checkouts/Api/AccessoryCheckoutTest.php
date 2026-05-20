<?php

namespace Tests\Feature\Checkouts\Api;

use App\Mail\CheckoutAccessoryMail;
use App\Models\Accessory;
use App\Models\Actionlog;
use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Tests\Concerns\TestsPermissionsRequirement;
use Tests\TestCase;

class AccessoryCheckoutTest extends TestCase implements TestsPermissionsRequirement
{
    public function test_requires_permission()
    {
        $this->actingAsForApi(User::factory()->create())
            ->postJson(route('api.accessories.checkout', Accessory::factory()->create()))
            ->assertForbidden();
    }

    public function test_validation_when_checking_out_accessory()
    {
        $this->actingAsForApi(User::factory()->checkoutAccessories()->create())
            ->postJson(route('api.accessories.checkout', Accessory::factory()->create()), [
                // missing assigned_user, assigned_location, assigned_asset
            ])
            ->assertStatusMessageIs('error');
    }

    public function test_accessory_must_be_available_when_checking_out()
    {
        $this->actingAsForApi(User::factory()->checkoutAccessories()->create())
            ->postJson(route('api.accessories.checkout', Accessory::factory()->withoutItemsRemaining()->create()), [
                'assigned_user' => User::factory()->create()->id,
                'checkout_to_type' => 'user',
            ])
            ->assertOk()
            ->assertStatusMessageIs('error')
            ->assertJson(
                [
                    'status' => 'error',
                    'messages' => [
                        'checkout_qty' => [
                            trans_choice('admin/accessories/message.checkout.checkout_qty.lte', 0,
                                [
                                    'number_currently_remaining' => 0,
                                    'checkout_qty' => 1,
                                    'number_remaining_after_checkout' => 0,
                                ]),
                        ],

                    ],
                    'payload' => null,
                ])
            ->assertStatus(200)
            ->json();
    }

    public function test_accessory_can_be_checked_out_without_qty()
    {
        $accessory = Accessory::factory()->create();
        $user = User::factory()->create();
        $admin = User::factory()->checkoutAccessories()->create();

        $this->actingAsForApi($admin)
            ->postJson(route('api.accessories.checkout', $accessory), [
                'assigned_user' => $user->id,
                'checkout_to_type' => 'user',
            ])
            ->assertOk()
            ->assertStatusMessageIs('success')
            ->assertStatus(200)
            ->assertJson(['messages' => trans('admin/accessories/message.checkout.success')])
            ->json();

        $this->assertTrue($accessory->checkouts()->where('assigned_type', User::class)->where('assigned_to', $user->id)->count() > 0);

        $this->assertEquals(
            1,
            Actionlog::where([
                'action_type' => 'checkout',
                'target_id' => $user->id,
                'target_type' => User::class,
                'item_id' => $accessory->id,
                'item_type' => Accessory::class,
                'created_by' => $admin->id,
            ])->count(), 'Log entry either does not exist or there are more than expected'
        );
        $this->assertHasTheseActionLogs($accessory, ['create', 'checkout']);
    }

    public function test_accessory_can_be_checked_out_with_qty()
    {
        $accessory = Accessory::factory()->create(['qty' => 20]);
        $user = User::factory()->create();
        $admin = User::factory()->checkoutAccessories()->create();

        $this->actingAsForApi($admin)
            ->postJson(route('api.accessories.checkout', $accessory), [
                'assigned_user' => $user->id,
                'checkout_to_type' => 'user',
                'checkout_qty' => 2,
            ])
            ->assertOk()
            ->assertStatusMessageIs('success')
            ->assertStatus(200)
            ->assertJson(['messages' => trans('admin/accessories/message.checkout.success')])
            ->json();

        $this->assertTrue($accessory->checkouts()->where('assigned_type', User::class)->where('assigned_to', $user->id)->count() > 0);

        $this->assertDatabaseHas('action_logs', [
            'action_type' => 'checkout',
            'target_id' => $user->id,
            'target_type' => User::class,
            'item_id' => $accessory->id,
            'item_type' => Accessory::class,
            'quantity' => 2,
            'created_by' => $admin->id,
        ]);

        $this->assertHasTheseActionLogs($accessory, ['create', 'checkout']);
    }

    public function test_accessory_cannot_be_checked_out_to_invalid_user()
    {
        $accessory = Accessory::factory()->create();
        $user = User::factory()->create();

        $this->actingAsForApi(User::factory()->checkoutAccessories()->create())
            ->postJson(route('api.accessories.checkout', $accessory), [
                'assigned_user' => 'invalid-user-id',
                'checkout_to_type' => 'user',
                'note' => 'oh hi there',
            ])
            ->assertOk()
            ->assertStatusMessageIs('error')
            ->assertStatus(200)
            ->json();

        $this->assertFalse($accessory->checkouts()->where('assigned_type', User::class)->where('assigned_to', $user->id)->count() > 0);
    }

    public function test_user_sent_notification_upon_checkout()
    {
        Mail::fake();

        $accessory = Accessory::factory()->requiringAcceptance()->create();
        $user = User::factory()->create();

        $this->actingAsForApi(User::factory()->checkoutAccessories()->create())
            ->postJson(route('api.accessories.checkout', $accessory), [
                'assigned_user' => $user->id,
                'checkout_to_type' => 'user',
            ]);

        Mail::assertSent(CheckoutAccessoryMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });
    }

    public function test_action_log_created_upon_checkout()
    {
        $accessory = Accessory::factory()->create();
        $actor = User::factory()->checkoutAccessories()->create();
        $user = User::factory()->create();

        $this->actingAsForApi($actor)
            ->postJson(route('api.accessories.checkout', $accessory), [
                'assigned_user' => $user->id,
                'checkout_to_type' => 'user',
                'note' => 'oh hi there',
            ]);

        $this->assertEquals(
            1,
            Actionlog::where([
                'action_type' => 'checkout',
                'target_id' => $user->id,
                'target_type' => User::class,
                'item_id' => $accessory->id,
                'item_type' => Accessory::class,
                'created_by' => $actor->id,
                'note' => 'oh hi there',
            ])->count(),
            'Log entry either does not exist or there are more than expected'
        );
        $this->assertHasTheseActionLogs($accessory, ['create', 'checkout']);

    }

    public function test_superuser_cannot_checkout_accessory_to_a_target_in_another_company_when_full_company_support_is_enabled()
    {
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $superuser = User::factory()->superuser()->create(['company_id' => null]);
        $accessoryInCompanyA = Accessory::factory()->for($companyA)->create(['qty' => 1]);
        $userInCompanyB = User::factory()->for($companyB)->create();

        $this->actingAsForApi($superuser)
            ->postJson(route('api.accessories.checkout', $accessoryInCompanyA), [
                'assigned_user' => $userInCompanyB->id,
                'checkout_to_type' => 'user',
                'checkout_qty' => 1,
            ])
            ->assertOk()
            ->assertStatusMessageIs('error')
            ->assertMessagesAre(trans('general.error_user_company'));

        $this->assertDatabaseMissing('accessories_checkout', [
            'accessory_id' => $accessoryInCompanyA->id,
            'assigned_to' => $userInCompanyB->id,
            'assigned_type' => User::class,
        ]);

        $this->assertDatabaseMissing('action_logs', [
            'created_by' => $superuser->id,
            'action_type' => 'checkout',
            'target_type' => User::class,
            'target_id' => $userInCompanyB->id,
            'item_type' => Accessory::class,
            'item_id' => $accessoryInCompanyA->id,
        ]);

        $this->assertEquals(1, $accessoryInCompanyA->fresh()->numRemaining());
    }

    public function test_user_in_same_company_can_checkout_accessory_when_full_company_support_is_enabled()
    {
        $this->settings->enableMultipleFullCompanySupport();

        $company = Company::factory()->create();
        $accessory = Accessory::factory()->for($company)->create(['qty' => 5]);
        $target = $company->users()->save(User::factory()->make());
        $actor = User::factory()->superuser()->create();

        $this->actingAsForApi($actor)
            ->postJson(route('api.accessories.checkout', $accessory), [
                'assigned_user' => $target->id,
                'checkout_to_type' => 'user',
            ])
            ->assertOk()
            ->assertStatusMessageIs('success');
    }

    public function test_user_in_multiple_companies_can_checkout_accessory_from_any_of_their_companies_when_full_company_support_is_enabled()
    {
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();
        $target = User::factory()->create();
        $target->companies()->sync([$companyA->id, $companyB->id]);

        $accessoryInA = Accessory::factory()->for($companyA)->create(['qty' => 5]);
        $accessoryInB = Accessory::factory()->for($companyB)->create(['qty' => 5]);
        $actor = User::factory()->superuser()->create();

        $this->actingAsForApi($actor)
            ->postJson(route('api.accessories.checkout', $accessoryInA), [
                'assigned_user' => $target->id,
                'checkout_to_type' => 'user',
            ])
            ->assertOk()
            ->assertStatusMessageIs('success');

        $this->actingAsForApi($actor)
            ->postJson(route('api.accessories.checkout', $accessoryInB), [
                'assigned_user' => $target->id,
                'checkout_to_type' => 'user',
            ])
            ->assertOk()
            ->assertStatusMessageIs('success');
    }
}
