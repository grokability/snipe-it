<?php

namespace Tests\Feature\Accessories\Ui;

use App\Models\Accessory;
use App\Models\User;
use Tests\TestCase;

class EditAccessoryTest extends TestCase
{
    public function test_page_renders()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('accessories.edit', Accessory::factory()->create()->id))
            ->assertOk();
    }

    public function test_edit_page_ships_requestable_checkbox_and_reflects_current_value()
    {
        // Regression guard for the migration off @include('partials.forms
        // .edit.requestable') to <x-form.checkbox-row>. The requestable
        // checkbox must still emit name="requestable" and reflect the
        // stored value as checked.
        $requestable = Accessory::factory()->create(['requestable' => 1]);

        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('accessories.edit', $requestable))
            ->assertOk()
            ->assertSee('name="requestable"', false)
            ->assertSee('checked', false);
    }
}
