<?php

use Illuminate\Filesystem\Filesystem;
use Laravel\Passport\Passport;

beforeEach(function () {
    $originalDirectory = dirname(Passport::keyPath('oauth-public.key'));
    $this->keyDirectory = $this->app->storagePath('framework/testing/passport-regression-'.bin2hex(random_bytes(8)));

    $this->beforeApplicationDestroyed(function () use ($originalDirectory): void {
        Passport::loadKeysFrom($originalDirectory);
        (new Filesystem)->deleteDirectory($this->keyDirectory);
    });
});

test('application uses separate test keys', function () {
    expect(Passport::keyPath('oauth-public.key'))->toBe($this->app->storagePath('framework/testing/passport/oauth-public.key'));
    expect(config('passport.public_key'))->toBeNull();
    expect(config('passport.private_key'))->toBeNull();
    assertValidKeyPair();
});

test('missing test key directory is created and configured keys are overridden', function () {
    config([
        'passport.private_key' => 'development-private-key',
        'passport.public_key' => 'development-public-key',
    ]);

    $this->assertDirectoryDoesNotExist($this->keyDirectory);

    $this->preparePassportKeys($this->app, $this->keyDirectory);

    expect(config('passport.public_key'))->toBeNull();
    expect(config('passport.private_key'))->toBeNull();
    assertValidKeyPair();
});

test('existing test keys are preserved', function () {
    $this->preparePassportKeys($this->app, $this->keyDirectory);
    $privateHash = hash_file('sha256', Passport::keyPath('oauth-private.key'));
    $publicHash = hash_file('sha256', Passport::keyPath('oauth-public.key'));

    $this->preparePassportKeys($this->app, $this->keyDirectory);

    expect(hash_file('sha256', Passport::keyPath('oauth-private.key')))->toBe($privateHash);
    expect(hash_file('sha256', Passport::keyPath('oauth-public.key')))->toBe($publicHash);
});

test('pair is regenerated when either test key is deleted', function () {
    $this->preparePassportKeys($this->app, $this->keyDirectory);

    foreach (['public', 'private'] as $type) {
        $privateHash = hash_file('sha256', Passport::keyPath('oauth-private.key'));
        unlink(Passport::keyPath('oauth-'.$type.'.key'));

        $this->preparePassportKeys($this->app, $this->keyDirectory);

        $this->assertNotSame($privateHash, hash_file('sha256', Passport::keyPath('oauth-private.key')));
        assertValidKeyPair();
    }
});

function assertValidKeyPair(): void
{
    $privateKey = file_get_contents(Passport::keyPath('oauth-private.key'));
    $publicKey = file_get_contents(Passport::keyPath('oauth-public.key'));
    $signature = '';

    expect(openssl_sign('test Passport keys', $signature, $privateKey, OPENSSL_ALGO_SHA256))->toBeTrue();
    expect(openssl_verify('test Passport keys', $signature, $publicKey, OPENSSL_ALGO_SHA256))->toBe(1);
}
