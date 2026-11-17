<?php

namespace Tests\Feature\Authentication;

use Illuminate\Filesystem\Filesystem;
use Laravel\Passport\Passport;
use Tests\TestCase;

class PassportKeysTest extends TestCase
{
    protected string $keyDirectory;

    protected function setUp(): void
    {
        parent::setUp();

        $originalDirectory = dirname(Passport::keyPath('oauth-public.key'));
        $this->keyDirectory = $this->app->storagePath('framework/testing/passport-regression-'.bin2hex(random_bytes(8)));

        $this->beforeApplicationDestroyed(function () use ($originalDirectory): void {
            Passport::loadKeysFrom($originalDirectory);
            (new Filesystem)->deleteDirectory($this->keyDirectory);
        });
    }

    public function test_application_uses_separate_test_keys(): void
    {
        $this->assertSame(
            $this->app->storagePath('framework/testing/passport/oauth-public.key'),
            Passport::keyPath('oauth-public.key')
        );
        $this->assertNull(config('passport.public_key'));
        $this->assertNull(config('passport.private_key'));
        $this->assertValidKeyPair();
    }

    public function test_missing_test_key_directory_is_created_and_configured_keys_are_overridden(): void
    {
        config([
            'passport.private_key' => 'development-private-key',
            'passport.public_key' => 'development-public-key',
        ]);

        $this->assertDirectoryDoesNotExist($this->keyDirectory);

        $this->preparePassportKeys($this->app, $this->keyDirectory);

        $this->assertNull(config('passport.public_key'));
        $this->assertNull(config('passport.private_key'));
        $this->assertValidKeyPair();
    }

    public function test_existing_test_keys_are_preserved(): void
    {
        $this->preparePassportKeys($this->app, $this->keyDirectory);
        $privateHash = hash_file('sha256', Passport::keyPath('oauth-private.key'));
        $publicHash = hash_file('sha256', Passport::keyPath('oauth-public.key'));

        $this->preparePassportKeys($this->app, $this->keyDirectory);

        $this->assertSame($privateHash, hash_file('sha256', Passport::keyPath('oauth-private.key')));
        $this->assertSame($publicHash, hash_file('sha256', Passport::keyPath('oauth-public.key')));
    }

    public function test_pair_is_regenerated_when_either_test_key_is_deleted(): void
    {
        $this->preparePassportKeys($this->app, $this->keyDirectory);

        foreach (['public', 'private'] as $type) {
            $privateHash = hash_file('sha256', Passport::keyPath('oauth-private.key'));
            unlink(Passport::keyPath('oauth-'.$type.'.key'));

            $this->preparePassportKeys($this->app, $this->keyDirectory);

            $this->assertNotSame($privateHash, hash_file('sha256', Passport::keyPath('oauth-private.key')));
            $this->assertValidKeyPair();
        }
    }

    protected function assertValidKeyPair(): void
    {
        $privateKey = file_get_contents(Passport::keyPath('oauth-private.key'));
        $publicKey = file_get_contents(Passport::keyPath('oauth-public.key'));
        $signature = '';

        $this->assertTrue(openssl_sign('test Passport keys', $signature, $privateKey, OPENSSL_ALGO_SHA256));
        $this->assertSame(1, openssl_verify('test Passport keys', $signature, $publicKey, OPENSSL_ALGO_SHA256));
    }
}
