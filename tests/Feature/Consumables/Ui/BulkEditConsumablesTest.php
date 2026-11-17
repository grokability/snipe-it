<?php

namespace Tests\Feature\Consumables\Ui;

use App\Models\Category;
use App\Models\Company;
use App\Models\Consumable;
use App\Models\Location;
use App\Models\Manufacturer;
use App\Models\Supplier;
use App\Models\User;
use Tests\TestCase;

class BulkEditConsumablesTest extends TestCase
{
    public function test_requires_update_permission_to_save(): void
    {
        $consumable = Consumable::factory()->create();

        $this->actingAs(User::factory()->viewConsumables()->create())
            ->post(route('consumables.bulkedit.store'), [
                'ids' => [$consumable->id],
                'archived' => '1',
            ])
            ->assertForbidden();

        $this->assertFalse((bool) $consumable->fresh()->archived);
    }

    public function test_dispatcher_shows_edit_view_for_edit_action(): void
    {
        $consumable = Consumable::factory()->create();

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('consumables.bulkedit.show'), [
                'ids' => [$consumable->id],
                'bulk_actions' => 'edit',
            ])
            ->assertOk()
            ->assertViewIs('consumables.bulk-edit');
    }

    public function test_dispatcher_shows_delete_view_for_delete_action(): void
    {
        $consumable = Consumable::factory()->create();

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('consumables.bulkedit.show'), [
                'ids' => [$consumable->id],
                'bulk_actions' => 'delete',
            ])
            ->assertOk()
            ->assertViewIs('consumables.bulk-delete');
    }

    public function test_update_writes_only_provided_fields(): void
    {
        $originalCategory = Category::factory()->consumablePaperCategory()->create();
        $originalLocation = Location::factory()->create();
        $newLocation = Location::factory()->create();

        $one = Consumable::factory()->create([
            'category_id' => $originalCategory->id,
            'location_id' => $originalLocation->id,
            'min_amt' => 5,
        ]);
        $two = Consumable::factory()->create([
            'category_id' => $originalCategory->id,
            'location_id' => $originalLocation->id,
            'min_amt' => 5,
        ]);

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('consumables.bulkedit.store'), [
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
            ->assertRedirect(route('consumables.index'))
            ->assertSessionHas('success');

        $this->assertSame($newLocation->id, $one->fresh()->location_id);
        $this->assertSame($newLocation->id, $two->fresh()->location_id);
        $this->assertSame($originalCategory->id, $one->fresh()->category_id);
        $this->assertSame(5, (int) $one->fresh()->min_amt);
    }

    public function test_update_can_set_archived_across_selection(): void
    {
        $one = Consumable::factory()->create();
        $two = Consumable::factory()->create();

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('consumables.bulkedit.store'), [
                'ids' => [$one->id, $two->id],
                'archived' => '1',
            ])
            ->assertRedirect(route('consumables.index'));

        $this->assertTrue((bool) $one->fresh()->archived);
        $this->assertTrue((bool) $two->fresh()->archived);
    }

    public function test_update_flashes_error_when_no_rows_updated_due_to_validation(): void
    {
        // Regression test: archive-ing consumables that all have
        // outstanding distributions should surface a model-validation
        // error, not a "0 consumables were updated" success flash.
        $one = Consumable::factory()->checkedOutToUser()->create();
        $two = Consumable::factory()->checkedOutToUser()->create();

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('consumables.bulkedit.store'), [
                'ids' => [$one->id, $two->id],
                'archived' => '1',
            ])
            ->assertRedirect(route('consumables.index'))
            ->assertSessionHas('error')
            ->assertSessionMissing('success')
            ->assertSessionHas('multi_error_messages');

        $this->assertFalse((bool) $one->fresh()->archived);
        $this->assertFalse((bool) $two->fresh()->archived);
    }

    public function test_update_flashes_warning_on_partial_validation_failure(): void
    {
        // One row saves, one is rejected by the outstanding-check.
        // Response should be warning + error details, not plain success.
        $saveable = Consumable::factory()->create();
        $blocked = Consumable::factory()->checkedOutToUser()->create();

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('consumables.bulkedit.store'), [
                'ids' => [$saveable->id, $blocked->id],
                'archived' => '1',
            ])
            ->assertRedirect(route('consumables.index'))
            ->assertSessionHas('warning')
            ->assertSessionMissing('success')
            ->assertSessionHas('multi_error_messages');

        $this->assertTrue((bool) $saveable->fresh()->archived);
        $this->assertFalse((bool) $blocked->fresh()->archived);
    }

    public function test_update_covers_full_classification_field_set(): void
    {
        $company = Company::factory()->create();
        $category = Category::factory()->consumablePaperCategory()->create();
        $manufacturer = Manufacturer::factory()->create();
        $location = Location::factory()->create();
        $supplier = Supplier::factory()->create();

        $consumable = Consumable::factory()->create(['min_amt' => 1]);

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('consumables.bulkedit.store'), [
                'ids' => [$consumable->id],
                'company_id' => (string) $company->id,
                'category_id' => (string) $category->id,
                'manufacturer_id' => (string) $manufacturer->id,
                'location_id' => (string) $location->id,
                'default_supplier_id' => (string) $supplier->id,
                'min_amt' => '42',
                'requestable' => '1',
                'archived' => '0',
            ])
            ->assertRedirect(route('consumables.index'));

        $fresh = $consumable->fresh();
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
