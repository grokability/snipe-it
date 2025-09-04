<?php

namespace Tests\Unit\Helpers;

use App\Helpers\Helper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class HelperTest extends TestCase
{
    public function testDefaultChartColorsMethodHandlesHighValues()
    {
        $this->assertIsString(Helper::defaultChartColors(1000));
    }

    public function testDefaultChartColorsMethodHandlesNegativeNumbers()
    {
        $this->assertIsString(Helper::defaultChartColors(-1));
    }

    public function testParseCurrencyMethod()
    {
        $this->settings->set(['default_currency' => 'USD']);
        $this->assertSame(12.34, Helper::ParseCurrency('USD 12.34'));

        $this->settings->set(['digit_separator' => '1.234,56']);
        $this->assertSame(12.34, Helper::ParseCurrency('12,34'));
    }
    public function testGetRedirectOptionMethod()
    {
        $test_data = [
            'Option target: redirect for user assigned to ' => [
                'request' =>(object) ['assigned_user' => 22],
                'id' => 1,
                'checkout_to_type' => 'user',
                'redirect_option' => 'target',
                'table' => 'Assets',
                'route' => route('users.show', 22),
            ],
            'Option target: redirect location assigned to ' => [
                'request' =>(object) ['assigned_location' => 10],
                'id' => 2,
                'checkout_to_type' => 'location',
                'redirect_option' => 'target',
                'table' => 'Locations',
                'route' => route('locations.show', 10),
            ],
            'Option target: redirect back to asset assigned to ' => [
                'request' =>(object) ['assigned_asset' => 101],
                'id' => 3,
                'checkout_to_type' => 'asset',
                'redirect_option' => 'target',
                'table' => 'Assets',
                'route' => route('hardware.show', 101),
            ],
            'Option item: redirect back to asset ' => [
                'request' =>(object) ['assigned_asset' => null],
                'id' => 999,
                'checkout_to_type' => null,
                'redirect_option' => 'item',
                'table' => 'Assets',
                'route' => route('hardware.show', 999),
            ],
            'Option index: redirect back to asset index ' => [
                'request' =>(object) ['assigned_asset' => null],
                'id' => null,
                'checkout_to_type' => null,
                'redirect_option' => 'index',
                'table' => 'Assets',
                'route' => route('hardware.index'),
            ],

            'Option item: redirect back to user ' => [
                'request' =>(object) ['assigned_asset' => null],
                'id' => 999,
                'checkout_to_type' => null,
                'redirect_option' => 'item',
                'table' => 'Users',
                'route' => route('users.show', 999),
            ],

            'Option index: redirect back to user index ' => [
                'request' =>(object) ['assigned_asset' => null],
                'id' => null,
                'checkout_to_type' => null,
                'redirect_option' => 'index',
                'table' => 'Users',
                'route' => route('users.index'),
            ],

            'Option item: redirect back to license ' => [
                'request' =>(object) ['assigned_asset' => null],
                'id' => 999,
                'checkout_to_type' => null,
                'redirect_option' => 'item',
                'table' => 'Licenses',
                'route' => route('licenses.show', 999),
            ],

            'Option index: redirect back to license index ' => [
                'request' =>(object) ['assigned_asset' => null],
                'id' => null,
                'checkout_to_type' => null,
                'redirect_option' => 'index',
                'table' => 'Licenses',
                'route' => route('licenses.index'),
            ],

            'Option item: redirect back to accessory list ' => [
                'request' =>(object) ['assigned_asset' => null],
                'id' => 999,
                'checkout_to_type' => null,
                'redirect_option' => 'item',
                'table' => 'Accessories',
                'route' => route('accessories.show', 999),
            ],

            'Option index: redirect back to accessory index ' => [
                'request' =>(object) ['assigned_asset' => null],
                'id' => null,
                'checkout_to_type' => null,
                'redirect_option' => 'index',
                'table' => 'Accessories',
                'route' => route('accessories.index'),
            ],
            'Option item: redirect back to consumable ' => [
                'request' =>(object) ['assigned_asset' => null],
                'id' => 999,
                'checkout_to_type' => null,
                'redirect_option' => 'item',
                'table' => 'Consumables',
                'route' => route('consumables.show', 999),
            ],

            'Option index: redirect back to consumables index ' => [
                'request' =>(object) ['assigned_asset' => null],
                'id' => null,
                'checkout_to_type' => null,
                'redirect_option' => 'index',
                'table' => 'Consumables',
                'route' => route('consumables.index'),
            ],

            'Option item: redirect back to component ' => [
                'request' =>(object) ['assigned_asset' => null],
                'id' => 999,
                'checkout_to_type' => null,
                'redirect_option' => 'item',
                'table' => 'Components',
                'route' => route('components.show', 999),
            ],

            'Option index: redirect back to component index ' => [
                'request' =>(object) ['assigned_asset' => null],
                'id' => null,
                'checkout_to_type' => null,
                'redirect_option' => 'index',
                'table' => 'Components',
                'route' => route('components.index'),
            ],
        ];

        foreach ($test_data as $scenario => $data ) {

            Session::put('redirect_option', $data['redirect_option']);
            Session::put('checkout_to_type', $data['checkout_to_type']);

            $redirect = Helper::getRedirectOption($data['request'], $data['id'], $data['table']);


            $this->assertInstanceOf(RedirectResponse::class, $redirect);
            $this->assertEquals($data['route'], $redirect->getTargetUrl(), $scenario.'failed.');
        }
    }
}
