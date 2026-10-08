<?php

namespace Tests\Feature\CustomFieldsets\Ui;

use App\Livewire\CloneFieldset;
use App\Models\CustomFieldset;
use App\Models\User;
use Tests\TestCase;

class ShowCustomFieldsetTest extends TestCase
{
    public function test_requires_permission()
    {
        $fieldset = CustomFieldset::factory()->create();

        $this->actingAs(User::factory()->create())
            ->get(route('fieldsets.show', $fieldset))
            ->assertForbidden();
    }

    public function test_page_renders()
    {
        $fieldset = CustomFieldset::factory()->create();

        $this->actingAs(User::factory()->viewCustomFields()->create())
            ->get(route('fieldsets.show', $fieldset))
            ->assertOk();
    }

    public function test_shows_clone_button_to_user_who_can_clone()
    {
        $fieldset = CustomFieldset::factory()->create();

        $this->actingAs(User::factory()->viewCustomFields()->createCustomFields()->create())
            ->get(route('fieldsets.show', $fieldset))
            ->assertSeeLivewire(CloneFieldset::class);
    }

    public function test_hides_clone_button_from_user_who_cannot_clone()
    {
        $fieldset = CustomFieldset::factory()->create();

        $this->actingAs(User::factory()->viewCustomFields()->create())
            ->get(route('fieldsets.show', $fieldset))
            ->assertDontSeeLivewire(CloneFieldset::class);
    }
}
