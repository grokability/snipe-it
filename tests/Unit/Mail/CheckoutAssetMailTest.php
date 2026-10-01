<?php

namespace Tests\Unit\Mail;

use App\Mail\CheckoutAssetMail;
use App\Models\Asset;
use App\Models\CheckoutAcceptance;
use App\Models\Location;
use App\Models\User;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CheckoutAssetMailTest extends TestCase
{
    public static function data()
    {
        yield 'Asset requiring acceptance' => [
            function () {
                $asset = Asset::factory()->requiresAcceptance()->create();

                return [
                    'asset' => $asset,
                    'acceptance' => CheckoutAcceptance::factory()->for($asset, 'checkoutable')->create(),
                    'first_time_sending' => true,
                    'expected_subject' => trans('mail.Asset_Checkout_Notification', ['tag' => $asset->asset_tag]),
                    'expected_opening' => 'A new item has been checked out under your name that requires acceptance, details are below.',
                ];
            },
        ];

        yield 'Asset not requiring acceptance' => [
            function () {
                $asset = Asset::factory()->doesNotRequireAcceptance()->create();

                return [
                    'asset' => $asset,
                    'acceptance' => null,
                    'first_time_sending' => true,
                    'expected_subject' => trans('mail.Asset_Checkout_Notification', ['tag' => $asset->asset_tag]),
                    'expected_opening' => 'A new item has been checked out under your name, details are below.',
                ];
            },
        ];

        yield 'Reminder' => [
            function () {
                return [
                    'asset' => Asset::factory()->requiresAcceptance()->create(),
                    'acceptance' => CheckoutAcceptance::factory()->create(),
                    'first_time_sending' => false,
                    'expected_subject' => 'Reminder: You have Unaccepted Items',
                    'expected_opening' => 'An item was recently checked out under your name that requires acceptance, details are below.',
                ];
            },
        ];
    }

    #[DataProvider('data')]
    #[Test]
    public function subject_line_and_opening($data)
    {
        [
            'asset' => $asset,
            'acceptance' => $acceptance,
            'first_time_sending' => $firstTimeSending,
            'expected_subject' => $expectedSubject,
            'expected_opening' => $expectedOpening,
        ] = $data();

        (new CheckoutAssetMail(
            $asset,
            User::factory()->create(),
            User::factory()->create(),
            $acceptance,
            'A note goes here',
            $firstTimeSending,
        ))->assertHasSubject($expectedSubject)
            ->assertSeeInText($expectedOpening);
    }

    public static function checkoutTargetsWithLocation()
    {
        yield 'User' => [
            function () {
                return [
                    'target' => User::factory()
                        ->forLocation(['name' => 'Tatooine'])
                        ->create(),
                    'expected_location_name' => 'Tatooine',
                ];
            },
        ];

        yield 'Asset' => [
            function () {
                return [
                    'target' => Asset::factory()
                        ->forLocation(['name' => 'Hoth'])
                        ->create(),
                    'expected_location_name' => 'Hoth',
                ];
            },
        ];

        yield 'Location' => [
            function () {
                return [
                    'target' => Location::factory()->create(['name' => 'Echo Base']),
                    'expected_location_name' => 'Echo Base',
                ];
            },
        ];
    }

    #[DataProvider('checkoutTargetsWithLocation')]
    #[Test]
    public function shows_location_of_checkout_target($data)
    {
        ['target' => $target, 'expected_location_name' => $expectedLocationName] = $data();

        $r2d2 = Asset::factory()->create();

        $mail = new CheckoutAssetMail($r2d2, $target, User::factory()->create(), null, null);

        $mail->assertSeeInOrderInText([trans('general.location'), $expectedLocationName]);
    }

    #[Test]
    public function does_not_show_location_when_user_has_no_location()
    {
        $han = User::factory()->create(['location_id' => null]);
        $r2d2 = Asset::factory()->create();

        $mail = new CheckoutAssetMail($r2d2, $han, User::factory()->create(), null, null);

        $mail->assertDontSeeInText(trans('general.location'));
    }
}
