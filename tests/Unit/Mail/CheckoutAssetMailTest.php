<?php

namespace Tests\Unit\Mail;

use App\Mail\CheckoutAssetMail;
use App\Models\Asset;
use App\Models\CheckoutAcceptance;
use App\Models\User;
use PHPUnit\Framework\Attributes\DataProvider;
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
                    'expected_opening' => 'A new item has been checked out under your name that requires acceptance, details are below.'
                ];
            }
        ];

        yield 'Asset not requiring acceptance' => [
            function () {
                $asset = Asset::factory()->doesNotRequireAcceptance()->create();
                return [
                    'asset' => $asset,
                    'acceptance' => null,
                    'first_time_sending' => true,
                    'expected_subject' => trans('mail.Asset_Checkout_Notification', ['tag' => $asset->asset_tag]),
                    'expected_opening' => 'A new item has been checked out under your name, details are below.'
                ];
            }
        ];

        yield 'Reminder' => [
            function () {
                return [
                    'asset' => Asset::factory()->requiresAcceptance()->create(),
                    'acceptance' => CheckoutAcceptance::factory()->create(),
                    'first_time_sending' => false,
                    'expected_subject' => 'Reminder: You have Unaccepted Assets.',
                    'expected_opening' => 'An item was recently checked out under your name that requires acceptance, details are below.'
                ];
            }
        ];
    }

    #[DataProvider('data')]
    public function testSubjectLineAndOpening($data)
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
}
