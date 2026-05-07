<?php

namespace Tests\Feature\Notifications\Email;

use App\Mail\CheckoutAccessoryMail;
use App\Mail\CheckoutAssetMail;
use App\Mail\CheckoutConsumableMail;
use App\Mail\CheckoutLicenseMail;
use App\Models\Accessory;
use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Category;
use App\Models\CheckoutAcceptance;
use App\Models\Consumable;
use App\Models\LicenseSeat;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('notifications')]
class SignInPlaceCheckoutEmailSuppressionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
    }

    public function test_asset_checkout_does_not_send_initial_acceptance_email_when_sign_in_place_is_selected(): void
    {
        $targetUser = User::factory()->create();
        $category = Category::factory()
            ->forAssets()
            ->doesNotRequireAcceptance()
            ->doesNotSendCheckinEmail()
            ->hasLocalEula()
            ->create();
        $asset = Asset::factory()
            ->for(AssetModel::factory()->for($category, 'category'), 'model')
            ->create();

        $response = $this->actingAs(User::factory()->admin()->create())
            ->post(route('hardware.checkout.store', $asset), [
                'checkout_to_type' => 'user',
                'assigned_user' => $targetUser->id,
                'redirect_option' => 'index',
                'sign_in_place' => 1,
            ]);

        $acceptance = CheckoutAcceptance::query()
            ->where('checkoutable_type', Asset::class)
            ->where('checkoutable_id', $asset->id)
            ->where('assigned_to_id', $targetUser->id)
            ->pending()
            ->latest()
            ->first();

        $this->assertNotNull($acceptance);
        $response->assertRedirect(route('account.accept.item', $acceptance));
        Mail::assertNotSent(CheckoutAssetMail::class);
    }

    public function test_consumable_checkout_does_not_send_initial_acceptance_email_when_sign_in_place_is_selected(): void
    {
        $targetUser = User::factory()->create();
        $consumable = Consumable::factory()->requiringAcceptance()->create();

        $response = $this->actingAs(User::factory()->admin()->create())
            ->post(route('consumables.checkout.store', $consumable), [
                'assigned_to' => $targetUser->id,
                'redirect_option' => 'index',
                'checkout_qty' => 2,
                'sign_in_place' => 1,
            ]);

        $acceptance = CheckoutAcceptance::query()
            ->where('checkoutable_type', Consumable::class)
            ->where('checkoutable_id', $consumable->id)
            ->where('assigned_to_id', $targetUser->id)
            ->pending()
            ->latest()
            ->first();

        $this->assertNotNull($acceptance);
        $response->assertRedirect(route('account.accept.item', $acceptance));
        Mail::assertNotSent(CheckoutConsumableMail::class);
    }

    public function test_license_checkout_does_not_send_initial_acceptance_email_when_sign_in_place_is_selected(): void
    {
        $targetUser = User::factory()->create();
        $seat = LicenseSeat::factory()->requiringAcceptance()->create();

        $response = $this->actingAs(User::factory()->admin()->create())
            ->post(route('licenses.checkout', $seat->license), [
                'assigned_to' => $targetUser->id,
                'redirect_option' => 'index',
                'sign_in_place' => 1,
            ]);

        $acceptance = CheckoutAcceptance::query()
            ->where('checkoutable_type', LicenseSeat::class)
            ->where('assigned_to_id', $targetUser->id)
            ->pending()
            ->latest()
            ->first();

        $this->assertNotNull($acceptance);
        $response->assertRedirect(route('account.accept.item', $acceptance));
        Mail::assertNotSent(CheckoutLicenseMail::class);
    }

    public function test_accessory_checkout_does_not_send_initial_acceptance_email_when_sign_in_place_is_selected(): void
    {
        $targetUser = User::factory()->create();
        $accessory = Accessory::factory()->requiringAcceptance()->create(['qty' => 5]);

        $response = $this->actingAs(User::factory()->admin()->create())
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
        $response->assertRedirect(route('account.accept.item', $acceptance));
        Mail::assertNotSent(CheckoutAccessoryMail::class);
    }
}
