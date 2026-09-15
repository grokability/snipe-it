<?php

namespace Tests\Unit\Rules;

use App\Rules\AssetTagPatternRule;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;


class AssetTagPatternRuleTest extends TestCase
{
    public function test_pattern_with_external_id_placeholder_passes()
    {
        $this->assertTrue($this->validate('SNIPEYHEAD-{external_id}'));
    }

    public function test_pattern_with_serial_placeholder_passes()
    {
        $this->assertTrue($this->validate('{serial}'));
    }

    public function test_pattern_with_hostname_placeholder_passes()
    {
        $this->assertTrue($this->validate('inv-{hostname}-2026'));
    }

    public function test_pattern_with_model_placeholder_passes()
    {
        $this->assertTrue($this->validate('{model}-{external_id}'));
    }

    public function test_pattern_with_source_placeholder_passes()
    {
        $this->assertTrue($this->validate('{source}-{external_id}'));
    }

    public function test_empty_pattern_passes()
    {
        $this->assertTrue($this->validate(''));
    }

    public function test_null_pattern_passes()
    {
        $this->assertTrue($this->validate(null));
    }

    public function test_literal_prefix_without_placeholder_fails()
    {
        // The exact case that bit real users: a "prefix + dash"
        // pattern with no placeholder. Every record renders to
        // "SNIPEYHEAD-" and only the first saves.
        $this->assertFalse($this->validate('SNIPEYHEAD-'));
    }

    public function test_unrelated_curly_braces_do_not_count_as_placeholders()
    {
        // Regression guard: a naive is-there-a-brace check would let
        // "DEMO-{unknown}" through, even though {unknown} isn't in
        // the substitution list and renders literally.
        $this->assertFalse($this->validate('DEMO-{unknown}'));
    }

    public function test_pattern_with_placeholder_and_trailing_characters_passes()
    {
        $this->assertTrue($this->validate('{external_id}-v2'));
    }

    private function validate(?string $value): bool
    {
        return Validator::make(
            ['pattern' => $value],
            ['pattern' => ['nullable', new AssetTagPatternRule]],
        )->passes();
    }
}
