<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Importer;
use App\Models\CustomField;
use App\Models\CustomFieldset;
use App\Models\Import;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ImporterTest extends TestCase
{
    /**
     * Write a minimal CSV file at the imports path an Import record points
     * to, so selectFile()'s file-existence guard passes. The guard was added
     * to block the wizard when a demo-seeded Import references a file that
     * was deleted outside Snipe-IT (e.g. by git clean). Tests that seed
     * factory Imports without a real file need to plant one here to exercise
     * the code path beyond the guard.
     */
    protected function writeFakeImportFile(Import $import, string $csvBody = "a,b,c\n1,2,3\n"): void
    {
        Storage::put('private_uploads/imports/'.$import->file_path, $csvBody);

        $path = config('app.private_uploads').'/imports/'.$import->file_path;
        file_put_contents($path, $csvBody);
        $this->fakeImportPaths[] = $path;
    }

    /** @var array<int, string> */
    protected array $fakeImportPaths = [];

    protected function tearDown(): void
    {
        foreach ($this->fakeImportPaths as $path) {
            @unlink($path);
        }
        parent::tearDown();
    }

    public function test_renders_successfully()
    {
        Livewire::actingAs(User::factory()->canImport()->create())
            ->test(Importer::class)
            ->assertStatus(200);
    }

    public function test_requires_permission()
    {
        Livewire::actingAs(User::factory()->create())
            ->test(Importer::class)
            ->assertStatus(403);
    }

    public function test_bulk_deletes_owned_imports()
    {
        Storage::fake();
        $user = User::factory()->canImport()->create();
        $imports = Import::factory()->count(3)->create(['created_by' => $user->id]);

        Livewire::actingAs($user)
            ->test(Importer::class)
            ->set('selectedIds', $imports->pluck('id')->map(fn ($id) => (string) $id)->all())
            ->call('bulkDestroy')
            ->assertSet('message_type', 'success');

        foreach ($imports as $import) {
            $this->assertDatabaseMissing('imports', ['id' => $import->id]);
        }
    }

    public function test_bulk_destroy_skips_imports_the_caller_does_not_own()
    {
        Storage::fake();
        $me = User::factory()->canImport()->create();
        $someoneElse = User::factory()->canImport()->create();

        $mine = Import::factory()->create(['created_by' => $me->id]);
        $theirs = Import::factory()->create(['created_by' => $someoneElse->id]);

        Livewire::actingAs($me)
            ->test(Importer::class)
            ->set('selectedIds', [(string) $mine->id, (string) $theirs->id])
            ->call('bulkDestroy')
            ->assertSet('message_type', 'success');

        $this->assertDatabaseMissing('imports', ['id' => $mine->id]);
        $this->assertDatabaseHas('imports', ['id' => $theirs->id]);
    }

    public function test_bulk_destroy_all_denied_produces_error_message()
    {
        Storage::fake();
        $me = User::factory()->canImport()->create();
        $someoneElse = User::factory()->canImport()->create();

        $theirs = Import::factory()->count(2)->create(['created_by' => $someoneElse->id]);

        Livewire::actingAs($me)
            ->test(Importer::class)
            ->set('selectedIds', $theirs->pluck('id')->map(fn ($id) => (string) $id)->all())
            ->call('bulkDestroy')
            ->assertSet('message_type', 'danger');

        // Neither import was deleted.
        foreach ($theirs as $import) {
            $this->assertDatabaseHas('imports', ['id' => $import->id]);
        }
    }

    public function test_superuser_can_bulk_delete_anyone_elses_imports()
    {
        Storage::fake();
        $superuser = User::factory()->superuser()->create();
        $owner = User::factory()->canImport()->create();
        $imports = Import::factory()->count(2)->create(['created_by' => $owner->id]);

        Livewire::actingAs($superuser)
            ->test(Importer::class)
            ->set('selectedIds', $imports->pluck('id')->map(fn ($id) => (string) $id)->all())
            ->call('bulkDestroy')
            ->assertSet('message_type', 'success');

        foreach ($imports as $import) {
            $this->assertDatabaseMissing('imports', ['id' => $import->id]);
        }
    }

    public function test_bulk_destroy_with_no_selection_does_nothing()
    {
        Storage::fake();
        $user = User::factory()->canImport()->create();
        Import::factory()->create(['created_by' => $user->id]);

        Livewire::actingAs($user)
            ->test(Importer::class)
            ->call('bulkDestroy')
            ->assertSet('message', null);

        $this->assertDatabaseCount('imports', 1);
    }

    public function test_files_paginate_by_per_page()
    {
        $user = User::factory()->canImport()->create();
        Import::factory()->count(30)->create(['created_by' => $user->id]);

        $component = Livewire::actingAs($user)->test(Importer::class);

        // Default $perPage = 25; 30 imports means 25 on page 1, 5 on page 2.
        $this->assertCount(25, $component->instance()->files->items());
        $this->assertSame(30, $component->instance()->files->total());
        $this->assertTrue($component->instance()->files->hasPages());
    }

    public function test_select_all_selects_only_current_page_deletable_rows()
    {
        $me = User::factory()->canImport()->create();
        $someoneElse = User::factory()->canImport()->create();

        // 5 mine + 3 theirs = 8 imports on page 1 (fits under the 25 default).
        $mine = Import::factory()->count(5)->create(['created_by' => $me->id]);
        Import::factory()->count(3)->create(['created_by' => $someoneElse->id]);

        $component = Livewire::actingAs($me)
            ->test(Importer::class)
            ->set('selectAll', true);

        // Only the 5 the caller can delete are picked up.
        $selected = $component->get('selectedIds');
        $this->assertCount(5, $selected);
        $this->assertEquals(
            $mine->pluck('id')->sort()->values()->all(),
            collect($selected)->map(fn ($id) => (int) $id)->sort()->values()->all()
        );
    }

    public function test_changing_page_clears_selection()
    {
        $user = User::factory()->canImport()->create();
        Import::factory()->count(30)->create(['created_by' => $user->id]);

        $component = Livewire::actingAs($user)
            ->test(Importer::class)
            ->set('selectAll', true);

        $this->assertNotEmpty($component->get('selectedIds'));

        // Livewire's WithPagination exposes a setPage() method; calling it
        // triggers the updatedPage hook where we clear selection.
        $component->call('setPage', 2);

        $this->assertSame([], $component->get('selectedIds'));
        $this->assertFalse($component->get('selectAll'));
    }

    /**
     * Security regression pin. Before the fix in Importer::files, a
     * non-superuser with the import permission could see every other
     * user's uploaded CSV in the file list (created_by scoping was
     * absent, matching the API's per-owner filter was not applied).
     */
    public function test_file_list_excludes_imports_owned_by_other_users(): void
    {
        Storage::fake();
        $me = User::factory()->canImport()->create();
        $someoneElse = User::factory()->canImport()->create();

        $mine = Import::factory()->count(2)->create(['created_by' => $me->id]);
        $theirs = Import::factory()->count(3)->create(['created_by' => $someoneElse->id]);

        $files = Livewire::actingAs($me)
            ->test(Importer::class)
            ->get('files');

        $visibleIds = collect($files->items())->pluck('id')->all();
        sort($visibleIds);

        $expected = $mine->pluck('id')->sort()->values()->all();

        $this->assertSame($expected, $visibleIds, 'File list must only include imports owned by the caller');
        foreach ($theirs as $import) {
            $this->assertNotContains($import->id, $visibleIds);
        }
    }

    /**
     * Security regression pin. Before the fix in Importer::activeFile,
     * selectFile($id) would populate headerRow / typeOfImport /
     * field_map from ANY Import record, even one owned by another user.
     * With the owner scope in place, activeFile returns null for a
     * cross-user id and selectFile's "file not found" branch fires
     * without ever reading the target's preview state.
     */
    public function test_selecting_another_users_import_does_not_leak_preview(): void
    {
        Storage::fake();
        $me = User::factory()->canImport()->create();
        $someoneElse = User::factory()->canImport()->create();

        $theirImport = Import::factory()->create([
            'created_by' => $someoneElse->id,
            'header_row' => ['sensitive_column'],
            'import_type' => 'asset',
        ]);

        Livewire::actingAs($me)
            ->test(Importer::class)
            ->call('selectFile', $theirImport->id)
            ->assertSet('headerRow', [])
            ->assertSet('typeOfImport', null)
            ->assertSet('message_type', 'danger');
    }

    /**
     * Positive-path sanity: the owner still sees + selects their own imports.
     */
    public function test_owner_can_still_select_their_own_import(): void
    {
        Storage::fake();
        $me = User::factory()->canImport()->create();

        $mine = Import::factory()->create([
            'created_by' => $me->id,
            'header_row' => ['my_column'],
            'import_type' => 'asset',
        ]);
        $this->writeFakeImportFile($mine, "my_column\nvalue\n");

        Livewire::actingAs($me)
            ->test(Importer::class)
            ->call('selectFile', $mine->id)
            ->assertSet('headerRow', ['my_column'])
            ->assertSet('typeOfImport', 'asset');
    }

    /**
     * Superusers keep the cross-user visibility they've always had.
     */
    public function test_superuser_sees_all_imports(): void
    {
        Storage::fake();
        $superuser = User::factory()->superuser()->create();
        $someoneElse = User::factory()->canImport()->create();

        $theirs = Import::factory()->count(2)->create(['created_by' => $someoneElse->id]);

        $files = Livewire::actingAs($superuser)
            ->test(Importer::class)
            ->get('files');

        $visibleIds = collect($files->items())->pluck('id')->all();
        foreach ($theirs as $import) {
            $this->assertContains($import->id, $visibleIds);
        }
    }

    public function test_selecting_a_file_dispatches_open_import_modal_event(): void
    {
        Storage::fake();
        $user = User::factory()->canImport()->create();
        $import = Import::factory()->create([
            'created_by' => $user->id,
            'header_row' => ['asset tag'],
        ]);
        $this->writeFakeImportFile($import, "asset tag\nAH-1\n");

        Livewire::actingAs($user)
            ->test(Importer::class)
            ->call('selectFile', $import->id)
            ->assertDispatched('open-import-modal');
    }

    public function test_selecting_a_file_populates_row_count_from_csv(): void
    {
        $user = User::factory()->canImport()->create();

        $csv = "asset tag,name,email,checkout date\n"
            ."AH-1,alice,,2025-01-01\n"
            ."AH-2,bob,,2025-01-02\n"
            ."AH-3,carol,,2025-01-03\n";

        $filename = 'row-count-'.uniqid().'.csv';
        file_put_contents(config('app.private_uploads').'/imports/'.$filename, $csv);

        try {
            $import = Import::factory()->create([
                'created_by' => $user->id,
                'file_path' => $filename,
                'header_row' => ['asset tag', 'name', 'email', 'checkout date'],
                'first_row' => ['AH-1', 'alice', '', '2025-01-01'],
            ]);

            Livewire::actingAs($user)
                ->test(Importer::class)
                ->call('selectFile', $import->id)
                ->assertSet('activeFileRowCount', 3);
        } finally {
            @unlink(config('app.private_uploads').'/imports/'.$filename);
        }
    }

    public function test_row_count_is_zero_when_csv_file_is_missing_from_disk(): void
    {
        Storage::fake();
        $user = User::factory()->canImport()->create();
        $import = Import::factory()->create([
            'created_by' => $user->id,
            'file_path' => 'never-existed-'.uniqid().'.csv',
            'header_row' => ['asset tag'],
        ]);

        Livewire::actingAs($user)
            ->test(Importer::class)
            ->call('selectFile', $import->id)
            ->assertSet('activeFileRowCount', 0);
    }

    /**
     * Regression for Rollbar: selectFile() foreach()-on-null when the Import
     * row was persisted with header_row = null (legacy imports, or a background
     * job that never wrote the column). Previously exploded with
     * "foreach() argument must be of type array|object, null given" at the
     * headerRow loop. The guard now short-circuits with a translated error.
     */
    public function test_selecting_a_file_with_null_header_row_shows_error_and_does_not_crash(): void
    {
        $user = User::factory()->canImport()->create();
        $import = Import::factory()->create([
            'created_by' => $user->id,
            'header_row' => null,
        ]);
        $this->writeFakeImportFile($import, "asset tag\nAH-1\n");

        Livewire::actingAs($user)
            ->test(Importer::class)
            ->call('selectFile', $import->id)
            ->assertSet('message_type', 'danger')
            ->assertSet('message', trans('admin/hardware/message.import.header_row_missing'));
    }

    public function test_next_step_from_type_selection_advances_when_type_is_set(): void
    {
        Storage::fake();
        $user = User::factory()->canImport()->create();
        $import = Import::factory()->create([
            'created_by' => $user->id,
            'header_row' => ['asset tag'],
        ]);

        Livewire::actingAs($user)
            ->test(Importer::class)
            ->call('selectFile', $import->id)
            ->assertSet('wizardStep', 1)
            ->set('typeOfImport', 'user')
            ->call('nextStep')
            ->assertSet('wizardStep', 2);
    }

    public function test_next_step_blocks_when_no_type_is_selected(): void
    {
        Storage::fake();
        $user = User::factory()->canImport()->create();
        $import = Import::factory()->create([
            'created_by' => $user->id,
            'header_row' => ['asset tag'],
        ]);

        Livewire::actingAs($user)
            ->test(Importer::class)
            ->call('selectFile', $import->id)
            ->set('typeOfImport', '')
            ->call('nextStep')
            ->assertSet('wizardStep', 1)
            ->assertSet('statusType', 'error');
    }

    public function test_previous_step_walks_backwards(): void
    {
        Storage::fake();
        $user = User::factory()->canImport()->create();
        $import = Import::factory()->create([
            'created_by' => $user->id,
            'header_row' => ['asset tag'],
        ]);

        Livewire::actingAs($user)
            ->test(Importer::class)
            ->call('selectFile', $import->id)
            ->set('typeOfImport', 'user')
            ->call('nextStep') // step 2
            ->call('previousStep')
            ->assertSet('wizardStep', 1);
    }

    public function test_next_step_from_mapping_blocks_when_required_field_is_unmapped(): void
    {
        Storage::fake();
        $user = User::factory()->canImport()->create();
        // User import requires first_name (per User::$rules); leave field_map
        // pointing at nothing useful so the required check fires.
        $import = Import::factory()->create([
            'created_by' => $user->id,
            'header_row' => ['Some Column', 'Another Column'],
        ]);

        Livewire::actingAs($user)
            ->test(Importer::class)
            ->call('selectFile', $import->id)
            ->set('typeOfImport', 'user')
            ->call('nextStep') // -> step 2
            ->set('field_map', [null, null]) // nothing mapped
            ->call('nextStep') // should block
            ->assertSet('wizardStep', 2)
            ->assertSet('statusType', 'error');
    }

    public function test_mapping_step_renders_every_csv_header_even_when_auto_map_finds_no_match(): void
    {
        // Reporter feedback on #19450 (swift2512 / Dewi4nt): the wizard
        // was silently omitting CSV columns whose headers didn't hit an
        // auto-match or an alias, which left users with no way to
        // hand-map them. Every header should show up in the mapping list
        // - matched ones with the target pre-selected, unmatched ones
        // defaulting to "Do not import" (rendered as an empty-value
        // option) with the dropdown available.
        Storage::fake();
        $user = User::factory()->canImport()->create();

        $import = Import::factory()->create([
            'created_by' => $user->id,
            'header_row' => ['Asset Tag', 'lorem ipsum column'],
            'first_row' => ['ASSET-001', 'whatever'],
            'import_type' => 'asset',
        ]);
        $this->writeFakeImportFile($import, "Asset Tag,lorem ipsum column\nASSET-001,whatever\n");

        $component = Livewire::actingAs($user)
            ->test(Importer::class)
            ->call('selectFile', $import->id)
            ->set('typeOfImport', 'asset');

        // Both header positions are represented in field_map. Index 0 is
        // the auto-matched target key, index 1 is null (auto-unmapped -
        // the user picks from the dropdown in the UI).
        $fieldMap = $component->get('field_map');
        $this->assertCount(2, $fieldMap, 'field_map should hold one entry per CSV header, matched or not.');
        $this->assertSame('asset_tag', $fieldMap[0]);
        $this->assertNull($fieldMap[1]);

        // The unmapped header's raw text is rendered in the mapping list.
        $component->call('nextStep')
            ->assertSet('wizardStep', 2)
            ->assertSee('lorem ipsum column')
            ->assertSee('Asset Tag');
    }

    public function test_next_step_from_mapping_advances_when_required_fields_are_mapped(): void
    {
        $user = User::factory()->canImport()->create();

        $csv = "First Name,Username\nAlice,alice\nBob,bob\n";
        $filename = 'wiz-'.uniqid().'.csv';
        file_put_contents(config('app.private_uploads').'/imports/'.$filename, $csv);

        try {
            $import = Import::factory()->create([
                'created_by' => $user->id,
                'file_path' => $filename,
                'header_row' => ['First Name', 'Username'],
                'first_row' => ['Alice', 'alice'],
            ]);

            Livewire::actingAs($user)
                ->test(Importer::class)
                ->call('selectFile', $import->id)
                ->set('typeOfImport', 'user')
                ->call('nextStep') // -> step 2
                ->set('field_map', ['first_name', 'username'])
                ->call('nextStep') // -> step 3
                ->assertSet('wizardStep', 3)
                ->assertCount('previewRows', 2);
        } finally {
            @unlink(config('app.private_uploads').'/imports/'.$filename);
        }
    }

    public function test_asset_history_required_fields_are_hardcoded_not_from_model(): void
    {
        Livewire::actingAs(User::factory()->canImport()->create())
            ->test(Importer::class)
            ->tap(function ($c) {
                $this->assertEquals(
                    ['asset_tag', 'full_name', 'checkout_date'],
                    $c->instance()->requiredForType('assetHistory'),
                );
            });
    }

    public function test_user_required_fields_derived_from_user_model_rules(): void
    {
        // User::$rules requires first_name; requiredForType('user') should
        // include it even though we didn't hardcode it.
        Livewire::actingAs(User::factory()->canImport()->create())
            ->test(Importer::class)
            ->tap(function ($c) {
                $required = $c->instance()->requiredForType('user');
                $this->assertContains('first_name', $required);
            });
    }

    public function test_license_required_fields_do_not_include_seats(): void
    {
        // Regression for #19467 follow-up. License::$rules requires seats
        // for the create path, but a seat-assignment import (matching an
        // existing license) does not use seats. The wizard cannot know
        // per-row whether the caller intends create or update, so it must
        // not enforce seats at wizard level. Per-row server-side validation
        // still enforces seats on License::save() for genuine creates.
        Livewire::actingAs(User::factory()->canImport()->create())
            ->test(Importer::class)
            ->tap(function ($c) {
                $required = $c->instance()->requiredForType('license');
                $this->assertNotContains(
                    'seats',
                    $required,
                    'Seats should not be flagged as required at the wizard level for license imports.',
                );
                $this->assertContains('item_name', $required, 'item_name should still be required at the wizard level for license imports.');
            });
    }

    public function test_asset_required_fields_do_not_include_custom_fields_required_in_some_fieldsets_only(): void
    {
        // Regression for #19468. A CSV of assets can span multiple asset
        // models with different fieldsets. A custom field required in one
        // fieldset but not in another (or not attached to another) must not
        // be flagged as required at the wizard level, because rows destined
        // for the other fieldset don't need it. Per-asset server-side
        // validation on Asset::save() enforces the correct rule at save-time.
        $customField = CustomField::factory()->create(['name' => 'Priority']);

        $laptopFieldset = CustomFieldset::factory()->create();
        $laptopFieldset->fields()->attach($customField, ['required' => 1, 'order' => 1]);

        // Second fieldset that does NOT include the custom field at all.
        CustomFieldset::factory()->create();

        Livewire::actingAs(User::factory()->canImport()->create())
            ->test(Importer::class)
            ->tap(function ($c) use ($customField) {
                $required = $c->instance()->requiredForType('asset');
                $this->assertNotContains(
                    $customField->db_column_name(),
                    $required,
                    'Custom field required in only some fieldsets should not be flagged as required at the wizard level.',
                );
            });
    }

    public function test_next_step_auto_maps_fields_even_when_type_was_preselected(): void
    {
        // Regression: updatingTypeOfImport only fires on wire-model changes.
        // When selectFile programmatically assigns import_type, the auto-map
        // never runs and users land on step 2 with an all-null field_map.
        // nextStep() 1->2 now re-runs the auto-map so headers still bind.
        Storage::fake();
        $user = User::factory()->canImport()->create();
        $import = Import::factory()->create([
            'created_by' => $user->id,
            'import_type' => 'user',
            'header_row' => ['First Name', 'Username', 'Email'],
        ]);
        $this->writeFakeImportFile($import, "First Name,Username,Email\nAlice,alice,alice@example.com\n");

        Livewire::actingAs($user)
            ->test(Importer::class)
            ->call('selectFile', $import->id)
            ->call('nextStep')
            ->tap(function ($c) {
                $map = $c->get('field_map');
                $this->assertContains('first_name', $map);
                $this->assertContains('username', $map);
                $this->assertContains('email', $map);
            });
    }

    public function test_auto_map_handles_underscore_style_headers(): void
    {
        // Regression: CSV column "asset_tag" (underscore) should still bind
        // to the asset_tag target field, even though the target's display
        // label is "Asset Tag" (with a space).
        Storage::fake();
        $user = User::factory()->canImport()->create();
        $import = Import::factory()->create([
            'created_by' => $user->id,
            'import_type' => 'asset',
            'header_row' => ['asset_tag', 'serial_number', 'purchase_cost'],
        ]);
        $this->writeFakeImportFile($import, "asset_tag,serial_number,purchase_cost\nAH-1,ser-1,100\n");

        Livewire::actingAs($user)
            ->test(Importer::class)
            ->call('selectFile', $import->id)
            ->call('nextStep')
            ->tap(function ($c) {
                $map = $c->get('field_map');
                $this->assertContains('asset_tag', $map);
                $this->assertContains('serial', $map);
                $this->assertContains('purchase_cost', $map);
            });
    }

    public function test_demo_mode_blocks_start_processing_for_non_superadmin(): void
    {
        // With lock_passwords set the Process button on the wizard is
        // disabled in the blade, but a hand-crafted Livewire call would
        // still fire the action - guard it server-side so the modal can't
        // flip into processing mode either. Superadmins bypass this gate
        // in demo mode so the seeded demo imports can actually be run.
        config(['app.lock_passwords' => true]);

        $user = User::factory()->canImport()->create();

        Livewire::actingAs($user)
            ->test(Importer::class)
            ->call('startProcessing')
            ->assertSet('processing', false)
            ->assertSet('message_type', 'danger');
    }

    public function test_demo_mode_allows_start_processing_for_superadmin(): void
    {
        config(['app.lock_passwords' => true]);

        $user = User::factory()->canImport()->superuser()->create();
        $file = Import::factory()->create();

        Livewire::actingAs($user)
            ->test(Importer::class)
            ->set('activeFileId', $file->id)
            ->call('startProcessing')
            ->assertSet('processing', true);
    }

    public function test_demo_mode_blocks_destroy(): void
    {
        config(['app.lock_passwords' => true]);

        Storage::fake();
        $user = User::factory()->canImport()->create();
        $import = Import::factory()->create(['created_by' => $user->id]);

        Livewire::actingAs($user)
            ->test(Importer::class)
            ->call('destroy', $import->id)
            ->assertSet('message_type', 'danger');

        $this->assertDatabaseHas('imports', ['id' => $import->id]);
    }

    public function test_demo_mode_blocks_bulk_destroy(): void
    {
        config(['app.lock_passwords' => true]);

        Storage::fake();
        $user = User::factory()->canImport()->create();
        $imports = Import::factory()->count(2)->create(['created_by' => $user->id]);

        Livewire::actingAs($user)
            ->test(Importer::class)
            ->set('selectedIds', $imports->pluck('id')->map(fn ($id) => (string) $id)->all())
            ->call('bulkDestroy')
            ->assertSet('message_type', 'danger');

        foreach ($imports as $import) {
            $this->assertDatabaseHas('imports', ['id' => $import->id]);
        }
    }

    public function test_asset_model_required_field_uses_name_key_not_item_name(): void
    {
        // Regression: earlier iteration of required_field_model_map
        // pointed assetModel at 'item_name' but AssetModel's mapping
        // dropdown uses 'name' as the option value. Result was that the
        // dropdown never lit up as required for a mapped Name column.
        Livewire::actingAs(User::factory()->canImport()->create())
            ->test(Importer::class)
            ->tap(function ($c) {
                $required = $c->instance()->requiredForType('assetModel');
                $this->assertContains('name', $required);
                $this->assertNotContains('item_name', $required);
            });
    }

    public function test_sliced_import_processes_only_the_requested_slice(): void
    {
        $actor = User::factory()->canImport()->superuser()->create();

        // 5-row CSV, slice size 2 starting at offset 1: should touch rows
        // 2 and 3 only. Rows 0, 1 (offset 0), and 4 (past offset+limit)
        // should be untouched.
        $csv = "First Name,Username\n"
            ."alice,alice.slice\n"
            ."bob,bob.slice\n"
            ."carol,carol.slice\n"
            ."dave,dave.slice\n"
            ."eve,eve.slice\n";

        $filename = 'slice-'.uniqid().'.csv';
        file_put_contents(config('app.private_uploads').'/imports/'.$filename, $csv);

        try {
            $import = \App\Models\Import::factory()->create([
                'created_by' => $actor->id,
                'file_path' => $filename,
                'import_type' => 'user',
                'header_row' => ['First Name', 'Username'],
                'first_row' => ['alice', 'alice.slice'],
            ]);

            $this->actingAsForApi($actor);
            $this->postJson(
                route('api.imports.importFile', ['import' => $import->id]),
                [
                    'import-type' => 'user',
                    'import' => $import->id,
                    'offset' => 1,
                    'limit' => 2,
                ],
            )->assertOk();

            $this->assertDatabaseMissing('users', ['username' => 'alice.slice']);
            $this->assertDatabaseHas('users', ['username' => 'bob.slice']);
            $this->assertDatabaseHas('users', ['username' => 'carol.slice']);
            $this->assertDatabaseMissing('users', ['username' => 'dave.slice']);
            $this->assertDatabaseMissing('users', ['username' => 'eve.slice']);
        } finally {
            @unlink(config('app.private_uploads').'/imports/'.$filename);
        }
    }

    public function test_all_slices_together_reach_every_row(): void
    {
        $actor = User::factory()->canImport()->superuser()->create();

        $csv = "First Name,Username\n"
            ."alice,alice.all\n"
            ."bob,bob.all\n"
            ."carol,carol.all\n"
            ."dave,dave.all\n";

        $filename = 'sliceall-'.uniqid().'.csv';
        file_put_contents(config('app.private_uploads').'/imports/'.$filename, $csv);

        try {
            $import = \App\Models\Import::factory()->create([
                'created_by' => $actor->id,
                'file_path' => $filename,
                'import_type' => 'user',
                'header_row' => ['First Name', 'Username'],
                'first_row' => ['alice', 'alice.all'],
            ]);

            $this->actingAsForApi($actor);

            // Two slices of 2 rows each.
            foreach ([0, 2] as $offset) {
                $this->postJson(
                    route('api.imports.importFile', ['import' => $import->id]),
                    [
                        'import-type' => 'user',
                        'import' => $import->id,
                        'offset' => $offset,
                        'limit' => 2,
                    ],
                )->assertOk();
            }

            foreach (['alice.all', 'bob.all', 'carol.all', 'dave.all'] as $username) {
                $this->assertDatabaseHas('users', ['username' => $username]);
            }
        } finally {
            @unlink(config('app.private_uploads').'/imports/'.$filename);
        }
    }

    public function test_import_without_offset_and_limit_processes_whole_file(): void
    {
        // Backwards-compat: any caller that doesn't send offset/limit
        // should see the pre-slicing behavior - the whole CSV in one shot.
        $actor = User::factory()->canImport()->superuser()->create();

        $csv = "First Name,Username\n"
            ."alice,alice.full\n"
            ."bob,bob.full\n"
            ."carol,carol.full\n";

        $filename = 'slicefull-'.uniqid().'.csv';
        file_put_contents(config('app.private_uploads').'/imports/'.$filename, $csv);

        try {
            $import = \App\Models\Import::factory()->create([
                'created_by' => $actor->id,
                'file_path' => $filename,
                'import_type' => 'user',
                'header_row' => ['First Name', 'Username'],
                'first_row' => ['alice', 'alice.full'],
            ]);

            $this->actingAsForApi($actor);
            $this->postJson(
                route('api.imports.importFile', ['import' => $import->id]),
                ['import-type' => 'user', 'import' => $import->id],
            )->assertOk();

            foreach (['alice.full', 'bob.full', 'carol.full'] as $username) {
                $this->assertDatabaseHas('users', ['username' => $username]);
            }
        } finally {
            @unlink(config('app.private_uploads').'/imports/'.$filename);
        }
    }
}
