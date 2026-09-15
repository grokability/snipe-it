<?php

namespace Tests\Unit\Rules;

use App\Rules\CustomHttpExtrasJsonRule;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;


class CustomHttpExtrasJsonRuleTest extends TestCase
{
    public function test_empty_string_passes()
    {
        $this->assertTrue($this->validate(''));
    }

    public function test_null_passes()
    {
        $this->assertTrue($this->validate(null));
    }

    public function test_whitespace_only_passes()
    {
        $this->assertTrue($this->validate("   \n  "));
    }

    public function test_valid_json_array_of_complete_entries_passes()
    {
        $this->assertTrue($this->validate(json_encode([
            ['key' => 'vendor_tag', 'label' => 'Vendor Tag', 'path' => 'tags.asset_tag'],
            ['key' => 'vendor_room', 'label' => 'Vendor Room', 'path' => 'location.room'],
        ])));
    }

    public function test_entry_without_label_passes_since_label_is_optional()
    {
        // key and path are required, but label falls back to key at read time.
        $this->assertTrue($this->validate(json_encode([
            ['key' => 'vendor_tag', 'path' => 'tags.asset_tag'],
        ])));
    }

    public function test_malformed_json_fails()
    {
        // Exactly what bit the user: object followed by a bare number
        // with no comma. json_decode returns null with a syntax error.
        $this->assertFalse($this->validate(
            '[{"key": "vendor_field", "label": "Human Label", "path": "dot.path.to.value"} 90]'
        ));
    }

    public function test_not_an_array_fails()
    {
        $this->assertFalse($this->validate('{"key": "one"}'));
    }

    public function test_entry_not_an_object_fails()
    {
        $this->assertFalse($this->validate(json_encode(['just a string', 'another'])));
    }

    public function test_entry_missing_key_fails()
    {
        $this->assertFalse($this->validate(json_encode([
            ['label' => 'Missing Key', 'path' => 'tags.a'],
        ])));
    }

    public function test_entry_missing_path_fails()
    {
        $this->assertFalse($this->validate(json_encode([
            ['key' => 'vendor_tag', 'label' => 'Missing Path'],
        ])));
    }

    public function test_entry_with_blank_key_fails()
    {
        $this->assertFalse($this->validate(json_encode([
            ['key' => '   ', 'path' => 'tags.a'],
        ])));
    }

    private function validate(?string $value): bool
    {
        return Validator::make(
            ['blob' => $value],
            ['blob' => ['nullable', new CustomHttpExtrasJsonRule]],
        )->passes();
    }
}
