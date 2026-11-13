<?php

namespace Tests\Feature\CustomFields\Ui;

use App\Livewire\CustomFieldEditor;
use App\Models\User;
use Livewire\Livewire;
use Tests\TestCase;

class CreateCustomFieldsTest extends TestCase
{
    public function test_user_without_create_permission_cannot_reach_create_page()
    {
        // fields.create routes directly to the CustomFieldEditor Livewire
        // component; mount() checks the 'create' policy. A user with only
        // 'view' can't reach the authoring form.
        $this->actingAs(User::factory()->viewCustomFields()->create())
            ->get(route('fields.create'))
            ->assertForbidden();
    }

    public function test_user_with_create_can_reach_create_page()
    {
        // Route no longer requires view permission separately — create is
        // the authoring gate for the authoring form.
        $this->actingAs(User::factory()->createCustomFields()->create())
            ->get(route('fields.create'))
            ->assertOk();
    }

    public function test_superuser_can_reach_create_page()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('fields.create'))
            ->assertOk();
    }

    public function test_link_template_is_saved()
    {
        $this->actingAs(User::factory()->superuser()->create());

        Livewire::test(CustomFieldEditor::class)
            ->set('name', 'AnyDesk ID')
            ->set('link_template', 'anydesk:{VALUE}')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('custom_fields', [
            'name' => 'AnyDesk ID',
            'link_template' => 'anydesk:{VALUE}',
        ]);
    }

    public function test_link_template_is_not_saved_for_unsupported_element()
    {
        $this->actingAs(User::factory()->superuser()->create());

        Livewire::test(CustomFieldEditor::class)
            ->set('name', 'Notes')
            ->set('link_template', 'anydesk:{VALUE}')
            ->set('element', 'textarea')
            ->assertSet('showLinkTemplate', false)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('custom_fields', [
            'name' => 'Notes',
            'link_template' => null,
        ]);
    }

    public function test_preview_shows_link_built_from_template()
    {
        $this->actingAs(User::factory()->superuser()->create());

        Livewire::test(CustomFieldEditor::class)
            ->set('link_template', 'rustdesk://{VALUE}')
            ->assertSee('rustdesk://123456789');
    }

    public function test_preview_uses_first_field_value_for_listbox()
    {
        $this->actingAs(User::factory()->superuser()->create());

        Livewire::test(CustomFieldEditor::class)
            ->set('element', 'listbox')
            ->set('field_values', "pc-01@ad\npc-02@ad")
            ->set('link_template', 'anydesk:{VALUE}')
            ->assertSee('anydesk:pc-01@ad');
    }

    public function test_preview_flags_invalid_link_template()
    {
        $this->actingAs(User::factory()->superuser()->create());

        Livewire::test(CustomFieldEditor::class)
            ->set('link_template', 'anydesk:')
            ->assertSee(trans('admin/custom_fields/general.validation.link_template_invalid'));
    }

    public function test_invalid_link_template_shows_error()
    {
        $this->actingAs(User::factory()->superuser()->create());

        Livewire::test(CustomFieldEditor::class)
            ->set('name', 'AnyDesk ID')
            ->set('link_template', 'javascript:alert({VALUE})')
            ->call('save')
            ->assertHasErrors('link_template');

        $this->assertDatabaseMissing('custom_fields', ['name' => 'AnyDesk ID']);
    }
}
