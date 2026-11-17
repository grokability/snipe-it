<?php

namespace Tests\Feature\Livewire;

use App\Livewire\CloneFieldset;
use App\Models\CustomField;
use App\Models\CustomFieldset;
use App\Models\User;
use Livewire\Livewire;
use Tests\TestCase;

class CloneFieldsetTest extends TestCase
{
    public function test_the_component_can_render()
    {
        $fieldset = CustomFieldset::factory()->create();

        Livewire::actingAs(User::factory()->viewCustomFields()->createCustomFields()->create())
            ->test(CloneFieldset::class, ['id' => $fieldset->id])
            ->assertStatus(200);
    }

    public function test_clones_fieldset_with_field_order_and_required_flags_and_redirects_to_it()
    {
        $original = CustomFieldset::factory()->create();
        [$first, $second] = CustomField::factory()->count(2)->create();
        $original->fields()->attach($first, ['order' => 1, 'required' => true]);
        $original->fields()->attach($second, ['order' => 2, 'required' => false]);

        $user = User::factory()->viewCustomFields()->createCustomFields()->create();

        $component = Livewire::actingAs($user)
            ->test(CloneFieldset::class, ['id' => $original->id])
            ->set('name', 'Cloned Fieldset')
            ->call('submit');

        $clone = CustomFieldset::where('name', 'Cloned Fieldset')->sole();

        $component->assertRedirectToRoute('fieldsets.show', [$clone->id]);
        $this->assertEquals($user->id, $clone->created_by);

        $this->assertDatabaseHas('custom_field_custom_fieldset', [
            'custom_fieldset_id' => $clone->id,
            'custom_field_id' => $first->id,
            'order' => 1,
            'required' => 1,
        ]);
        $this->assertDatabaseHas('custom_field_custom_fieldset', [
            'custom_fieldset_id' => $clone->id,
            'custom_field_id' => $second->id,
            'order' => 2,
            'required' => 0,
        ]);
    }

    public function test_forbids_user_without_customfields_create_permission()
    {
        $original = CustomFieldset::factory()->create();

        Livewire::actingAs(User::factory()->viewCustomFields()->create())
            ->test(CloneFieldset::class, ['id' => $original->id])
            ->set('name', 'Cloned Fieldset')
            ->call('submit')
            ->assertStatus(403);

        $this->assertDatabaseMissing('custom_fieldsets', ['name' => 'Cloned Fieldset']);
    }

    public function test_rejects_blank_name()
    {
        $original = CustomFieldset::factory()->create();
        $fieldsetCount = CustomFieldset::count();

        Livewire::actingAs(User::factory()->viewCustomFields()->createCustomFields()->create())
            ->test(CloneFieldset::class, ['id' => $original->id])
            ->set('name', '')
            ->call('submit')
            ->assertHasErrors(['name' => 'required']);

        $this->assertDatabaseCount('custom_fieldsets', $fieldsetCount);
    }

    public function test_rejects_name_already_used_by_another_fieldset()
    {
        $original = CustomFieldset::factory()->create();
        $fieldsetCount = CustomFieldset::count();

        Livewire::actingAs(User::factory()->viewCustomFields()->createCustomFields()->create())
            ->test(CloneFieldset::class, ['id' => $original->id])
            ->set('name', $original->name)
            ->call('submit')
            ->assertHasErrors(['name' => 'unique']);

        $this->assertDatabaseCount('custom_fieldsets', $fieldsetCount);
    }
}
