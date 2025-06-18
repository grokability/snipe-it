<?php

namespace Tests\Feature\Checkouts\Ui;

use App\Mail\CheckoutAssetMail;
use App\Models\Asset;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\ExpectationFailedException;
use Tests\TestCase;

class BulkAssetCheckoutTest extends TestCase
{
    public function testRequiresPermission()
    {
        $this->actingAs(User::factory()->create())
            ->post(route('hardware.bulkcheckout.store'), [
                'selected_assets' => [1],
                'checkout_to_type' => 'user',
                'assigned_user' => 1,
                'assigned_asset' => null,
                'checkout_at' => null,
                'expected_checkin' => null,
                'note' => null,
            ])
            ->assertForbidden();
    }

    public function testCanBulkCheckoutAssets()
    {
        Mail::fake();

        $assets = Asset::factory()->requiresAcceptance()->count(2)->create();
        $user = User::factory()->create(['email' => 'someone@example.com']);

        $checkoutAt = now()->subWeek()->format('Y-m-d');
        $expectedCheckin = now()->addWeek()->format('Y-m-d');

        $this->actingAs(User::factory()->checkoutAssets()->viewAssets()->create())
            ->followingRedirects()
            ->post(route('hardware.bulkcheckout.store'), [
                'selected_assets' => $assets->pluck('id')->toArray(),
                'checkout_to_type' => 'user',
                'assigned_user' => $user->id,
                'assigned_asset' => null,
                'checkout_at' => $checkoutAt,
                'expected_checkin' => $expectedCheckin,
                'note' => null,
            ])
            ->assertOk();

        $assets = $assets->fresh();

        $assets->each(function ($asset) use ($expectedCheckin, $checkoutAt, $user) {
            $asset->assignedTo()->is($user);
            $asset->last_checkout = $checkoutAt;
            $asset->expected_checkin = $expectedCheckin;
        });

        Mail::assertSent(CheckoutAssetMail::class, 2);
        Mail::assertSent(CheckoutAssetMail::class, function (CheckoutAssetMail $mail) {
            return $mail->hasTo('someone@example.com');
        });
    }

    public function testHandleMissingModelBeingIncluded()
    {
        Mail::fake();

        $this->actingAs(User::factory()->checkoutAssets()->create())
            ->post(route('hardware.bulkcheckout.store'), [
                'selected_assets' => [
                    Asset::factory()->requiresAcceptance()->create()->id,
                    9999999,
                ],
                'checkout_to_type' => 'user',
                'assigned_user' => User::factory()->create(['email' => 'someone@example.com'])->id,
                'assigned_asset' => null,
                'checkout_at' => null,
                'expected_checkin' => null,
                'note' => null,
            ])
            ->assertSessionHas('error', trans_choice('admin/hardware/message.multi-checkout.error', 2));

        try {
            Mail::assertNotSent(CheckoutAssetMail::class);
        } catch (ExpectationFailedException $e) {
            $this->fail('Asset checkout email was sent when the entire checkout failed.');
        }
    }
}
