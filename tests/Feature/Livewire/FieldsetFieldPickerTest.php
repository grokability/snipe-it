<?php

namespace Tests\Feature\Livewire;

use App\Livewire\FieldsetFieldPicker;
use App\Models\CustomField;
use App\Models\CustomFieldset;
use App\Models\User;
use Livewire\Livewire;
use Tests\TestCase;

class FieldsetFieldPickerTest extends TestCase
{
    /**
     * User with view + create + edit on customfields. The policies
     * require all three to reach every phase of the picker.
     */
    private function editorUser(): User
    {
        return User::factory()
            ->viewCustomFields()
            ->createCustomFields()
            ->editCustomFields()
            ->create();
    }

    // --- Security ---------------------------------------------------------

    public function test_edit_phase_rejects_user_without_customfields_edit_permission()
    {
        $fieldset = CustomFieldset::factory()->create();
        $field = CustomField::factory()->create();
        $fieldset->fields()->attach($field, ['order' => 1, 'required' => false]);

        $noEdit = User::factory()->viewCustomFields()->createCustomFields()->create();

        try {
            Livewire::actingAs($noEdit)
                ->test(FieldsetFieldPicker::class, ['fieldset' => $fieldset])
                ->call('detach', $field->id);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            // expected
        }

        $this->assertDatabaseHas('custom_field_custom_fieldset', [
            'custom_fieldset_id' => $fieldset->id,
            'custom_field_id' => $field->id,
        ]);
    }

    public function test_create_phase_mount_rejects_user_without_customfields_create_permission()
    {
        Livewire::actingAs(User::factory()->viewCustomFields()->create())
            ->test(FieldsetFieldPicker::class)
            ->assertStatus(403);
    }

    public function test_mount_allows_editor_user()
    {
        $fieldset = CustomFieldset::factory()->create();

        Livewire::actingAs($this->editorUser())
            ->test(FieldsetFieldPicker::class, ['fieldset' => $fieldset])
            ->assertStatus(200);
    }

    public function test_mutating_actions_leave_pivot_untouched_for_unauthorized_user()
    {
        $fieldset = CustomFieldset::factory()->create();
        $attachedField = CustomField::factory()->create();
        $wouldBeAttached = CustomField::factory()->create();
        $fieldset->fields()->attach($attachedField, ['order' => 1, 'required' => false]);

        $viewer = User::factory()->viewCustomFields()->create();

        // boot() throws AuthorizationException. Catch Throwable so the
        // test stays tied to the invariant that matters - the pivot
        // row wasn't written - not to which layer rejected it.
        try {
            Livewire::actingAs($viewer)
                ->test(FieldsetFieldPicker::class, ['fieldset' => $fieldset])
                ->call('attach', $wouldBeAttached->id);
        } catch (\Throwable $e) {
            // expected
        }

        $this->assertDatabaseMissing('custom_field_custom_fieldset', [
            'custom_fieldset_id' => $fieldset->id,
            'custom_field_id' => $wouldBeAttached->id,
        ]);
    }

    // --- Create phase -----------------------------------------------------

    public function test_create_fieldset_persists_row_and_flips_to_edit_phase()
    {
        $user = $this->editorUser();

        Livewire::actingAs($user)
            ->test(FieldsetFieldPicker::class)
            ->assertSet('fieldsetId', 0)
            ->set('name', 'My New Fieldset')
            ->call('createFieldset')
            ->assertSet('fieldsetId', fn ($v) => $v > 0);

        $this->assertDatabaseHas('custom_fieldsets', [
            'name' => 'My New Fieldset',
            'created_by' => $user->id,
        ]);
    }

    public function test_create_fieldset_rejects_duplicate_name()
    {
        CustomFieldset::factory()->create(['name' => 'Already There']);
        $countBefore = CustomFieldset::count();

        Livewire::actingAs($this->editorUser())
            ->test(FieldsetFieldPicker::class)
            ->set('name', 'Already There')
            ->call('createFieldset')
            ->assertHasErrors(['name' => 'unique']);

        $this->assertDatabaseCount('custom_fieldsets', $countBefore);
    }

