<?php

namespace Tests\Feature\Consumables\Ui;

use App\Models\Company;
use App\Models\Consumable;
use App\Models\User;
use Tests\TestCase;

class BulkDeleteConsumablesTest extends TestCase
{
    public function test_requires_permission(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('consumables.bulkdelete.store'), [
                'ids' => [1, 2, 3],
            ])
            ->assertForbidden();
    }

    public function test_consumable_with_outstanding_distribution_is_not_bulk_deleted(): void
    {
        $consumable = Consumable::factory()->checkedOutToUser()->create();

        $this->actingAs(User::factory()->deleteConsumables()->create())
            ->post(route('consumables.bulkdelete.store'), [
                'ids' => [$consumable->id],
            ])
            ->assertSessionHas('multi_error_messages');

        $this->assertDatabaseHas('consumables', ['id' => $consumable->id, 'deleted_at' => null]);
    }

    public function test_deletable_consumables_are_bulk_deleted(): void
    {
        $one = Consumable::factory()->create();
        $two = Consumable::factory()->create();
        $three = Consumable::factory()->create();

        $this->actingAs(User::factory()->deleteConsumables()->create())
            ->post(route('consumables.bulkdelete.store'), [
                'ids' => [$one->id, $two->id, $three->id],
            ])
            ->assertRedirect(route('consumables.index'));

        $this->assertSoftDeleted('consumables', ['id' => $one->id]);
        $this->assertSoftDeleted('consumables', ['id' => $two->id]);
        $this->assertSoftDeleted('consumables', ['id' => $three->id]);
    }

    public function test_partial_success_deletes_the_clean_ones_and_reports_the_rest(): void
    {
        $deletable = Consumable::factory()->create();
        $blocked = Consumable::factory()->checkedOutToUser()->create();

        $this->actingAs(User::factory()->deleteConsumables()->create())
            ->post(route('consumables.bulkdelete.store'), [
                'ids' => [$deletable->id, $blocked->id],
            ])
            ->assertRedirect(route('consumables.index'))
            ->assertSessionHas('success')
            ->assertSessionHas('multi_error_messages');

        $this->assertSoftDeleted('consumables', ['id' => $deletable->id]);
        $this->assertDatabaseHas('consumables', ['id' => $blocked->id, 'deleted_at' => null]);
    }

    public function test_nonexistent_ids_are_reported_and_do_not_break_the_batch(): void
    {
        $deletable = Consumable::factory()->create();

        $this->actingAs(User::factory()->deleteConsumables()->create())
            ->post(route('consumables.bulkdelete.store'), [
                'ids' => [$deletable->id, 999999],
            ])
            ->assertRedirect(route('consumables.index'))
            ->assertSessionHas('multi_error_messages');

        $this->assertSoftDeleted('consumables', ['id' => $deletable->id]);
    }

    public function test_respects_fmcs_scoping_for_non_superuser(): void
    {
        // Caller scoped to company A asking to bulk delete [A-owned,
        // B-owned] sees the B-owned row hidden by CompanyableScope
        // (Consumable::find() returns null), falling into the
        // does_not_exist bucket. A-owned deletes normally, so the
        // request is a partial success.
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();
        $consumableA = Consumable::factory()->for($companyA)->create();
        $consumableB = Consumable::factory()->for($companyB)->create();
        $userA = User::factory()->forCompany($companyA)->deleteConsumables()->create();

        $this->actingAs($userA)
            ->post(route('consumables.bulkdelete.store'), [
                'ids' => [$consumableA->id, $consumableB->id],
            ])
            ->assertRedirect(route('consumables.index'))
            ->assertSessionHas('success')
            ->assertSessionHas('multi_error_messages');

        $this->assertSoftDeleted('consumables', ['id' => $consumableA->id]);
        $this->assertDatabaseHas('consumables', ['id' => $consumableB->id, 'deleted_at' => null]);
    }

    public function test_superuser_bypasses_fmcs_and_deletes_consumables_in_any_company(): void
    {
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();
        $consumableA = Consumable::factory()->for($companyA)->create();
        $consumableB = Consumable::factory()->for($companyB)->create();

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('consumables.bulkdelete.store'), [
                'ids' => [$consumableA->id, $consumableB->id],
            ])
            ->assertRedirect(route('consumables.index'))
            ->assertSessionHas('success')
            ->assertSessionMissing('multi_error_messages');

        $this->assertSoftDeleted('consumables', ['id' => $consumableA->id]);
        $this->assertSoftDeleted('consumables', ['id' => $consumableB->id]);
    }

    public function test_bulk_success_message_pluralizes_by_count(): void
    {
        $solo = Consumable::factory()->create();

        $this->actingAs(User::factory()->deleteConsumables()->create())
            ->post(route('consumables.bulkdelete.store'), [
                'ids' => [$solo->id],
            ])
            ->assertSessionHas('success', trans_choice('admin/consumables/message.delete.bulk_success', 1, ['count' => 1]));

        $a = Consumable::factory()->create();
        $b = Consumable::factory()->create();
        $c = Consumable::factory()->create();

        $this->actingAs(User::factory()->deleteConsumables()->create())
            ->post(route('consumables.bulkdelete.store'), [
                'ids' => [$a->id, $b->id, $c->id],
            ])
            ->assertSessionHas('success', trans_choice('admin/consumables/message.delete.bulk_success', 3, ['count' => 3]));
    }
}
