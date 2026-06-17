<?php

namespace Tests\Unit\Rules;

use App\Rules\AlphaEncrypted;
use App\Rules\BooleanEncrypted;
use App\Rules\DateEncrypted;
use App\Rules\EmailEncrypted;
use App\Rules\IPEncrypted;
use App\Rules\IPv4Encrypted;
use App\Rules\IPv6Encrypted;
use App\Rules\MacEncrypted;
use App\Rules\NumericEncrypted;
use App\Rules\UrlEncrypted;
use App\Rules\ValidJson;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Crypt;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class EncryptedRulesTest extends TestCase
{
    /**
     * Ejecuta la regla y devuelve true si fallo (si se invoco el closure $fail).
     */
    private function ruleFails(ValidationRule $rule, mixed $value, string $attribute = 'field'): bool
    {
        $failed = false;
        $rule->validate($attribute, $value, function () use (&$failed) {
            $failed = true;
        });

        return $failed;
    }

    public static function encryptedRuleProvider(): array
    {
        return [
            'alpha'   => [AlphaEncrypted::class, 'abcdef', '12345'],
            'numeric' => [NumericEncrypted::class, '12345', 'abcdef'],
            'email'   => [EmailEncrypted::class, 'user@example.com', 'not-an-email'],
            'boolean' => [BooleanEncrypted::class, '1', 'xyz'],
            'date'    => [DateEncrypted::class, '2020-01-01', 'not-a-date'],
            'ip'      => [IPEncrypted::class, '192.168.1.1', 'zzz'],
            'ipv4'    => [IPv4Encrypted::class, '192.168.1.1', '::1'],
            'ipv6'    => [IPv6Encrypted::class, '::1', '192.168.1.1'],
            'mac'     => [MacEncrypted::class, '00:11:22:33:44:55', 'xx'],
            'url'     => [UrlEncrypted::class, 'https://example.com', 'not a url'],
        ];
    }

    #[DataProvider('encryptedRuleProvider')]
    public function test_encrypted_rule_passes_for_valid_value(string $ruleClass, string $valid): void
    {
        $rule = new $ruleClass;
        $this->assertFalse($this->ruleFails($rule, Crypt::encrypt($valid)));
    }

    #[DataProvider('encryptedRuleProvider')]
    public function test_encrypted_rule_fails_for_invalid_value(string $ruleClass, string $valid, string $invalid): void
    {
        $rule = new $ruleClass;
        $this->assertTrue($this->ruleFails($rule, Crypt::encrypt($invalid)));
    }

    #[DataProvider('encryptedRuleProvider')]
    public function test_encrypted_rule_fails_for_undecryptable_value(string $ruleClass): void
    {
        // Un valor no cifrado provoca DecryptException -> el rule llama a $fail.
        $rule = new $ruleClass;
        $this->assertTrue($this->ruleFails($rule, 'plain-not-encrypted'));
    }

    public function test_valid_json_rule(): void
    {
        $rule = new ValidJson;

        $this->assertFalse($this->ruleFails($rule, '{"a":1}'));
        $this->assertTrue($this->ruleFails($rule, '{not valid json'));
        $this->assertTrue($this->ruleFails($rule, 12345));
    }
}