    public function test_create_fieldset_rejects_empty_name()
    {
        $countBefore = CustomFieldset::count();

        Livewire::actingAs($this->editorUser())
            ->test(FieldsetFieldPicker::class)
            ->set('name', '')
            ->call('createFieldset')
            ->assertHasErrors(['name' => 'required']);

        $this->assertDatabaseCount('custom_fieldsets', $countBefore);
    }

    public function test_create_fieldset_syncs_auto_add_fields_with_sequential_order()
    {
        $auto1 = CustomField::factory()->create(['name' => 'Alpha', 'auto_add_to_fieldsets' => '1']);
        $auto2 = CustomField::factory()->create(['name' => 'Bravo', 'auto_add_to_fieldsets' => '1']);
        CustomField::factory()->create(['name' => 'NotAuto', 'auto_add_to_fieldsets' => '0']);

        Livewire::actingAs($this->editorUser())
            ->test(FieldsetFieldPicker::class)
            ->set('name', 'Auto-Populated')
            ->call('createFieldset');

        $fieldset = CustomFieldset::where('name', 'Auto-Populated')->sole();

        // Alphabetical name sort, Alpha=1 then Bravo=2. Required
        // defaults false.
        $this->assertDatabaseHas('custom_field_custom_fieldset', [
            'custom_fieldset_id' => $fieldset->id,
            'custom_field_id' => $auto1->id,
            'order' => 1,
            'required' => 0,
        ]);
        $this->assertDatabaseHas('custom_field_custom_fieldset', [
            'custom_fieldset_id' => $fieldset->id,
            'custom_field_id' => $auto2->id,
            'order' => 2,
            'required' => 0,
        ]);
    }

    // --- Edit phase: rename -----------------------------------------------

    public function test_updated_name_persists_rename()
    {
        $fieldset = CustomFieldset::factory()->create(['name' => 'Original']);

        Livewire::actingAs($this->editorUser())
            ->test(FieldsetFieldPicker::class, ['fieldset' => $fieldset])
            ->set('name', 'Renamed');

        $this->assertDatabaseHas('custom_fieldsets', [
            'id' => $fieldset->id,
            'name' => 'Renamed',
        ]);
    }

    public function test_rename_to_same_name_passes_via_ignore_self()
    {
        $fieldset = CustomFieldset::factory()->create(['name' => 'Keep This Name']);

        Livewire::actingAs($this->editorUser())
            ->test(FieldsetFieldPicker::class, ['fieldset' => $fieldset])
            ->set('name', 'Keep This Name')
            ->assertHasNoErrors();
    }

    public function test_rename_to_another_fieldsets_name_fails()
    {
        CustomFieldset::factory()->create(['name' => 'Taken Already']);
        $two = CustomFieldset::factory()->create(['name' => 'Free Name']);

        Livewire::actingAs($this->editorUser())
            ->test(FieldsetFieldPicker::class, ['fieldset' => $two])
            ->set('name', 'Taken Already')
            ->assertHasErrors(['name' => 'unique']);

        $this->assertDatabaseHas('custom_fieldsets', ['id' => $two->id, 'name' => 'Free Name']);
    }

    // --- Edit phase: single-row actions -----------------------------------

    public function test_attach_adds_pivot_row_with_next_order()
    {
        $fieldset = CustomFieldset::factory()->create();
        $existing = CustomField::factory()->create();
        $new = CustomField::factory()->create();
        $fieldset->fields()->attach($existing, ['order' => 1, 'required' => false]);

        Livewire::actingAs($this->editorUser())
            ->test(FieldsetFieldPicker::class, ['fieldset' => $fieldset])
            ->call('attach', $new->id);

        $this->assertDatabaseHas('custom_field_custom_fieldset', [
            'custom_fieldset_id' => $fieldset->id,
            'custom_field_id' => $new->id,
            'order' => 2,
            'required' => 0,
        ]);
    }

