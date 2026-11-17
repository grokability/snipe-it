<?php

namespace Tests\Feature\Components\Ui;

use App\Events\CheckoutableCheckedIn;
use App\Models\Asset;
use App\Models\Company;
use App\Models\Component;
use App\Models\ComponentAssignment;
use App\Models\Location;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

/**
 * Adversarial cross-company tests for every bulk endpoint added in
 * this feature slice. These are belt-and-braces: the controllers
 * route through auto-scoped Component queries, so cross-company ids
 * should never reach the per-row handlers. Pin the behavior anyway
 * because any future refactor that bypasses the CompanyableScope
 * (e.g. switching to a raw DB query) would otherwise silently open
 * a cross-tenant hole.
 */
class BulkComponentsFmcsScopingTest extends TestCase
{
    private function pivotId(Component $component, Asset $asset): int
    {
        $component->assets()->attach($asset->id, [
            'assigned_qty' => 1,
            'created_at' => now(),
            'created_by' => 1,
        ]);

        return (int) ComponentAssignment::where('component_id', $component->id)
            ->where('asset_id', $asset->id)
            ->latest('id')
            ->value('id');
    }

    public function test_bulk_delete_cannot_delete_cross_company_component_under_fmcs(): void
    {
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();
        $actor = $companyA->users()->save(User::factory()->editComponents()->deleteComponents()->viewComponents()->make());
        $target = Component::factory()->for($companyB)->create();

        $this->actingAs($actor)
            ->post(route('components.bulkdelete.store'), ['ids' => [$target->id]])
            ->assertRedirect(route('components.index'));

        $this->assertNull($target->fresh()->deleted_at);
    }

    public function test_bulk_edit_dispatcher_does_not_leak_cross_company_component_under_fmcs(): void
    {
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();
        $actor = $companyA->users()->save(User::factory()->viewComponents()->editComponents()->checkinComponents()->make());

        $visible = Component::factory()->for($companyA)->create(['name' => 'Visible']);
        $hidden = Component::factory()->for($companyB)->create(['name' => 'Hidden']);

        $response = $this->actingAs($actor)
            ->post(route('components.bulkedit.show'), [
                'ids' => [$visible->id, $hidden->id],
                'bulk_actions' => 'edit',
            ])
            ->assertOk()
            ->assertViewIs('components.bulk-edit');

        $components = $response->viewData('components');
        $this->assertTrue($components->contains('id', $visible->id));
        $this->assertFalse($components->contains('id', $hidden->id));
    }

    public function test_bulk_update_cannot_write_cross_company_component_under_fmcs(): void
    {
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();
        $actor = $companyA->users()->save(User::factory()->editComponents()->viewComponents()->make());

        $target = Component::factory()->for($companyB)->create();
        $originalName = $target->name;

        $this->actingAs($actor)
            ->post(route('components.bulkedit.store'), [
                'ids' => [$target->id],
                'archived' => '1',
            ])
            ->assertRedirect(route('components.index'));

        $fresh = $target->fresh();
        $this->assertSame($originalName, $fresh->name);
        $this->assertFalse((bool) $fresh->archived);
    }

    public function test_bulk_checkin_cannot_check_in_cross_company_pivot_under_fmcs(): void
    {
        // Floater off: company A actor should not be able to check in
        // pivots whose component belongs to company B, even if they
        // post the pivot id by hand (the row isn't in their table).
        Event::fake([CheckoutableCheckedIn::class]);

        $this->settings->enableMultipleFullCompanySupport();
        $this->settings->disableFloaterMode();

        [$companyA, $companyB] = Company::factory()->count(2)->create();
        $actor = $companyA->users()->save(User::factory()->viewComponents()->editComponents()->checkinComponents()->make());

        $foreignComponent = Component::factory()->for($companyB)->create(['qty' => 5]);
        $pivot = $this->pivotId($foreignComponent, Asset::factory()->create());

        $this->actingAs($actor)
            ->post(route('components.bulkcheckin.store'), ['ids' => [$pivot]])
            ->assertRedirect();

        $this->assertDatabaseHas('components_assets', ['id' => $pivot]);
        Event::assertNothingDispatched();
    }

