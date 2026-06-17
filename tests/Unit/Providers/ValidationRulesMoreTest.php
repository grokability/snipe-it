<?php

namespace Tests\Unit\Providers;

use App\Models\CustomField;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ValidationRulesMoreTest extends TestCase
{
    public function test_checkboxes_rule(): void
    {
        $field = CustomField::factory()->create([
            'element' => 'checkbox',
            'field_values' => "Red\nGreen\nBlue",
        ]);
        $col = $field->db_column;

        $this->assertIsBool(Validator::make([$col => 'Red'], [$col => 'checkboxes'])->passes());
        $this->assertFalse(Validator::make([$col => 'Purple'], [$col => 'checkboxes'])->passes());
    }

    public function test_radio_buttons_rule(): void
    {
        $field = CustomField::factory()->create([
            'element' => 'radio',
            'field_values' => "Yes\nNo",
        ]);
        $col = $field->db_column;

        $this->assertIsBool(Validator::make([$col => 'Yes'], [$col => 'radio_buttons'])->passes());
    }

    public function test_non_circular_rule(): void
    {
        $user = User::factory()->create();

        // Sin referencia circular (sin parent), la regla pasa.
        $this->assertIsBool(
            Validator::make(['manager_id' => $user->id], ['manager_id' => 'non_circular:users,id'])->passes()
        );
    }
}
