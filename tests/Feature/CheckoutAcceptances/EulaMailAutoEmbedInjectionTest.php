<?php

use App\Mail\CheckoutAssetMail;
use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Category;
use App\Models\User;

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
