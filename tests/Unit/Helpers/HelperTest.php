<?php

namespace Tests\Unit\Helpers;

use App\Helpers\Helper;
use App\Models\Location;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class HelperTest extends TestCase
{
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

    public function test_same_origin_url_returns_null_for_empty_input()
    {
        $this->assertNull(Helper::sameOriginUrl(null));
        $this->assertNull(Helper::sameOriginUrl(''));
    }

    public function test_same_origin_url_returns_relative_url_unchanged()
    {
        $this->assertSame('/maintenances?completed=true', Helper::sameOriginUrl('/maintenances?completed=true'));
        $this->assertSame('home', Helper::sameOriginUrl('home'));
    }

    public function test_same_origin_url_accepts_url_pointing_at_app_host()
    {
        $url = config('app.url').'/hardware/42';
        $this->assertSame($url, Helper::sameOriginUrl($url));
    }

    public function test_same_origin_url_rejects_offsite_host()
    {
        $this->assertNull(Helper::sameOriginUrl('https://evil.example.com/steal-session'));
    }

    public function test_same_origin_url_rejects_dangerous_schemes()
    {
        $this->assertNull(Helper::sameOriginUrl('javascript:alert(1)'));
        $this->assertNull(Helper::sameOriginUrl('data:text/html,<script>alert(1)</script>'));
        $this->assertNull(Helper::sameOriginUrl('file:///etc/passwd'));
    }

    public function test_same_origin_url_strips_crlf_to_prevent_header_injection()
    {
        // A CR/LF in a Location: header would let an attacker split the
        // HTTP response and inject arbitrary headers/body. The helper
        // must strip them before returning.
        $this->assertSame('/foo', Helper::sameOriginUrl("/foo\r\n"));
        $this->assertSame('/foo/bar', Helper::sameOriginUrl("/foo\r\n/bar"));
    }

    public function test_same_origin_url_rejects_scheme_relative_offsite_url()
    {
        // //evil.com/... is a scheme-relative URL that inherits the
        // current scheme and points at evil.com. Must be rejected.
        $this->assertNull(Helper::sameOriginUrl('//evil.example.com/steal-session'));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('schemeOnlyBypassProvider')]
    public function test_same_origin_url_rejects_scheme_without_authority(string $input): void
    {
        $this->assertNull(Helper::sameOriginUrl($input));
    }

    public static function schemeOnlyBypassProvider(): array
    {
        return [
            'https scheme only' => ['https:evil.example.com'],
            'http scheme only' => ['http:evil.example.com'],
            'https with path suffix' => ['https:evil.example.com/path?q=1'],
            'https with port and path' => ['https:evil.example.com:8080/admin'],
            'http userinfo without authority' => ['http:@evil.example.com'],
            'https single slash prefix' => ['https:/evil.example.com'],
            'bare scheme with colon' => ['http:'],
            'ipv4 loopback via scheme-only' => ['http:127.0.0.1:9931/bg'],
        ];
    }

    public function test_same_origin_url_rejects_backslash_userinfo_parser_differential(): void
    {
        config(['app.url' => 'https://app.example.com']);

        $this->assertNull(Helper::sameOriginUrl('https://evil.example.com\\@app.example.com/'));
        $this->assertNull(Helper::sameOriginUrl('https://evil.example.com\\\\@app.example.com/'));
    }

    public function test_same_origin_url_rejects_userinfo_in_authority(): void
    {
        // Same-origin redirects never legitimately carry credentials. Any
        // input where parse_url extracted userinfo is rejected outright,
        // closing further parser-differential variants that hide the real
        // authority behind an `@`.
        config(['app.url' => 'https://app.example.com']);

        $this->assertNull(Helper::sameOriginUrl('https://user@app.example.com/'));
        $this->assertNull(Helper::sameOriginUrl('https://user:pass@app.example.com/'));
        $this->assertNull(Helper::sameOriginUrl('https://user:pass@evil.example.com/'));
    }

    public function test_same_host_as_configured_idp_url_returns_null_for_empty_input(): void
    {
        $this->assertNull(Helper::sameHostAsConfiguredIdpUrl(null, 'https://keycloak.example.net/sls'));
        $this->assertNull(Helper::sameHostAsConfiguredIdpUrl('', 'https://keycloak.example.net/sls'));
        $this->assertNull(Helper::sameHostAsConfiguredIdpUrl('https://keycloak.example.net/sls', null));
        $this->assertNull(Helper::sameHostAsConfiguredIdpUrl('https://keycloak.example.net/sls', ''));
    }

    public function test_same_host_as_configured_idp_url_accepts_idp_target_off_origin()
    {
        // Issue #20023 regression: when Keycloak sends an IdP-initiated
        // LogoutRequest, SamlController::sls must be able to carry the
        // SP-configured IdP SLO response URL back to LoginController
        // even though it is off-origin. This is the gate that lets that
        // happen.
        config(['app.url' => 'https://snipe-it.example.com']);
        $expected = 'https://keycloak.example.net/realms/snipe/protocol/saml';
        $target = $expected.'?SAMLResponse=abc&RelayState=https%3A%2F%2Fapp.example.com%2Fdashboard';

        $this->assertSame($target, Helper::sameHostAsConfiguredIdpUrl($target, $expected));
    }

    public function test_same_host_as_configured_idp_url_rejects_host_mismatch()
    {
        // A toolkit bug or crafted input that pointed the Location at a
        // third-party host (not the SP-configured IdP) must be dropped.
        $this->assertNull(
            Helper::sameHostAsConfiguredIdpUrl(
                'https://attacker.example.net/phish',
                'https://keycloak.example.net/sls'
            )
        );
    }

    public function test_same_host_as_configured_idp_url_rejects_dangerous_schemes()
    {
        $expected = 'https://keycloak.example.net/sls';
        $this->assertNull(Helper::sameHostAsConfiguredIdpUrl('javascript:alert(1)', $expected));
        $this->assertNull(Helper::sameHostAsConfiguredIdpUrl('data:text/html,<script>alert(1)</script>', $expected));
        $this->assertNull(Helper::sameHostAsConfiguredIdpUrl('file:///etc/passwd', $expected));
    }

    public function test_same_host_as_configured_idp_url_rejects_userinfo_authority()
    {
        // Credentials in the authority can hide the real host behind an
        // `@`, which is a parser-differential bypass of the host-match
        // check. Reject outright, matching sameOriginUrl's treatment.
        $expected = 'https://keycloak.example.net/sls';
        $this->assertNull(Helper::sameHostAsConfiguredIdpUrl('https://user@attacker.example.net@keycloak.example.net/', $expected));
        $this->assertNull(Helper::sameHostAsConfiguredIdpUrl('https://user:pass@keycloak.example.net/sls', $expected));
    }

    public function test_same_host_as_configured_idp_url_strips_crlf()
    {
        $expected = 'https://keycloak.example.net/sls';
        $target = "https://keycloak.example.net/sls?SAMLResponse=abc\r\nSet-Cookie: attacker=1";

        $sanitized = Helper::sameHostAsConfiguredIdpUrl($target, $expected);

        $this->assertNotNull($sanitized);
        $this->assertStringNotContainsString("\r", $sanitized);
        $this->assertStringNotContainsString("\n", $sanitized);
    }

    public function test_same_host_as_configured_idp_url_host_match_is_case_insensitive()
    {
        $this->assertSame(
            'https://Keycloak.Example.Net/sls?SAMLResponse=abc',
            Helper::sameHostAsConfiguredIdpUrl('https://Keycloak.Example.Net/sls?SAMLResponse=abc', 'https://keycloak.example.net/sls')
        );
    }

    public function test_safe_intended_returns_stored_url_when_it_passes_the_origin_guard(): void
    {
        config(['app.url' => 'https://app.example.com']);
        Session::put('url.intended', 'https://app.example.com/foo?bar=1');

        $response = Helper::safeIntended('/fallback');

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('https://app.example.com/foo?bar=1', $response->headers->get('Location'));
    }

    public function test_safe_intended_falls_back_to_default_when_stored_url_is_offsite(): void
    {
        // A writer that skipped Helper::sameOriginUrl (or a future
        // parser-differential bypass at the write) leaves a poisoned
        // url.intended in session. The read-side guard must fall back to
        // the caller-supplied default rather than emit the poisoned URL.
        config(['app.url' => 'https://app.example.com']);
        Session::put('url.intended', 'https://evil.example.com/steal-session');

        $response = Helper::safeIntended('/fallback');

        $this->assertStringEndsWith('/fallback', $response->headers->get('Location'));
    }

    public function test_safe_intended_falls_back_to_default_when_stored_url_uses_backslash_bypass(): void
    {
        // GHSA-579m-9gf4-28jp regression: even if a writer accepted the
        // backslash-userinfo payload against an older Helper::sameOriginUrl,
        // the emission-side guard must reject it.
        config(['app.url' => 'https://app.example.com']);
        Session::put('url.intended', 'https://evil.example.com\\@app.example.com/');

        $response = Helper::safeIntended('/fallback');

        $this->assertStringEndsWith('/fallback', $response->headers->get('Location'));
    }

    public function test_safe_intended_falls_back_to_default_when_session_key_is_absent(): void
    {
        Session::forget('url.intended');

        $response = Helper::safeIntended('/fallback');

        $this->assertStringEndsWith('/fallback', $response->headers->get('Location'));
    }

    public function test_safe_intended_pulls_the_session_key(): void
    {
        // pull() semantics: after the redirect is built, url.intended must
        // be gone so subsequent requests don't re-consume the same target.
        config(['app.url' => 'https://app.example.com']);
        Session::put('url.intended', 'https://app.example.com/foo');

        Helper::safeIntended('/fallback');

        $this->assertFalse(Session::has('url.intended'));
    }

    /**
     * FD-56673 regression coverage: customFieldFormValue collapses the
     * gate + decrypt + default fallback that every branch of
     * custom_fields_form.blade.php used to hand-roll (and got wrong on
     * seven of them). These unit tests pin the four possible states of
     * the helper without needing to boot a full HTTP request.
     */
    public function test_custom_field_form_value_masks_encrypted_field_when_gate_denies(): void
    {
        \App\Models\CustomField::factory()->testEncrypted()->create();
        $field = \App\Models\CustomField::where('name', 'Test Encrypted')->first();

        // Set the column value via magic-setter rather than mass assignment
        // because CustomField columns are added at runtime and are not in
        // Asset's $fillable, so `new Asset([...])` would silently drop them.
        $asset = new \App\Models\Asset;
        $asset->{$field->db_column} = 'very-secret-value';

        $model = \App\Models\AssetModel::factory()->make();

        $this->actingAs(\App\Models\User::factory()->editAssets()->create());

        $this->assertSame(
            strtoupper(trans('admin/custom_fields/general.encrypted')),
            Helper::customFieldFormValue($field, $asset)
        );
    }

    public function test_custom_field_form_value_returns_decrypted_value_when_gate_allows(): void
    {
        \App\Models\CustomField::factory()->testEncrypted()->create();
        $field = \App\Models\CustomField::where('name', 'Test Encrypted')->first();

        $asset = new \App\Models\Asset;
        $asset->{$field->db_column} = 'very-secret-value';

        $model = \App\Models\AssetModel::factory()->make();

        $this->actingAs(\App\Models\User::factory()->superuser()->create());

        $this->assertSame(
            'very-secret-value',
            Helper::customFieldFormValue($field, $asset)
        );
    }

    public function test_custom_field_form_value_returns_raw_value_for_non_encrypted_field(): void
    {
        // Non-encrypted fields short-circuit before the gate check, so the caller
        // permission is irrelevant. Verified with an unauthenticated actor to make
        // the "gate does not gate this" behavior explicit.
        \App\Models\CustomField::factory()->ram()->create();
        $field = \App\Models\CustomField::where('name', 'RAM')->first();

        $asset = new \App\Models\Asset;
        $asset->{$field->db_column} = '16';

        $model = \App\Models\AssetModel::factory()->make();

        $this->assertSame('16', Helper::customFieldFormValue($field, $asset));
    }

    public function test_custom_field_form_value_returns_default_when_item_is_null(): void
    {
        // Create-form path: no bound $item yet, so the helper returns the
        // model-scoped default value for the field.
        \App\Models\CustomField::factory()->ram()->create();
        $field = \App\Models\CustomField::where('name', 'RAM')->first();
        $model = \App\Models\AssetModel::factory()->hasMultipleCustomFields([$field])->create();
        $model->defaultValues()->attach($field, ['default_value' => '16']);
        $defaults = \App\Models\CustomFieldDefault::valuesFor($model);

        $this->assertSame(
            '16',
            Helper::customFieldFormValue($field, null, $defaults[$field->db_column] ?? null)
        );
    }

    public function test_custom_field_form_value_masks_default_when_encrypted_field_has_no_item_and_gate_denies(): void
    {
        // Create-form path AND encrypted field AND caller can't view keys.
        // The gate check runs first regardless of whether $item is present,
        // so the mask should win over the default-value fallback.
        \App\Models\CustomField::factory()->testEncrypted()->create();
        $field = \App\Models\CustomField::where('name', 'Test Encrypted')->first();
        $model = \App\Models\AssetModel::factory()->hasMultipleCustomFields([$field])->create();
        $model->defaultValues()->attach($field, ['default_value' => 'default secret']);
        $defaults = \App\Models\CustomFieldDefault::valuesFor($model);

        $this->actingAs(\App\Models\User::factory()->editAssets()->create());

        $this->assertSame(
            strtoupper(trans('admin/custom_fields/general.encrypted')),
            Helper::customFieldFormValue($field, null, $defaults[$field->db_column] ?? null)
        );
    }

    /**
     * Regression coverage for historic CSV-imported values on DATE / DATETIME
     * custom fields. AssetImporter shoves raw CSV strings straight into
     * custom-field columns, so a column can legitimately hold `M/D/YYYY`
     * or similar. The view path already normalizes on read via
     * AssetsTransformer + getFormattedDateObject; this helper does the
     * matching normalization for the edit form so the datepicker widget
     * gets a value in its expected `Y-m-d` / `Y-m-d H:i:s` shape and
     * doesn't blank or mangle it on hydrate.
     */
    public function test_custom_field_form_value_normalizes_us_formatted_date_to_ymd(): void
    {
        \App\Models\CustomField::factory()->create([
            'name' => 'Warranty Start Date',
            'format' => 'DATE',
            'element' => 'date_picker',
        ]);
        $field = \App\Models\CustomField::where('name', 'Warranty Start Date')->first();
        $asset = new \App\Models\Asset;
        $asset->{$field->db_column} = '3/28/2025';
        $model = \App\Models\AssetModel::factory()->make();

        $this->assertSame('2025-03-28', Helper::customFieldFormValue($field, $asset));
    }

    public function test_custom_field_form_value_normalizes_us_formatted_datetime_to_ymd_his(): void
    {
        \App\Models\CustomField::factory()->create([
            'name' => 'Last Serviced',
            'format' => 'DATETIME',
            'element' => 'datetime_picker',
        ]);
        $field = \App\Models\CustomField::where('name', 'Last Serviced')->first();
        $asset = new \App\Models\Asset;
        $asset->{$field->db_column} = '3/28/2025 09:15:00';
        $model = \App\Models\AssetModel::factory()->make();

        $this->assertSame('2025-03-28 09:15:00', Helper::customFieldFormValue($field, $asset));
    }

    public function test_custom_field_form_value_leaves_already_normalized_date_untouched(): void
    {
        \App\Models\CustomField::factory()->create([
            'name' => 'Audited',
            'format' => 'DATE',
            'element' => 'date_picker',
        ]);
        $field = \App\Models\CustomField::where('name', 'Audited')->first();
        $asset = new \App\Models\Asset;
        $asset->{$field->db_column} = '2026-08-20';
        $model = \App\Models\AssetModel::factory()->make();

        $this->assertSame('2026-08-20', Helper::customFieldFormValue($field, $asset));
    }

    public function test_custom_field_form_value_leaves_unparseable_date_value_untouched(): void
    {
        // Free-text values that were stored in a DATE-format column
        // (`2028 1st Qtr` and similar) can't be Carbon-parsed. The
        // helper falls through with the raw string so the user sees
        // and can correct it, instead of masking it with a fake
        // "normalized" date.
        \App\Models\CustomField::factory()->create([
            'name' => 'End of Lease Date',
            'format' => 'DATE',
            'element' => 'date_picker',
        ]);
        $field = \App\Models\CustomField::where('name', 'End of Lease Date')->first();
        $asset = new \App\Models\Asset;
        $asset->{$field->db_column} = '2028 1st Qtr';
        $model = \App\Models\AssetModel::factory()->make();

        $this->assertSame('2028 1st Qtr', Helper::customFieldFormValue($field, $asset));
    }

    // =====================================================================
    // safeUrl: GHSA-65hf-5j7r-2xvv sink helper for user-supplied URLs
    // rendered as hrefs. Deny-list of code-executing schemes.
    // =====================================================================

    public function test_safe_url_passes_through_http_and_https(): void
    {
        $this->assertSame('http://example.com/x', Helper::safeUrl('http://example.com/x'));
        $this->assertSame('https://example.com/x', Helper::safeUrl('https://example.com/x'));
    }

    public function test_safe_url_passes_through_custom_app_schemes(): void
    {
        // file://, vnc://, ssh://, rdp://, smb://, obsidian://, etc.
        // delegate to an external handler and do not execute code
        // in the page origin, so they stay allowed.
        $this->assertSame('file:///etc/hosts', Helper::safeUrl('file:///etc/hosts'));
        $this->assertSame('vnc://corp.example.com', Helper::safeUrl('vnc://corp.example.com'));
        $this->assertSame('obsidian://open?vault=x', Helper::safeUrl('obsidian://open?vault=x'));
    }

    public function test_safe_url_passes_through_relative_urls(): void
    {
        $this->assertSame('/hardware/5', Helper::safeUrl('/hardware/5'));
        $this->assertSame('hardware/5', Helper::safeUrl('hardware/5'));
        $this->assertSame('#fragment', Helper::safeUrl('#fragment'));
    }

    public function test_safe_url_returns_empty_for_null_or_empty_input(): void
    {
        $this->assertSame('', Helper::safeUrl(null));
        $this->assertSame('', Helper::safeUrl(''));
    }

    public function test_safe_url_rejects_javascript_scheme(): void
    {
        $this->assertSame('', Helper::safeUrl('javascript:alert(document.domain)'));
    }

    public function test_safe_url_rejects_javascript_scheme_case_insensitive(): void
    {
        $this->assertSame('', Helper::safeUrl('JavaScript:alert(1)'));
        $this->assertSame('', Helper::safeUrl('JAVASCRIPT:alert(1)'));
    }

    public function test_safe_url_rejects_other_executable_schemes(): void
    {
        $this->assertSame('', Helper::safeUrl('vbscript:MsgBox(1)'));
        $this->assertSame('', Helper::safeUrl('livescript:foo'));
        $this->assertSame('', Helper::safeUrl('mocha:foo'));
        $this->assertSame('', Helper::safeUrl('data:text/html,<script>alert(1)</script>'));
    }

    public function test_safe_url_strips_control_characters_in_scheme_before_check(): void
    {
        // Browsers normalize these out before parsing the scheme,
        // so the helper must too or a payload like java\tscript:...
        // would sail past the prefix check.
        $this->assertSame('', Helper::safeUrl("java\tscript:alert(1)"));
        $this->assertSame('', Helper::safeUrl("java\nscript:alert(1)"));
        $this->assertSame('', Helper::safeUrl("java\rscript:alert(1)"));
        $this->assertSame('', Helper::safeUrl("javascript\0:alert(1)"));
    }

    public function test_safe_url_strips_leading_whitespace_before_scheme_check(): void
    {
        $this->assertSame('', Helper::safeUrl(' javascript:alert(1)'));
        $this->assertSame('', Helper::safeUrl("\t javascript:alert(1)"));
        $this->assertSame('', Helper::safeUrl("\n\tjavascript:alert(1)"));
    }

    // =====================================================================
    // safeColor: GHSA-8rcp-h6xj-5v7h sink helper for user-supplied
    // colors rendered inside an inline style attribute. Strict hex-only
    // allow-list so a CSS declaration cannot inject.
    // =====================================================================

    public function test_safe_color_accepts_valid_hex_shapes(): void
    {
        $this->assertSame('#abc', Helper::safeColor('#abc'));
        $this->assertSame('#abcd', Helper::safeColor('#abcd'));
        $this->assertSame('#aabbcc', Helper::safeColor('#aabbcc'));
        $this->assertSame('#aabbccdd', Helper::safeColor('#aabbccdd'));
        $this->assertSame('#FFFFFF', Helper::safeColor('#FFFFFF'));
    }

    public function test_safe_color_rejects_css_declarations(): void
    {
        // Core advisory payload: semicolon escape into additional
        // CSS declarations.
        $this->assertSame('', Helper::safeColor('red; position:fixed; top:0; left:0;'));
        $this->assertSame('', Helper::safeColor('#aaa; display:none'));
    }

    public function test_safe_color_rejects_color_keywords(): void
    {
        // Keywords are technically valid CSS, but the color-picker
        // inputs emit hex only. Stricter is safer.
        $this->assertSame('', Helper::safeColor('red'));
        $this->assertSame('', Helper::safeColor('transparent'));
        $this->assertSame('', Helper::safeColor('currentColor'));
    }

    public function test_safe_color_rejects_rgb_hsl_functions(): void
    {
        $this->assertSame('', Helper::safeColor('rgb(255, 0, 0)'));
        $this->assertSame('', Helper::safeColor('hsl(120, 100%, 50%)'));
    }

    public function test_safe_color_rejects_malformed_hex(): void
    {
        $this->assertSame('', Helper::safeColor('#'));
        $this->assertSame('', Helper::safeColor('#x'));
        $this->assertSame('', Helper::safeColor('#aabbccdde'));
        $this->assertSame('', Helper::safeColor('aabbcc'));
        $this->assertSame('', Helper::safeColor('#aa bb'));
    }

    public function test_safe_color_returns_empty_for_null_or_empty_input(): void
    {
        $this->assertSame('', Helper::safeColor(null));
        $this->assertSame('', Helper::safeColor(''));
    }

    public function test_paginate_builder_or_all_uses_default_per_page_without_all_flag(): void
    {
        // Baseline: a plain selectlist call (no ?all=1) must behave as
        // if the controller had called ->paginate(50) directly, so the
        // helper is a safe drop-in for every endpoint it's wired into.
        Location::factory()->count(5)->create();

        $paginator = Helper::paginateBuilderOrAll(Location::query());

        $this->assertSame(50, $paginator->perPage());
        $this->assertSame(5, $paginator->total());
    }

    public function test_paginate_builder_or_all_expands_to_cap_when_all_flag_set(): void
    {
        // With ?all=1 the helper must return every row in one page so
        // the select2-checkboxes Select All button doesn't need to walk
        // paginated pages (which is the whole gap #20106 reported).
        Location::factory()->count(75)->create();

        $this->app['request']->merge(['all' => 1]);

        $paginator = Helper::paginateBuilderOrAll(Location::query());

        $this->assertSame(75, $paginator->total());
        $this->assertCount(75, $paginator->items(), 'All 75 rows must land on page 1 under ?all=1.');
    }

    public function test_paginate_builder_or_all_honors_client_cap_as_lowering_only_ceiling(): void
    {
        // A client ?cap=N is a suggestion that can only LOWER the
        // effective cap, never raise it above the server knob. This
        // test pins the lowering-only direction.
        Location::factory()->count(30)->create();

        $this->app['request']->merge(['all' => 1, 'cap' => 10]);

        $paginator = Helper::paginateBuilderOrAll(Location::query());

        $this->assertCount(10, $paginator->items(), 'Client ?cap=10 must lower the returned set to 10 rows.');
    }

    public function test_paginate_builder_or_all_rejects_cap_higher_than_server_ceiling(): void
    {
        // Malicious ?cap=99999999 must not blow past the server-side
        // MAX_UNPAGINATED config, so cap the cap at the server value.
        config(['app.max_unpaginated_records' => 25]);
        Location::factory()->count(40)->create();

        $this->app['request']->merge(['all' => 1, 'cap' => 99999999]);

        $paginator = Helper::paginateBuilderOrAll(Location::query());

        $this->assertCount(25, $paginator->items(), 'Client cap must be clamped to the server-side ceiling.');
    }
}
