<?php

namespace Tests\Feature\CustomFieldsets\Ui;

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
}