    public function test_attach_skips_already_attached_field()
    {
        $fieldset = CustomFieldset::factory()->create();
        $field = CustomField::factory()->create();
        $fieldset->fields()->attach($field, ['order' => 5, 'required' => true]);

        Livewire::actingAs($this->editorUser())
            ->test(FieldsetFieldPicker::class, ['fieldset' => $fieldset])
            ->call('attach', $field->id);

        // Still exactly one row for this fieldset. Existing order +
        // required unchanged (not stomped by the duplicate attach).
        // Scoped to our fieldset because a migration seeds an unrelated
        // MAC-Address pivot row at DB setup time.
        $this->assertSame(1, \DB::table('custom_field_custom_fieldset')
            ->where('custom_fieldset_id', $fieldset->id)
            ->count());
        $this->assertDatabaseHas('custom_field_custom_fieldset', [
            'custom_fieldset_id' => $fieldset->id,
            'custom_field_id' => $field->id,
            'order' => 5,
            'required' => 1,
        ]);
    }

    public function test_detach_removes_pivot_and_clears_from_selection()
    {
        $fieldset = CustomFieldset::factory()->create();
        $field = CustomField::factory()->create();
        $fieldset->fields()->attach($field, ['order' => 1, 'required' => false]);

        Livewire::actingAs($this->editorUser())
            ->test(FieldsetFieldPicker::class, ['fieldset' => $fieldset])
            ->set('selectedFieldIds', [$field->id])
            ->call('detach', $field->id)
            ->assertSet('selectedFieldIds', []);

        $this->assertDatabaseMissing('custom_field_custom_fieldset', [
            'custom_fieldset_id' => $fieldset->id,
            'custom_field_id' => $field->id,
        ]);
    }

    public function test_toggle_required_flips_pivot_bool_and_preserves_order()
    {
        $fieldset = CustomFieldset::factory()->create();
        $field = CustomField::factory()->create();
        $fieldset->fields()->attach($field, ['order' => 7, 'required' => false]);

        Livewire::actingAs($this->editorUser())
            ->test(FieldsetFieldPicker::class, ['fieldset' => $fieldset])
            ->call('toggleRequired', $field->id);

        $this->assertDatabaseHas('custom_field_custom_fieldset', [
            'custom_fieldset_id' => $fieldset->id,
            'custom_field_id' => $field->id,
            'order' => 7,
            'required' => 1,
        ]);
    }

    // --- Edit phase: reorder ----------------------------------------------

    public function test_reorder_moves_item_to_target_position_and_preserves_required_flags()
    {
        $fieldset = CustomFieldset::factory()->create();
        [$a, $b, $c, $d] = CustomField::factory()->count(4)->create();
        $fieldset->fields()->attach($a, ['order' => 1, 'required' => true]);
        $fieldset->fields()->attach($b, ['order' => 2, 'required' => false]);
        $fieldset->fields()->attach($c, ['order' => 3, 'required' => true]);
        $fieldset->fields()->attach($d, ['order' => 4, 'required' => false]);

        // Move d (currently last) to position 0 (first).
        Livewire::actingAs($this->editorUser())
            ->test(FieldsetFieldPicker::class, ['fieldset' => $fieldset])
            ->call('reorder', $d->id, 0);

        // Final order: d, a, b, c. Required flag preserved per row.
        $this->assertDatabaseHas('custom_field_custom_fieldset', [
            'custom_fieldset_id' => $fieldset->id,
            'custom_field_id' => $d->id,
            'order' => 1,
            'required' => 0,
        ]);
        $this->assertDatabaseHas('custom_field_custom_fieldset', [
            'custom_fieldset_id' => $fieldset->id,
            'custom_field_id' => $a->id,
            'order' => 2,
            'required' => 1,
        ]);
        $this->assertDatabaseHas('custom_field_custom_fieldset', [
            'custom_fieldset_id' => $fieldset->id,
            'custom_field_id' => $b->id,
            'order' => 3,
            'required' => 0,
        ]);
        $this->assertDatabaseHas('custom_field_custom_fieldset', [
            'custom_fieldset_id' => $fieldset->id,
            'custom_field_id' => $c->id,
            'order' => 4,
            'required' => 1,
        ]);
    }

    // --- Edit phase: bulk actions -----------------------------------------