    public function test_bulk_checkin_show_does_not_leak_cross_company_pivot_under_fmcs(): void
    {
        // The interstitial view must also filter: posting a cross-tenant
        // id to .show should redirect with warning (empty after scoping)
        // instead of rendering the foreign pivot in the confirmation UI.
        $this->settings->enableMultipleFullCompanySupport();
        $this->settings->disableFloaterMode();

        [$companyA, $companyB] = Company::factory()->count(2)->create();
        $actor = $companyA->users()->save(User::factory()->viewComponents()->checkinComponents()->make());

        $foreignComponent = Component::factory()->for($companyB)->create(['qty' => 5]);
        $pivot = $this->pivotId($foreignComponent, Asset::factory()->create());

        $this->actingAs($actor)
            ->post(route('components.bulkcheckin.show'), ['ids' => [$pivot]])
            ->assertRedirect()
            ->assertSessionHas('warning');
    }

    public function test_bulk_edit_rejects_write_to_cross_company_company_id_under_fmcs(): void
    {
        // Even if a non-superuser hand-crafted a POST with a company_id
        // outside their pivot set, the fmcs_company validator + policy
        // layer should block the write. Pin so the picker-level
        // filtering can't be the only defense.
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();
        $actor = $companyA->users()->save(User::factory()->editComponents()->viewComponents()->make());
        $target = Component::factory()->for($companyA)->create();

        $this->actingAs($actor)
            ->post(route('components.bulkedit.store'), [
                'ids' => [$target->id],
                'company_id' => (string) $companyB->id,
            ])
            ->assertRedirect(route('components.index'));

        $this->assertSame($companyA->id, $target->fresh()->company_id);
    }

    public function test_bulk_edit_rejects_cross_company_location_under_scope_locations_fmcs(): void
    {
        // scope_locations_fmcs=1 means a location "belongs" to the
        // companies in its parent chain. fmcs_location validator must
        // reject a bulk-edit that pairs location X with company Y when
        // X doesn't live in Y.
        [$companyA, $companyB] = Company::factory()->count(2)->create();
        $actor = $companyA->users()->save(User::factory()->editComponents()->viewComponents()->make());
        $homeLocation = Location::factory()->for($companyA)->create();
        $target = Component::factory()->for($companyA)->create(['location_id' => $homeLocation->id]);
        $foreignLocation = Location::factory()->for($companyB)->create();

        // Flip scope_locations_fmcs AFTER factory setup so cross-company
        // location-vs-company validation is in force only for the actual
        // bulk-update request, not during the test's own data seeding.
        $this->settings->enableScopedLocationsWithFullMultipleCompanySupport();

        $this->actingAs($actor)
            ->post(route('components.bulkedit.store'), [
                'ids' => [$target->id],
                'location_id' => (string) $foreignLocation->id,
            ])
            ->assertRedirect(route('components.index'));

        $this->assertSame($homeLocation->id, $target->fresh()->location_id);
    }

    public function test_bulk_checkin_floater_can_checkin_null_company_pivot(): void
    {
        // Floater ON: company A actor is allowed to touch null-company
        // (floater) components. Pin so a future refactor that over-
        // tightens the subquery doesn't block a legitimate flow.
        Event::fake([CheckoutableCheckedIn::class]);

        $this->settings->enableFloaterMode();

        $companyA = Company::factory()->create();
        $actor = $companyA->users()->save(User::factory()->viewComponents()->editComponents()->checkinComponents()->make());

        $floater = Component::factory()->create(['company_id' => null, 'qty' => 5]);
        $pivot = $this->pivotId($floater, Asset::factory()->create());

        $this->actingAs($actor)
            ->post(route('components.bulkcheckin.store'), ['ids' => [$pivot]])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('components_assets', ['id' => $pivot]);
        Event::assertDispatched(CheckoutableCheckedIn::class);
    }
}
