<?php

namespace Tests\Feature\SyncAdapters\AppleBusinessManager;

use App\Models\SyncAdapterInstance;
use App\SyncAdapters\AppleBusinessManager\AppleBusinessManagerAdapter;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class PrivateKeyValidationTest extends TestCase
{
    private AppleBusinessManagerAdapter $adapter;

    private string $slug;

    protected function setUp(): void
    {
        parent::setUp();
        $instance = SyncAdapterInstance::where('slug', 'abm')->firstOrFail();
        $this->adapter = new AppleBusinessManagerAdapter($instance);
        $this->slug = $instance->slug;
    }

    public function test_valid_p256_pem_passes_validation(): void
    {
        $pem = $this->makeEcPem('prime256v1');

        $this->assertPasses($pem);
    }

    public function test_blank_value_is_deferred_to_the_required_rule(): void
    {
        // Blank submission is the "keep-what's-in-DB" path for secret
        // fields. The closure returns early so blank input doesn't
        // false-positive as "invalid key".
        $errors = $this->runValidator('');

        $this->assertContains(
            'validation.required',
            array_column($errors, 'rule'),
            'Blank private_key should fail the required rule, not the shape rule.',
        );
        $this->assertNotContains(
            trans('admin/settings/sync_adapters.abm_private_key_invalid'),
            array_column($errors, 'message'),
        );
    }

    public function test_malformed_pem_fails_with_invalid_message(): void
    {
        // Mimics gh #19688 exact case: header lost one leading dash.
        $malformed = str_replace('-----BEGIN', '----BEGIN', $this->makeEcPem('prime256v1'));

        $this->assertFailsWith($malformed, 'admin/settings/sync_adapters.abm_private_key_invalid');
    }

    public function test_rsa_key_fails_with_unsupported_message(): void
    {
        $key = openssl_pkey_new(['private_key_type' => OPENSSL_KEYTYPE_RSA, 'private_key_bits' => 2048]);
        $pem = '';
        openssl_pkey_export($key, $pem);

        $this->assertFailsWith($pem, 'admin/settings/sync_adapters.abm_private_key_unsupported');
    }

    public function test_wrong_curve_ec_key_fails_with_unsupported_message(): void
    {
        // EC on the wrong curve (P-384 here) parses cleanly but is
        // the wrong shape for ES256 / Apple. Same user-facing
        // remediation as the RSA case, so same trans key.
        $pem = $this->makeEcPem('secp384r1');

        $this->assertFailsWith($pem, 'admin/settings/sync_adapters.abm_private_key_unsupported');
    }

    private function makeEcPem(string $curve): string
    {
        $key = openssl_pkey_new([
            'private_key_type' => OPENSSL_KEYTYPE_EC,
            'curve_name' => $curve,
            'private_key_bits' => 2048,
        ]);
        $pem = '';
        openssl_pkey_export($key, $pem);

        return $pem;
    }

    private function assertPasses(string $pem): void
    {
        $errors = $this->runValidator($pem);
        $this->assertSame([], $errors, 'Expected private key to pass validation.');
    }

    private function assertFailsWith(string $pem, string $transKey): void
    {
        $errors = $this->runValidator($pem);
        $messages = array_column($errors, 'message');
        $this->assertContains(
            trans($transKey),
            $messages,
            'Expected validator to surface trans key "'.$transKey.'". Got: '.implode(' | ', $messages),
        );
    }

    /**
     * Run the adapter's validationRules() against a single-field
     * payload and return every error message on the private_key
     * field, tagged with the underlying rule name when it's a
     * built-in rule.
     *
     * @return array<int, array{message: string, rule: string}>
     */
    private function runValidator(string $value): array
    {
        $rules = $this->adapter->validationRules();
        $key = $this->slug.'_private_key';

        $validator = Validator::make([$key => $value], [$key => $rules[$key]]);
        $validator->passes();

        $out = [];
        foreach ($validator->errors()->get($key) as $message) {
            $out[] = ['message' => $message, 'rule' => $this->guessRule($message)];
        }

        return $out;
    }

    private function guessRule(string $message): string
    {
        // The built-in `required` message renders as "The X field is
        // required." for the slug-namespaced field. Match on that
        // shape so the required-vs-shape check in the blank test
        // doesn't depend on the exact localized string.
        return str_contains($message, 'is required') ? 'validation.required' : 'closure';
    }
}