    public function test_bulk_attach_adds_multiple_and_dedupes_against_already_attached()
    {
        $fieldset = CustomFieldset::factory()->create();
        [$existing, $new1, $new2] = CustomField::factory()->count(3)->create();
        $fieldset->fields()->attach($existing, ['order' => 1, 'required' => false]);

        Livewire::actingAs($this->editorUser())
            ->test(FieldsetFieldPicker::class, ['fieldset' => $fieldset])
            ->set('selectedAvailableIds', [$existing->id, $new1->id, $new2->id])
            ->call('bulkAttach')
            ->assertSet('selectedAvailableIds', []);

        $this->assertDatabaseHas('custom_field_custom_fieldset', [
            'custom_fieldset_id' => $fieldset->id,
            'custom_field_id' => $new1->id,
            'order' => 2,
        ]);
        $this->assertDatabaseHas('custom_field_custom_fieldset', [
            'custom_fieldset_id' => $fieldset->id,
            'custom_field_id' => $new2->id,
            'order' => 3,
        ]);
        $this->assertSame(3, \DB::table('custom_field_custom_fieldset')
            ->where('custom_fieldset_id', $fieldset->id)
            ->count());
    }

    public function test_bulk_remove_detaches_everything_selected_and_clears_selection()
    {
        $fieldset = CustomFieldset::factory()->create();
        [$a, $b, $c] = CustomField::factory()->count(3)->create();
        $fieldset->fields()->attach($a, ['order' => 1, 'required' => false]);
        $fieldset->fields()->attach($b, ['order' => 2, 'required' => false]);
        $fieldset->fields()->attach($c, ['order' => 3, 'required' => false]);

        Livewire::actingAs($this->editorUser())
            ->test(FieldsetFieldPicker::class, ['fieldset' => $fieldset])
            ->set('selectedFieldIds', [$a->id, $c->id])
            ->call('bulkRemove')
            ->assertSet('selectedFieldIds', []);

        $this->assertDatabaseMissing('custom_field_custom_fieldset', [
            'custom_fieldset_id' => $fieldset->id,
            'custom_field_id' => $a->id,
        ]);
        $this->assertDatabaseMissing('custom_field_custom_fieldset', [
            'custom_fieldset_id' => $fieldset->id,
            'custom_field_id' => $c->id,
        ]);
        $this->assertDatabaseHas('custom_field_custom_fieldset', [
            'custom_fieldset_id' => $fieldset->id,
            'custom_field_id' => $b->id,
        ]);
    }

    public function test_bulk_set_required_updates_every_selected_row()
    {
        $fieldset = CustomFieldset::factory()->create();
        [$a, $b] = CustomField::factory()->count(2)->create();
        $fieldset->fields()->attach($a, ['order' => 1, 'required' => false]);
        $fieldset->fields()->attach($b, ['order' => 2, 'required' => false]);

        Livewire::actingAs($this->editorUser())
            ->test(FieldsetFieldPicker::class, ['fieldset' => $fieldset])
            ->set('selectedFieldIds', [$a->id, $b->id])
            ->call('bulkSetRequired', true);

        $this->assertDatabaseHas('custom_field_custom_fieldset', [
            'custom_fieldset_id' => $fieldset->id,
            'custom_field_id' => $a->id,
            'required' => 1,
            'order' => 1,
        ]);
        $this->assertDatabaseHas('custom_field_custom_fieldset', [
            'custom_fieldset_id' => $fieldset->id,
            'custom_field_id' => $b->id,
            'required' => 1,
            'order' => 2,
        ]);
    }

    public function test_toggle_select_all_available_merges_additively_across_pages()
    {
        $fieldset = CustomFieldset::factory()->create();

        Livewire::actingAs($this->editorUser())
            ->test(FieldsetFieldPicker::class, ['fieldset' => $fieldset])
            ->call('toggleSelectAllAvailable', true, [10, 11, 12])
            ->assertSet('selectedAvailableIds', [10, 11, 12])
            ->call('toggleSelectAllAvailable', true, [20, 21])
            ->assertSet('selectedAvailableIds', [10, 11, 12, 20, 21])
            ->call('toggleSelectAllAvailable', false, [10, 11, 12])
            ->assertSet('selectedAvailableIds', [20, 21]);
    }
}
