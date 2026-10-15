<?php

namespace Tests\Feature\CustomFields\Ui;

use App\Livewire\CloneFieldset;
use App\Models\CustomFieldset;
use App\Models\User;
use Tests\TestCase;

class IndexCustomFieldsTest extends TestCase
{
    public function test_requires_permission()
    {
        $this->actingAs(User::factory()->create())
            ->get(route('fields.index'))
            ->assertForbidden();
    }

    public function test_page_renders()
    {
        $this->actingAs(User::factory()->viewCustomFields()->create())
            ->get(route('fields.index'))
            ->assertOk();
    }

    public function test_shows_clone_component_to_user_who_can_clone()
    {
        CustomFieldset::factory()->create();

        $this->actingAs(User::factory()->viewCustomFields()->createCustomFields()->create())
            ->get(route('fields.index'))
            ->assertSeeLivewire(CloneFieldset::class);
    }

    public function test_hides_clone_component_from_user_who_cannot_clone()
    {
        CustomFieldset::factory()->create();

        $this->actingAs(User::factory()->viewCustomFields()->create())
            ->get(route('fields.index'))
            ->assertDontSeeLivewire(CloneFieldset::class);
    }
}
