<?php

namespace Tests\Unit\Mail;

use App\Mail\CheckoutAccessoryMail;
use App\Models\Accessory;
use App\Models\Asset;
use App\Models\Location;
use App\Models\User;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CheckoutAccessoryMailTest extends TestCase
{
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
                $leia = User::factory()->create();

                return [
                    'target' => Location::factory()->create(['name' => 'Echo Base', 'manager_id' => $leia->id]),
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

        $lightsaber = Accessory::factory()
            ->forLocation(['name' => 'Jedi Temple'])
            ->create();

        $mail = new CheckoutAccessoryMail($lightsaber, $target, User::factory()->create(), null, null);

        $mail->assertSeeInOrderInText([trans('general.location'), $expectedLocationName]);
        $mail->assertDontSeeInText('Jedi Temple');
    }

    #[Test]
    public function does_not_show_location_when_user_has_no_location()
    {
        $han = User::factory()->create(['location_id' => null]);
        $lightsaber = Accessory::factory()
            ->forLocation(['name' => 'Jedi Temple'])
            ->create();

        $mail = new CheckoutAccessoryMail($lightsaber, $han, User::factory()->create(), null, null);

        $mail->assertDontSeeInText(trans('general.location'));
    }
}
