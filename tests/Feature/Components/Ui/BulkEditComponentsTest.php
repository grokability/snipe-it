<?php

namespace Tests\Feature\Components\Ui;

use App\Models\Asset;
use App\Models\Category;
use App\Models\Company;
use App\Models\Component;
use App\Models\Location;
use App\Models\Manufacturer;
use App\Models\Supplier;
use App\Models\User;
use Tests\TestCase;

class BulkEditComponentsTest extends TestCase
{
    public function test_requires_update_permission_to_save(): void
    {
        $component = Component::factory()->create();

        $this->actingAs(User::factory()->viewComponents()->create())
            ->post(route('components.bulkedit.store'), [
                'ids' => [$component->id],
                'archived' => '1',
            ])
            ->assertForbidden();

        $this->assertFalse((bool) $component->fresh()->archived);
    }

    public function test_dispatcher_shows_edit_view_for_edit_action(): void
    {
        $component = Component::factory()->create();

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('components.bulkedit.show'), [
                'ids' => [$component->id],
                'bulk_actions' => 'edit',
            ])
            ->assertOk()
            ->assertViewIs('components.bulk-edit');
    }

    public function test_dispatcher_shows_delete_view_for_delete_action(): void
    {
        $component = Component::factory()->create();

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('components.bulkedit.show'), [
                'ids' => [$component->id],
                'bulk_actions' => 'delete',
            ])
            ->assertOk()
            ->assertViewIs('components.bulk-delete');
    }

    public function test_dispatcher_rejects_empty_selection(): void
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('components.bulkedit.show'), ['bulk_actions' => 'edit'])
            ->assertRedirect(route('components.index'))
            ->assertSessionHas('error');
    }

    public function test_update_writes_only_provided_fields(): void
    {
        // Blank form inputs mean "keep as-is". Mirror a form POST where
        // only location changes to confirm every other column is left
        // alone across the selected rows.
        $originalCategory = Category::factory()->create();
        $originalLocation = Location::factory()->create();
        $newLocation = Location::factory()->create();

        $one = Component::factory()->create([
            'category_id' => $originalCategory->id,
            'location_id' => $originalLocation->id,
            'min_amt' => 5,
        ]);
        $two = Component::factory()->create([
            'category_id' => $originalCategory->id,
            'location_id' => $originalLocation->id,
            'min_amt' => 5,
        ]);

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('components.bulkedit.store'), [
                'ids' => [$one->id, $two->id],
                'company_id' => '',
                'category_id' => '',
                'manufacturer_id' => '',
                'location_id' => (string) $newLocation->id,
                'default_supplier_id' => '',
                'min_amt' => '',
                'requestable' => '',
                'archived' => '',
            ])
            ->assertRedirect(route('components.index'))
            ->assertSessionHas('success');

        $this->assertSame($newLocation->id, $one->fresh()->location_id);
        $this->assertSame($newLocation->id, $two->fresh()->location_id);
        $this->assertSame($originalCategory->id, $one->fresh()->category_id);
        $this->assertSame(5, (int) $one->fresh()->min_amt);
    }

    public function test_update_can_set_archived_across_selection(): void
    {
        $one = Component::factory()->create();
        $two = Component::factory()->create();

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('components.bulkedit.store'), [
                'ids' => [$one->id, $two->id],
                'archived' => '1',
            ])
            ->assertRedirect(route('components.index'));

        $this->assertTrue((bool) $one->fresh()->archived);
        $this->assertTrue((bool) $two->fresh()->archived);
    }

    public function test_update_flashes_error_when_no_rows_updated_due_to_validation(): void
    {
        // Regression test: archive-ing components that all have active
        // checkouts should surface a model-validation error, not a
        // "0 components were updated" success flash.
        $one = Component::factory()->create(['qty' => 5]);
        $two = Component::factory()->create(['qty' => 5]);
        $assetOne = Asset::factory()->create();
        $assetTwo = Asset::factory()->create();
        $one->assets()->attach($assetOne->id, ['assigned_qty' => 1, 'created_at' => now(), 'created_by' => 1]);
        $two->assets()->attach($assetTwo->id, ['assigned_qty' => 1, 'created_at' => now(), 'created_by' => 1]);

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('components.bulkedit.store'), [
                'ids' => [$one->id, $two->id],
                'archived' => '1',
            ])
            ->assertRedirect(route('components.index'))
            ->assertSessionHas('error')
            ->assertSessionMissing('success')
            ->assertSessionHas('multi_error_messages');

        $this->assertFalse((bool) $one->fresh()->archived);
        $this->assertFalse((bool) $two->fresh()->archived);
    }

    public function test_update_flashes_warning_on_partial_validation_failure(): void
    {
        // Regression test: one component saves, one is rejected by
        // validation. Response should be warning + error details, not
        // plain success.
        $saveable = Component::factory()->create(['qty' => 5]);
        $blocked = Component::factory()->create(['qty' => 5]);
        $asset = Asset::factory()->create();
        $blocked->assets()->attach($asset->id, ['assigned_qty' => 1, 'created_at' => now(), 'created_by' => 1]);

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('components.bulkedit.store'), [
                'ids' => [$saveable->id, $blocked->id],
                'archived' => '1',
            ])
            ->assertRedirect(route('components.index'))
            ->assertSessionHas('warning')
            ->assertSessionMissing('success')
            ->assertSessionHas('multi_error_messages');

        $this->assertTrue((bool) $saveable->fresh()->archived);
        $this->assertFalse((bool) $blocked->fresh()->archived);
    }

    public function test_update_rejects_empty_selection(): void
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('components.bulkedit.store'), ['archived' => '1'])
            ->assertRedirect(route('components.index'))
            ->assertSessionHas('error');
    }

    public function test_update_warns_when_no_fields_posted(): void
    {
        $component = Component::factory()->create();

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('components.bulkedit.store'), [
                'ids' => [$component->id],
            ])
            ->assertRedirect(route('components.index'))
            ->assertSessionHas('warning');
    }

    public function test_update_covers_full_classification_field_set(): void
    {
        // The field set exposed by BulkComponentsController::EDITABLE_FIELDS
        // is the contract the UI depends on. If someone narrows it (removes
        // a column from EDITABLE_FIELDS or the bulk-edit view), that column
        // silently stops writing across the selection. Pin all of them so
        // the regression surfaces here.
        $company = Company::factory()->create();
        $category = Category::factory()->create();
        $manufacturer = Manufacturer::factory()->create();
        $location = Location::factory()->create();
        $supplier = Supplier::factory()->create();

        $component = Component::factory()->create(['min_amt' => 1]);

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('components.bulkedit.store'), [
                'ids' => [$component->id],
                'company_id' => (string) $company->id,
                'category_id' => (string) $category->id,
                'manufacturer_id' => (string) $manufacturer->id,
                'location_id' => (string) $location->id,
                'default_supplier_id' => (string) $supplier->id,
                'min_amt' => '42',
                'requestable' => '1',
                'archived' => '0',
            ])
            ->assertRedirect(route('components.index'));

        $fresh = $component->fresh();
        $this->assertSame($company->id, $fresh->company_id);
        $this->assertSame($category->id, $fresh->category_id);
        $this->assertSame($manufacturer->id, $fresh->manufacturer_id);
        $this->assertSame($location->id, $fresh->location_id);
        $this->assertSame($supplier->id, $fresh->default_supplier_id);
        $this->assertSame(42, (int) $fresh->min_amt);
        $this->assertTrue((bool) $fresh->requestable);
        $this->assertFalse((bool) $fresh->archived);
    }
}
