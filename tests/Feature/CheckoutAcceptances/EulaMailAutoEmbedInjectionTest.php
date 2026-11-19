<?php

use App\Mail\CheckoutAssetMail;
use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Category;
use App\Models\User;

/**
 * Regression coverage for the arbitrary local-file read + SSRF reported by
 * W1nterFr3ak (Chris Byron Otieno) on 2026-08-02. Category eula_text was
 * passed raw through SnipeModel::getEula, echoed via `{!! $eula !!}` into
 * every checkout mail template, and the resulting HTML was walked by
 * `laravel-mail-auto-embed`, which fetched every `<img src="">`
 * server-side (file_get_contents for local paths, curl with TLS
 * verification disabled for remote URLs) and attached the bytes to the
 * outbound mail. A low-privilege user with categories.create/edit +
 * assets.checkout could set eula_text to `![x](/var/www/html/.env)` or a
 * raw `<img>` tag, check the asset out to themselves, and receive the
 * file contents (or any URL's response body, including cloud metadata) as
 * a MIME attachment.
 *
 * The fix sanitizes at the model boundary: `SnipeModel::getEula` now
 * pipes through `Helper::parseEscapedMarkedown` (strip_tags + Parsedown
 * safe mode) and additionally strips `<img>` from the Parsedown output,
 * killing both attack vectors before eula content reaches any mail
 * template. `BlockImagesMarkdownExtension` on the mail Markdown parser
 * from GHSA-f3vq-g24v-xc2g remains defense in depth.
 *
 * These tests exercise the model-layer sanitizer directly and the
 * end-to-end mailable render so both surfaces are pinned.
 */
function assetWithEula(string $eulaText): Asset
{
    $category = Category::factory()->assetLaptopCategory()->create([
        'eula_text' => $eulaText,
        'use_default_eula' => 0,
    ]);
    $model = AssetModel::factory()->create(['category_id' => $category->id]);

    return Asset::factory()->create(['model_id' => $model->id]);
}

test('get eula strips markdown syntax image pointing at local file', function () {
    $asset = assetWithEula('![logo](/var/www/html/.env)');

    $rendered = $asset->getEula();

    $this->assertStringNotContainsString('<img', (string) $rendered);
    $this->assertStringNotContainsString('/var/www/html/.env', (string) $rendered);
});

test('get eula strips raw html img pointing at local file', function () {
    $asset = assetWithEula('<img src="/var/www/html/.env" alt="logo">');

    $rendered = $asset->getEula();

    $this->assertStringNotContainsString('<img', (string) $rendered);
    $this->assertStringNotContainsString('/var/www/html/.env', (string) $rendered);
});

test('get eula strips markdown syntax image pointing at ssrf target', function () {
    $asset = assetWithEula('![x](http://169.254.169.254/latest/meta-data/iam/security-credentials/)');

    $rendered = $asset->getEula();

    $this->assertStringNotContainsString('<img', (string) $rendered);
    $this->assertStringNotContainsString('169.254.169.254', (string) $rendered);
});

test('get eula strips raw html img pointing at loopback ssrf target', function () {
    $asset = assetWithEula('<img src="http://127.0.0.1:9999/secret" alt="ssrf">');

    $rendered = $asset->getEula();

    $this->assertStringNotContainsString('<img', (string) $rendered);
    $this->assertStringNotContainsString('127.0.0.1', (string) $rendered);
});

test('get eula preserves legitimate markdown formatting', function () {
    $asset = assetWithEula("**Terms** apply.\n\n- item one\n- item two");

    $rendered = (string) $asset->getEula();

    $this->assertStringContainsString('<strong>Terms</strong>', $rendered);
    $this->assertStringContainsString('<li>item one</li>', $rendered);
});

test('checkout asset mail render omits poisoned img from eula', function () {
    $asset = assetWithEula('![logo](/var/www/html/.env)');
    $target = User::factory()->create();
    $admin = User::factory()->create();

    $mail = new CheckoutAssetMail(
        $asset,
        $target,
        $admin,
        null,
        null,
    );

    $rendered = (string) $mail->render();

    $this->assertStringNotContainsString('<img', $rendered);
    $this->assertStringNotContainsString('/var/www/html/.env', $rendered);
});

test('checkout asset mail render omits raw html img from eula', function () {
    $asset = assetWithEula('<img src="/etc/hostname" alt="logo">');
    $target = User::factory()->create();
    $admin = User::factory()->create();

    $mail = new CheckoutAssetMail(
        $asset,
        $target,
        $admin,
        null,
        null,
    );

    $rendered = (string) $mail->render();

    $this->assertStringNotContainsString('<img', $rendered);
    $this->assertStringNotContainsString('/etc/hostname', $rendered);
});
