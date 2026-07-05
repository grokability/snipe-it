<?php

namespace Tests\Feature\CustomFields\Ui;

use App\Models\User;
use Tests\TestCase;

class CreateCustomFieldsTest extends TestCase
{
    public function test_user_without_view_permission_cannot_reach_create_page()
    {
        // The menu entry already requires customfields.view; the create page
        // and store action should match so a user can't bypass by typing the
        // URL directly. Defense-in-depth tie-in with the XSS report on the
        // custom-field name column.
        $this->actingAs(User::factory()->createCustomFields()->create())
            ->get(route('fields.create'))
            ->assertForbidden();
    }

    public function test_user_without_view_permission_cannot_post_to_store()
    {
        $this->actingAs(User::factory()->createCustomFields()->create())
            ->post(route('fields.store'), [
                'name' => 'TestField',
                'element' => 'text',
                'format' => 'ANY',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('custom_fields', ['name' => 'TestField']);
    }

    public function test_user_with_view_and_create_can_reach_create_page()
    {
        $this->actingAs(User::factory()->viewCustomFields()->createCustomFields()->create())
            ->get(route('fields.create'))
            ->assertOk();
    }

    public function test_superuser_can_reach_create_page()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('fields.create'))
            ->assertOk();
    }
}
