<?php

namespace Tests\Unit\Helpers;

use App\Helpers\Helper;
use App\Models\Location;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class HelperTest extends TestCase
{
    /**
     * Regression: `<x-form.row type="datetimepicker">` on transient checkout
     * forms (hardware/checkout, bulk-checkout, kits/checkout, etc.) passes
     * `$item = null` down to `Helper::checkIfRequired`. Without the null guard
     * this hit `null::rules()` and threw "Class name must be a valid object or
     * a string", 500ing the whole page. When there's no bound model to
     * introspect, we treat the field as not required.
     */
    public function test_check_if_required_returns_false_when_class_is_null()
    {
        $this->assertFalse(Helper::checkIfRequired(null, 'name'));
    }

    public function test_check_if_required_detects_required_field_on_a_real_model()
    {
        // Location::$rules declares 'name' => 'required|max:255|unique_undeleted'
        $this->assertTrue(Helper::checkIfRequired(new Location, 'name'));
    }

    public function test_check_if_required_returns_false_for_a_non_required_field()
    {
        // Location's 'address' is 'max:191|nullable' — no required rule.
        $this->assertFalse(Helper::checkIfRequired(new Location, 'address'));
    }

    public function test_check_if_required_returns_false_for_unknown_field()
    {
        $this->assertFalse(Helper::checkIfRequired(new Location, 'no_such_field_on_location'));
    }

    public function test_default_chart_colors_method_handles_high_values()
    {
        $this->assertIsString(Helper::defaultChartColors(1000));
    }

    public function test_default_chart_colors_method_handles_negative_numbers()
    {
        $this->assertIsString(Helper::defaultChartColors(-1));
    }

    public function test_parse_currency_method()
    {
        $this->settings->set(['default_currency' => 'USD', 'digit_separator' => '1,234.56']);
        $this->assertSame(12.34, Helper::ParseCurrency('USD 12.34'));
        $this->assertSame(8888.0, Helper::ParseCurrency('8,888.00'));   // US thousands comma
        $this->assertSame(8888.0, Helper::ParseCurrency('8888.00'));    // US plain

        $this->settings->set(['digit_separator' => '1.234,56']);
        $this->assertSame(12.34, Helper::ParseCurrency('12,34'));
        $this->assertSame(8888.0, Helper::ParseCurrency('8.888,00'));   // EU thousands dot
        $this->assertSame(8888.0, Helper::ParseCurrency('8888,00'));    // EU plain
    }

    public function test_get_redirect_option_method()
    {
        $test_data = [
            'Option target: redirect for user assigned to ' => [
                'request' => (object) ['assigned_user' => 22],
                'id' => 1,
                'checkout_to_type' => 'user',
                'redirect_option' => 'target',
                'table' => 'Assets',
                'route' => route('users.show', 22),
            ],
            'Option target: redirect location assigned to ' => [
                'request' => (object) ['assigned_location' => 10],
                'id' => 2,
                'checkout_to_type' => 'location',
                'redirect_option' => 'target',
                'table' => 'Locations',
                'route' => route('locations.show', 10),
            ],
            'Option target: redirect back to asset assigned to ' => [
                'request' => (object) ['assigned_asset' => 101],
                'id' => 3,
                'checkout_to_type' => 'asset',
                'redirect_option' => 'target',
                'table' => 'Assets',
                'route' => route('hardware.show', 101),
            ],
            'Option item: redirect back to asset ' => [
                'request' => (object) ['assigned_asset' => null],
                'id' => 999,
                'checkout_to_type' => null,
                'redirect_option' => 'item',
                'table' => 'Assets',
                'route' => route('hardware.show', 999),
            ],
            'Option index: redirect back to asset index ' => [
                'request' => (object) ['assigned_asset' => null],
                'id' => null,
                'checkout_to_type' => null,
                'redirect_option' => 'index',
                'table' => 'Assets',
                'route' => route('hardware.index'),
            ],

            'Option item: redirect back to user ' => [
                'request' => (object) ['assigned_asset' => null],
                'id' => 999,
                'checkout_to_type' => null,
                'redirect_option' => 'item',
                'table' => 'Users',
                'route' => route('users.show', 999),
            ],

            'Option index: redirect back to user index ' => [
                'request' => (object) ['assigned_asset' => null],
                'id' => null,
                'checkout_to_type' => null,
                'redirect_option' => 'index',
                'table' => 'Users',
                'route' => route('users.index'),
            ],

            'Option item: redirect back to license ' => [
                'request' => (object) ['assigned_asset' => null],
                'id' => 999,
                'checkout_to_type' => null,
                'redirect_option' => 'item',
                'table' => 'Licenses',
                'route' => route('licenses.show', 999),
            ],

            'Option index: redirect back to license index ' => [
                'request' => (object) ['assigned_asset' => null],
                'id' => null,
                'checkout_to_type' => null,
                'redirect_option' => 'index',
                'table' => 'Licenses',
                'route' => route('licenses.index'),
            ],

            'Option item: redirect back to accessory list ' => [
                'request' => (object) ['assigned_asset' => null],
                'id' => 999,
                'checkout_to_type' => null,
                'redirect_option' => 'item',
                'table' => 'Accessories',
                'route' => route('accessories.show', 999),
            ],

            'Option index: redirect back to accessory index ' => [
                'request' => (object) ['assigned_asset' => null],
                'id' => null,
                'checkout_to_type' => null,
                'redirect_option' => 'index',
                'table' => 'Accessories',
                'route' => route('accessories.index'),
            ],
            'Option item: redirect back to consumable ' => [
                'request' => (object) ['assigned_asset' => null],
                'id' => 999,
                'checkout_to_type' => null,
                'redirect_option' => 'item',
                'table' => 'Consumables',
                'route' => route('consumables.show', 999),
            ],

            'Option index: redirect back to consumables index ' => [
                'request' => (object) ['assigned_asset' => null],
                'id' => null,
                'checkout_to_type' => null,
                'redirect_option' => 'index',
                'table' => 'Consumables',
                'route' => route('consumables.index'),
            ],

            'Option item: redirect back to component ' => [
                'request' => (object) ['assigned_asset' => null],
                'id' => 999,
                'checkout_to_type' => null,
                'redirect_option' => 'item',
                'table' => 'Components',
                'route' => route('components.show', 999),
            ],

            'Option index: redirect back to component index ' => [
                'request' => (object) ['assigned_asset' => null],
                'id' => null,
                'checkout_to_type' => null,
                'redirect_option' => 'index',
                'table' => 'Components',
                'route' => route('components.index'),
            ],
        ];

        foreach ($test_data as $scenario => $data) {

            Session::put('redirect_option', $data['redirect_option']);
            Session::put('checkout_to_type', $data['checkout_to_type']);

            $redirect = Helper::getRedirectOption($data['request'], $data['id'], $data['table']);

            $this->assertInstanceOf(RedirectResponse::class, $redirect);
            $this->assertEquals($data['route'], $redirect->getTargetUrl(), $scenario.'failed.');
        }
    }

    public function test_get_redirect_option_preserves_query_filters_when_returning_to_index()
    {
        // #15214: a user editing an asset from `hardware?status_type=Deployed`
        // should land back on that filtered view, not the plain
        // hardware.index that drops the side-nav filter context.
        Session::put('redirect_option', 'index');
        Session::put('url.intended', route('hardware.index').'?status_type=Deployed');

        $redirect = Helper::getRedirectOption((object) [], null, 'Assets');

        $this->assertInstanceOf(RedirectResponse::class, $redirect);
        $this->assertEquals(
            route('hardware.index').'?status_type=Deployed',
            $redirect->getTargetUrl(),
        );
    }

    public function test_get_redirect_option_preserves_multiple_query_filters()
    {
        // Query string with more than one filter round-trips cleanly.
        Session::put('redirect_option', 'index');
        Session::put('url.intended', route('hardware.index').'?status_type=RTD&category_id=3');

        $redirect = Helper::getRedirectOption((object) [], null, 'Assets');

        $this->assertEquals(
            route('hardware.index').'?status_type=RTD&category_id=3',
            $redirect->getTargetUrl(),
        );
    }

    public function test_get_redirect_option_falls_back_to_plain_index_when_no_referrer_query()
    {
        // When there's no filter to preserve, behavior matches the
        // pre-#15214 code path.
        Session::put('redirect_option', 'index');
        Session::put('url.intended', route('hardware.index'));

        $redirect = Helper::getRedirectOption((object) [], null, 'Assets');

        $this->assertEquals(route('hardware.index'), $redirect->getTargetUrl());
    }

    public function test_get_redirect_option_ignores_referrer_pointing_at_a_different_path()
    {
        // If the referrer was the show page or the create form (not the
        // index they'd be redirected to), don't smuggle it into the
        // index redirect. Only same-path referrers are preserved.
        Session::put('redirect_option', 'index');
        Session::put('url.intended', route('hardware.show', 42));

        $redirect = Helper::getRedirectOption((object) [], null, 'Assets');

        $this->assertEquals(route('hardware.index'), $redirect->getTargetUrl());
    }

    public function test_get_redirect_option_ignores_offsite_referrer_when_returning_to_index()
    {
        // Off-site protection was already in place for the 'back'
        // option (via the parse_url host check). Verify it also applies
        // on the 'index' path so an attacker-controlled url.intended
        // (see SamlController RelayState) can't smuggle an external URL
        // into an index redirect.
        Session::put('redirect_option', 'index');
        Session::put('url.intended', 'https://evil.example.com/hardware?status_type=Deployed');

        $redirect = Helper::getRedirectOption((object) [], null, 'Assets');

        $this->assertEquals(route('hardware.index'), $redirect->getTargetUrl());
    }

    public function test_get_redirect_option_preserves_filters_for_other_entity_types_too()
    {
        // The #15214 fix lives in Helper::getRedirectOption so it covers
        // every entity that routes through the redirect helper, not just
        // Assets. Users/Licenses/etc. don't have side-nav filters today
        // but might grow them, and generic ?category_id=... links are
        // already common.
        Session::put('redirect_option', 'index');
        Session::put('url.intended', route('users.index').'?company_id=5');

        $redirect = Helper::getRedirectOption((object) [], null, 'Users');

        $this->assertEquals(route('users.index').'?company_id=5', $redirect->getTargetUrl());
    }
}
