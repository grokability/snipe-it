<?php

namespace Tests\Feature\Consumables\Ui;

use App\Models\Company;
use App\Models\Consumable;
use App\Models\ConsumableAssignment;
use App\Models\Location;
use App\Models\User;
use Tests\TestCase;

/**
 * Adversarial cross-company tests for every bulk endpoint added or
 * touched in the consumables bulk + archive + mark-consumed slice.
 * Mirrors BulkAccessoriesFmcsScopingTest. The controllers route
 * through auto-scoped Consumable queries, so cross-company ids
 * should never reach the per-row handlers. Pin the behavior anyway
 * because any future refactor that bypasses the CompanyableScope
 * would otherwise silently open a cross-tenant hole.
 */
class BulkConsumablesFmcsScopingTest extends TestCase
{
    public function test_bulk_delete_cannot_delete_cross_company_consumable_under_fmcs(): void
    {
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();
        $actor = $companyA->users()->save(User::factory()->editConsumables()->deleteConsumables()->viewConsumables()->make());
        $target = Consumable::factory()->for($companyB)->create();

        $this->actingAs($actor)
            ->post(route('consumables.bulkdelete.store'), ['ids' => [$target->id]]);

        $this->assertNull($target->fresh()->deleted_at);
    }

    public function test_bulk_edit_dispatcher_does_not_leak_cross_company_consumable_under_fmcs(): void
    {
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();
        $actor = $companyA->users()->save(User::factory()->viewConsumables()->editConsumables()->checkinConsumables()->make());

        $visible = Consumable::factory()->for($companyA)->create(['name' => 'Visible']);
        $hidden = Consumable::factory()->for($companyB)->create(['name' => 'Hidden']);

        $response = $this->actingAs($actor)
            ->post(route('consumables.bulkedit.show'), [
                'ids' => [$visible->id, $hidden->id],
                'bulk_actions' => 'edit',
            ])
            ->assertOk()
            ->assertViewIs('consumables.bulk-edit');

        $consumables = $response->viewData('consumables');
        $this->assertTrue($consumables->contains('id', $visible->id));
        $this->assertFalse($consumables->contains('id', $hidden->id));
    }

    public function test_bulk_update_cannot_write_cross_company_consumable_under_fmcs(): void
    {
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();
        $actor = $companyA->users()->save(User::factory()->editConsumables()->viewConsumables()->make());

        $target = Consumable::factory()->for($companyB)->create();
        $originalName = $target->name;

        $this->actingAs($actor)
            ->post(route('consumables.bulkedit.store'), [
                'ids' => [$target->id],
                'archived' => '1',
            ]);

        $fresh = $target->fresh();
        $this->assertSame($originalName, $fresh->name);
        $this->assertFalse((bool) $fresh->archived);
    }

    public function test_bulk_edit_rejects_write_to_cross_company_company_id_under_fmcs(): void
    {
        // Even if a non-superuser hand-crafted a POST with a company_id
        // outside their pivot set, the fmcs_company validator should
        // block the write.
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();
        $actor = $companyA->users()->save(User::factory()->editConsumables()->viewConsumables()->make());
        $target = Consumable::factory()->for($companyA)->create();

        $this->actingAs($actor)
            ->post(route('consumables.bulkedit.store'), [
                'ids' => [$target->id],
                'company_id' => (string) $companyB->id,
            ]);

        $this->assertSame($companyA->id, $target->fresh()->company_id);
    }

    public function test_bulk_edit_rejects_cross_company_location_under_scope_locations_fmcs(): void
    {
        // scope_locations_fmcs=1 means a location "belongs" to the
        // companies in its parent chain. fmcs_location validator must
        // reject a bulk-edit that pairs location X with company Y when
        // X doesn't live in Y.
        [$companyA, $companyB] = Company::factory()->count(2)->create();
        $actor = $companyA->users()->save(User::factory()->editConsumables()->viewConsumables()->make());
        $homeLocation = Location::factory()->for($companyA)->create();
        $target = Consumable::factory()->for($companyA)->create(['location_id' => $homeLocation->id]);
        $foreignLocation = Location::factory()->for($companyB)->create();

        $this->settings->enableScopedLocationsWithFullMultipleCompanySupport();

        $this->actingAs($actor)
            ->post(route('consumables.bulkedit.store'), [
                'ids' => [$target->id],
                'location_id' => (string) $foreignLocation->id,
            ]);

        $this->assertSame($homeLocation->id, $target->fresh()->location_id);
    }

