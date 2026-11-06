<?php

namespace Tests\Feature\CustomFields\Ui;

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
}
