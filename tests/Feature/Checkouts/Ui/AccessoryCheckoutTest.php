<?php

namespace Tests\Feature\Checkouts\Ui;

use App\Mail\CheckoutAccessoryMail;
use App\Models\Accessory;
use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\CheckoutAcceptance;
use App\Models\Location;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AccessoryCheckoutTest extends TestCase
{
    public function test_checking_out_accessory_requires_correct_permission()
    {
        $this->actingAs(User::factory()->create())
            ->post(route('accessories.checkout.store', Accessory::factory()->create()))
            ->assertForbidden();
    }

    public function test_page_renders()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('accessories.checkout.show', Accessory::factory()->create()))
            ->assertOk();
    }

    public function test_validation_when_checking_out_accessory()
    {
        $accessory = Accessory::factory()->create();
        $response = $this->actingAs(User::factory()->superuser()->create())
            ->from(route('accessories.checkout.show', $accessory))
            ->post(route('accessories.checkout.store', $accessory), [
                // missing assigned_to
            ])
            ->assertStatus(302)
            ->assertSessionHas('errors')
            ->assertRedirect(route('accessories.checkout.store', $accessory));

        $this->followRedirects($response)->assertSee(trans('general.error'));
    }

    public function test_accessory_must_have_available_items_for_checkout_when_checking_out()
    {

        $accessory = Accessory::factory()->withoutItemsRemaining()->create();
        $response = $this->actingAs(User::factory()->viewAccessories()->checkoutAccessories()->create())
            ->from(route('accessories.checkout.show', $accessory))
            ->post(route('accessories.checkout.store', $accessory), [
                'assigned_user' => User::factory()->create()->id,
                'checkout_to_type' => 'user',
            ])
            ->assertStatus(302)
            ->assertSessionHas('errors')
            ->assertRedirect(route('accessories.checkout.store', $accessory));
        $response->assertInvalid(['checkout_qty']);
        $this->followRedirects($response)->assertSee(trans('general.error'));
    }

    public function test_accessory_can_be_checked_out_without_quantity()
    {
        $accessory = Accessory::factory()->create();
        $user = User::factory()->create();

        $this->actingAs(User::factory()->checkoutAccessories()->create())
            ->post(route('accessories.checkout.store', $accessory), [
                'assigned_user' => $user->id,
                'checkout_to_type' => 'user',
                'note' => 'oh hi there',
            ]);

        $this->assertTrue($accessory->checkouts()->where('assigned_type', User::class)->where('assigned_to', $user->id)->count() > 0);

        $this->assertDatabaseHas('action_logs', [
            'action_type' => 'checkout',
            'target_id' => $user->id,
            'target_type' => User::class,
            'item_id' => $accessory->id,
            'item_type' => Accessory::class,
            'quantity' => 1,
            'note' => 'oh hi there',
        ]);
        $this->assertHasTheseActionLogs($accessory, ['create', 'checkout']);
    }

    public function test_accessory_can_be_checked_out_with_quantity()
    {
        $accessory = Accessory::factory()->create(['qty' => 5]);
        $user = User::factory()->create();

        $this->actingAs(User::factory()->checkoutAccessories()->create())
            ->from(route('accessories.checkout.show', $accessory))
            ->post(route('accessories.checkout.store', $accessory), [
                'assigned_user' => $user->id,
                'checkout_to_type' => 'user',
                'checkout_qty' => 3,
                'note' => 'oh hi there',
            ]);

        $this->assertTrue($accessory->checkouts()->where('assigned_type', User::class)->where('assigned_to', $user->id)->count() > 0);

        $this->assertDatabaseHas('action_logs', [
            'action_type' => 'checkout',
            'target_id' => $user->id,
            'target_type' => User::class,
            'item_id' => $accessory->id,
            'item_type' => Accessory::class,
            'quantity' => 3,
            'note' => 'oh hi there',
        ]);
        $this->assertHasTheseActionLogs($accessory, ['create', 'checkout']);
    }

    public function test_accessory_can_be_checked_out_to_location_with_quantity()
    {
        $accessory = Accessory::factory()->create(['qty' => 5]);
        $location = Location::factory()->create();

        $this->actingAs(User::factory()->checkoutAccessories()->create())
            ->from(route('accessories.checkout.show', $accessory))
            ->post(route('accessories.checkout.store', $accessory), [
                'assigned_location' => $location->id,
                'checkout_to_type' => 'location',
                'checkout_qty' => 3,
                'note' => 'oh hi there',
            ]);

        $this->assertTrue($accessory->checkouts()->where('assigned_type', Location::class)->where('assigned_to', $location->id)->count() > 0);

        $this->assertDatabaseHas('action_logs', [
            'action_type' => 'checkout',
            'target_id' => $location->id,
            'target_type' => Location::class,
            'item_id' => $accessory->id,
            'item_type' => Accessory::class,
            'quantity' => 3,
            'note' => 'oh hi there',
        ]);
        $this->assertHasTheseActionLogs($accessory, ['create', 'checkout']);
    }

    public function test_accessory_can_be_checked_out_to_asset_with_quantity()
    {
        $accessory = Accessory::factory()->create(['qty' => 5]);
        $asset = Asset::factory()->create();

        $this->actingAs(User::factory()->checkoutAccessories()->create())
            ->from(route('accessories.checkout.show', $accessory))
            ->post(route('accessories.checkout.store', $accessory), [
                'assigned_asset' => $asset->id,
                'checkout_to_type' => 'asset',
                'checkout_qty' => 3,
                'note' => 'oh hi there',
            ]);

        $this->assertTrue($accessory->checkouts()->where('assigned_type', Asset::class)->where('assigned_to', $asset->id)->count() > 0);

        $this->assertDatabaseHas('action_logs', [
            'action_type' => 'checkout',
            'target_id' => $asset->id,
            'target_type' => Asset::class,
            'item_id' => $accessory->id,
            'item_type' => Accessory::class,
            'quantity' => 3,
            'note' => 'oh hi there',
        ]);
        $this->assertHasTheseActionLogs($accessory, ['create', 'checkout']);
    }

    public function test_user_sent_notification_upon_checkout()
    {
        Mail::fake();

        $accessory = Accessory::factory()->requiringAcceptance()->create(['qty' => 5]);
        $user = User::factory()->create();

        $this->actingAs(User::factory()->checkoutAccessories()->create())
            ->from(route('accessories.checkout.show', $accessory))
            ->post(route('accessories.checkout.store', $accessory), [
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

        $this->actingAs($actor)
            ->from(route('accessories.checkout.show', $accessory))
            ->post(route('accessories.checkout.store', $accessory), [
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

    public function test_accessory_checkout_page_post_is_redirected_if_redirect_selection_is_index()
    {
        $accessory = Accessory::factory()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->from(route('accessories.index'))
            ->post(route('accessories.checkout.store', $accessory), [
                'assigned_user' => User::factory()->create()->id,
                'checkout_to_type' => 'user',
                'redirect_option' => 'index',
                'assigned_qty' => 1,
            ])
            ->assertStatus(302)
            ->assertRedirect(route('accessories.index'));
    }

    public function test_accessory_checkout_page_post_is_redirected_if_redirect_selection_is_item()
    {
        $accessory = Accessory::factory()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->from(route('accessories.index'))
            ->post(route('accessories.checkout.store', $accessory), [
                'assigned_user' => User::factory()->create()->id,
                'checkout_to_type' => 'user',
                'redirect_option' => 'item',
                'assigned_qty' => 1,
            ])
            ->assertStatus(302)
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('accessories.show', $accessory));
    }

    public function test_accessory_checkout_page_post_is_redirected_if_redirect_selection_is_target()
    {
        $user = User::factory()->create();
        $accessory = Accessory::factory()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->from(route('accessories.index'))
            ->post(route('accessories.checkout.store', $accessory), [
                'assigned_user' => $user->id,
                'checkout_to_type' => 'user',
                'redirect_option' => 'target',
                'assigned_qty' => 1,
            ])
            ->assertStatus(302)
            ->assertRedirect(route('users.show', $user));
    }

    public function test_accessory_checkout_page_post_redirects_to_signature_page_when_sign_in_place_is_checked()
    {
        $targetUser = User::factory()->create();
        $accessory = Accessory::factory()->requiringAcceptance()->create(['qty' => 5]);

        $response = $this->actingAs(User::factory()->admin()->create())
            ->from(route('accessories.checkout.show', $accessory))
            ->post(route('accessories.checkout.store', $accessory), [
                'assigned_user' => $targetUser->id,
                'checkout_to_type' => 'user',
                'redirect_option' => 'index',
                'checkout_qty' => 2,
                'sign_in_place' => 1,
            ]);

        $acceptance = CheckoutAcceptance::query()
            ->where('checkoutable_type', Accessory::class)
            ->where('checkoutable_id', $accessory->id)
            ->where('assigned_to_id', $targetUser->id)
            ->pending()
            ->latest()
            ->first();

        $this->assertNotNull($acceptance);
        $this->assertEquals(2, $acceptance->qty);

        $response->assertStatus(302)
            ->assertRedirect(route('account.accept.item', $acceptance));
    }

    public function test_accessory_checkout_stores_sign_in_place_preference_in_session()
    {
        $targetUser = User::factory()->create();
        $accessory = Accessory::factory()->create();

        $response = $this->actingAs(User::factory()->admin()->create())
            ->post(route('accessories.checkout.store', $accessory), [
                'assigned_user' => $targetUser->id,
                'checkout_to_type' => 'user',
                'redirect_option' => 'index',
                'sign_in_place' => 1,
            ]);

        $response->assertSessionHas('sign_in_place', true);
    }
}