    public function test_single_consume_cannot_touch_cross_company_assignment_under_fmcs(): void
    {
        // Mark-consumed is gated on consumables.checkout plus the
        // $consumable route-model binding, which runs through the
        // CompanyableScope. A company A actor should not be able to
        // close out a pivot row attached to a company B consumable.
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();
        $actor = $companyA->users()->save(User::factory()->viewConsumables()->checkoutConsumables()->make());

        $foreignConsumable = Consumable::factory()->for($companyB)->checkedOutToUser()->create();
        $foreignAssignment = ConsumableAssignment::where('consumable_id', $foreignConsumable->id)->firstOrFail();

        $this->actingAs($actor)
            ->post(route('consumables.consume', [
                'consumable' => $foreignConsumable->id,
                'assignment' => $foreignAssignment->id,
            ]));

        $this->assertNull($foreignAssignment->fresh()->consumed_at);
    }

    public function test_bulk_consume_cannot_mark_cross_company_assignment_under_fmcs(): void
    {
        // bulkConsumeStore resolves the parent consumable from the
        // ConsumableAssignment lookup, then authorizes 'checkout' on
        // it. A company A actor targeting company B assignments
        // should fail the authorize step.
        $this->settings->enableMultipleFullCompanySupport();
        $this->settings->disableFloaterMode();

        [$companyA, $companyB] = Company::factory()->count(2)->create();
        $actor = $companyA->users()->save(User::factory()->viewConsumables()->checkoutConsumables()->make());

        $foreignConsumable = Consumable::factory()->for($companyB)->checkedOutToUser()->create();
        $foreignAssignment = ConsumableAssignment::where('consumable_id', $foreignConsumable->id)->firstOrFail();

        $this->actingAs($actor)
            ->post(route('consumables.bulkconsume.store'), [
                'ids' => [$foreignAssignment->id],
            ])
            ->assertForbidden();

        $this->assertNull($foreignAssignment->fresh()->consumed_at);
    }

    public function test_bulk_consume_show_blocks_cross_company_assignment_under_fmcs(): void
    {
        // The interstitial view runs through the same auth gate as the
        // commit step. A company A actor hand-posting a company B
        // assignment id should either redirect with a warning or be
        // forbidden outright. The pivot row must not surface in the
        // confirmation view either way.
        $this->settings->enableMultipleFullCompanySupport();
        $this->settings->disableFloaterMode();

        [$companyA, $companyB] = Company::factory()->count(2)->create();
        $actor = $companyA->users()->save(User::factory()->viewConsumables()->checkoutConsumables()->make());

        $foreignConsumable = Consumable::factory()->for($companyB)->checkedOutToUser()->create();
        $foreignAssignment = ConsumableAssignment::where('consumable_id', $foreignConsumable->id)->firstOrFail();

        $this->actingAs($actor)
            ->post(route('consumables.bulkconsume.show'), [
                'ids' => [$foreignAssignment->id],
            ])
            ->assertForbidden();

        $this->assertNull($foreignAssignment->fresh()->consumed_at);
    }

    public function test_bulk_consume_floater_can_consume_null_company_assignment(): void
    {
        // Floater ON: company A actor is allowed to touch null-company
        // (floater) consumables. Pin so a future refactor that over-
        // tightens the subquery does not block a legitimate flow.
        $this->settings->enableFloaterMode();

        $companyA = Company::factory()->create();
        $actor = $companyA->users()->save(User::factory()->viewConsumables()->checkoutConsumables()->make());

        $floater = Consumable::factory()->create(['company_id' => null]);
        $floater->users()->attach(User::factory()->create(), [
            'consumable_id' => $floater->id,
            'created_by' => $actor->id,
            'created_at' => now(),
        ]);
        $assignment = ConsumableAssignment::where('consumable_id', $floater->id)->firstOrFail();

        $this->actingAs($actor)
            ->post(route('consumables.bulkconsume.store'), [
                'ids' => [$assignment->id],
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertNotNull($assignment->fresh()->consumed_at);
    }

    public function test_unarchive_cannot_touch_cross_company_consumable_under_fmcs(): void
    {
        // postUnarchive relies on route-model binding (which runs the
        // Consumable CompanyableScope) + authorize('update', $consumable).
        // A cross-tenant target is either scoped out at binding time or
        // forbidden by the policy. Either way the archived bit must
        // not flip.
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();
        $actor = $companyA->users()->save(User::factory()->editConsumables()->viewConsumables()->make());
        $foreign = Consumable::factory()->for($companyB)->create(['archived' => true]);

        $this->actingAs($actor)
            ->post(route('consumables.unarchive', $foreign));

        $this->assertTrue((bool) $foreign->fresh()->archived);
    }
}
