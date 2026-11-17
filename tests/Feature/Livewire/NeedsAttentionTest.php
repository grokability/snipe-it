<?php

namespace Tests\Feature\Livewire;

use App\Enums\CheckoutRequestState;
use App\Livewire\NeedsAttention;
use App\Models\Asset;
use App\Models\CheckoutRequest;
use App\Models\Company;
use App\Models\License;
use App\Models\Maintenance;
use App\Models\User;
use Livewire\Livewire;
use Tests\Concerns\TestsFullMultipleCompaniesSupport;
use Tests\Concerns\TestsPermissionsRequirement;
use Tests\TestCase;

/**
 * Coverage for the dashboard's Needs Attention Livewire widget.
 * Focuses on:
 *
 *   - boot() authorization: canViewUsersAndCheckoutables gate
 *     refuses users who can't view any HasCalendarEvents source so
 *     a snapshot-replay POST can't leak counts they'd never see in
 *     the parent view.
 *   - Placeholder shape: renders a skeleton with the same title +
 *     list-group chrome so hydration doesn't visibly shift the row.
 *   - Count accuracy: each of the eight counts reflects seeded data.
 *   - Per-type scoping: a caller with a narrow view grant (e.g.
 *     accessories.view only) gets zero for the counts covering
 *     types they can't see, instead of a leaked global count.
 *   - FMCS scoping: a company-scoped admin sees only the counts
 *     matching rows in their own company, not cross-company totals.
 */
class NeedsAttentionTest extends TestCase implements TestsFullMultipleCompaniesSupport, TestsPermissionsRequirement
{
    public function test_requires_permission(): void
    {
        // A user with zero view grants fails canViewUsersAndCheckoutables
        // and boot() aborts with 403. Livewire's test helper intercepts
        // HttpExceptions raised in boot() and surfaces them as test
        // failures rather than a response status, so call boot() directly.
        $this->actingAs(User::factory()->create());

        try {
            (new NeedsAttention)->boot();
            $this->fail('Expected boot() to abort with a 403.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }
    }

    public function test_boot_permits_users_who_can_view_any_source(): void
    {
        // A non-admin who can view at least one HasCalendarEvents
        // source (Asset here via viewAssets()) passes the widened
        // gate. boot() should return silently.
        $this->actingAs(User::factory()->viewAssets()->create());

        (new NeedsAttention)->boot();

        // Reaching this line means boot() didn't throw.
        $this->assertTrue(true);
    }

    public function test_boot_permits_admin(): void
    {
        // Superuser trivially passes canViewUsersAndCheckoutables.
        $this->actingAs(User::factory()->superuser()->create());

        (new NeedsAttention)->boot();

        $this->assertTrue(true);
    }

    public function test_counts_return_zero_for_types_the_caller_cannot_view(): void
    {
        // Seed data that would populate every asset / license /
        // maintenance count. A user with only accessories.view
        // should see zero for all of them, since none of those
        // seeds belong to a type they can view.
        $farFuture = now()->addYears(5)->format('Y-m-d');

        Asset::factory()->count(3)->create(['next_audit_date' => now()->subDays(5)])
            ->each(fn ($asset) => $asset->forceFill(['asset_eol_date' => $farFuture])->save());
        License::factory()->count(2)->create(['expiration_date' => now()->addDays(10)->format('Y-m-d')]);

        Livewire::withoutLazyLoading();
        Livewire::actingAs(User::factory()->viewAccessories()->create())
            ->test(NeedsAttention::class)
            ->assertSet('overdueAudits', 0)
            ->assertSet('assetsPastEol', 0)
            ->assertSet('licensesExpiringSoon', 0)
            ->assertSet('overdueMaintenances', 0)
            ->assertSet('usersOffboardingSoon', 0);
    }

    public function test_the_component_renders_for_admin(): void
    {
        Livewire::withoutLazyLoading();
        Livewire::actingAs(User::factory()->superuser()->create())
            ->test(NeedsAttention::class)
            ->assertStatus(200);
    }

    public function test_placeholder_reserves_widget_footprint(): void
    {
        $placeholder = (new NeedsAttention)->placeholder();

        // Placeholder shape mirrors the loaded view's box + list-group
        // markup so first-paint layout matches post-hydration layout
        // and the row doesn't visibly jump when the real counts land.
        $this->assertStringContainsString('box box-default', $placeholder);
        $this->assertStringContainsString('box-title', $placeholder);
        $this->assertStringContainsString('list-group', $placeholder);
    }

    public function test_counts_reflect_seeded_data(): void
    {
        // AssetFactory's configure() afterMaking hook has two sprinkles
        // that leak into these counts if the test doesn't override
        // them per-asset:
        //   1. next_audit_date is set on ~20% of assets, with a range
        //      that lands in the past ~14% of the time (2.8% total per
        //      asset). Would inflate overdueAudits.
        //   2. asset_eol_date is set unconditionally from purchase_date
        //      + model.eol and can land in the past. Would inflate
        //      assetsPastEol.
        // Every non-target asset gets both fields pinned to a far
        // future date to isolate each count to the seeds intended for
        // it. Target assets forceFill the specific past date they need
        // after creation so the hook's unconditional overwrite of
        // asset_eol_date doesn't undo the intent.
        $farFuture = now()->addYears(5)->format('Y-m-d');

        // 3 assets overdue for audit
        Asset::factory()->count(3)->create(['next_audit_date' => now()->subDays(5)])
            ->each(fn ($asset) => $asset->forceFill(['asset_eol_date' => $farFuture])->save());

        // 2 assets overdue for checkin
        Asset::factory()->count(2)->create([
            'expected_checkin' => now()->subDays(2),
            'assigned_to' => User::factory()->create()->id,
            'assigned_type' => User::class,
            'next_audit_date' => $farFuture,
        ])->each(fn ($asset) => $asset->forceFill(['asset_eol_date' => $farFuture])->save());

        // 4 assets past EOL
        Asset::factory()->count(4)->create(['next_audit_date' => $farFuture])
            ->each(fn ($asset) => $asset->forceFill(['asset_eol_date' => now()->subDays(10)])->save());

        License::factory()->count(2)->create([
            'expiration_date' => now()->addDays(10)->format('Y-m-d'),
        ]);

        // Maintenance's own Asset needs the same isolation - otherwise
        // it can accidentally satisfy overdueAudits or assetsPastEol.
        $maintenanceAsset = Asset::factory()->create(['next_audit_date' => $farFuture]);
        $maintenanceAsset->forceFill(['asset_eol_date' => $farFuture])->save();

        Maintenance::factory()->create([
            'item_id' => $maintenanceAsset->id,
            'item_type' => Asset::class,
            'start_date' => now()->subDays(20),
            'expected_completion_date' => now()->subDays(5),
            'completed_at' => null,
        ]);

        Livewire::withoutLazyLoading();
        Livewire::actingAs(User::factory()->superuser()->create())
            ->test(NeedsAttention::class)
            ->assertSet('overdueAudits', 3)
            ->assertSet('overdueCheckins', 2)
            ->assertSet('assetsPastEol', 4)
            ->assertSet('licensesExpiringSoon', 2)
            ->assertSet('overdueMaintenances', 1);
    }

    public function test_counts_ignore_completed_maintenances(): void
    {
        // A past-due maintenance that's been marked complete should
        // drop out of the overdue count; only the still-open one below
        // should register.
        $asset = Asset::factory()->create();

        Maintenance::factory()->create([
            'item_id' => $asset->id,
            'item_type' => Asset::class,
            'start_date' => now()->subDays(20),
            'expected_completion_date' => now()->subDays(5),
            'completed_at' => now()->subDays(1),
        ]);
        Maintenance::factory()->create([
            'item_id' => $asset->id,
            'item_type' => Asset::class,
            'start_date' => now()->subDays(20),
            'expected_completion_date' => now()->subDays(5),
            'completed_at' => null,
        ]);

        Livewire::withoutLazyLoading();
        Livewire::actingAs(User::factory()->superuser()->create())
            ->test(NeedsAttention::class)
            ->assertSet('overdueMaintenances', 1);
    }

    public function test_pending_checkout_requests_count_ignores_canceled_and_fulfilled(): void
    {
        $requestable = Asset::factory()->create();
        $requester = User::factory()->create();
        $base = [
            'requestable_id' => $requestable->id,
            'requestable_type' => Asset::class,
            'user_id' => $requester->id,
            'quantity' => 1,
        ];

        // CheckoutRequest's $fillable is narrow (user_id + quantity
        // only), so mass-assign the morphable fields via forceFill +
        // save. Matches how the application's Actions class writes
        // these rows. State is the source of truth for the widget
        // count now that the state machine landed - explicitly set
        // it here alongside the terminal datetime column so the row
        // shape matches what CancelCheckoutRequestAction and
        // FulfillCheckoutRequestAction persist.
        (new CheckoutRequest)->forceFill($base)->save();
        (new CheckoutRequest)->forceFill($base + [
            'canceled_at' => now(),
            'state' => CheckoutRequestState::Canceled,
        ])->save();
        (new CheckoutRequest)->forceFill($base + [
            'fulfilled_at' => now(),
            'state' => CheckoutRequestState::Fulfilled,
        ])->save();

        Livewire::withoutLazyLoading();
        Livewire::actingAs(User::factory()->superuser()->create())
            ->test(NeedsAttention::class)
            ->assertSet('pendingRequestsCount', 1);
    }

    public function test_users_offboarding_count_requires_at_least_one_assignment(): void
    {
        // Regression test for issue #19809. The tile is meant to flag
        // offboarding users who still hold items, so a leaver with
        // nothing on record shouldn't inflate the count. Narrowing to
        // users-with-assignments also matches the ?with_assignments=1
        // filter the tile forwards to users.index.
        //
        // Formatted as Y-m-d because the User model's $rules enforce
        // date_format:Y-m-d via Watson Validating. Passing a Carbon
        // directly fails validation silently and never persists.
        $withinWindow = now()->addDays(10)->format('Y-m-d');

        // No assignments at all. Shouldn't register.
        User::factory()->create(['end_date' => $withinWindow]);

        // One asset assigned. Should register.
        $leaverWithAsset = User::factory()->create(['end_date' => $withinWindow]);
        Asset::factory()->create([
            'assigned_to' => $leaverWithAsset->id,
            'assigned_type' => User::class,
        ]);

        Livewire::withoutLazyLoading();
        Livewire::actingAs(User::factory()->superuser()->create())
            ->test(NeedsAttention::class)
            ->assertSet('usersOffboardingSoon', 1);
    }

    public function test_users_offboarding_count_excludes_leavers_outside_window(): void
    {
        // Only end_dates within the 30-day window should count.
        $assetOwner = User::factory()->create(['end_date' => now()->addDays(60)->format('Y-m-d')]);
        Asset::factory()->create([
            'assigned_to' => $assetOwner->id,
            'assigned_type' => User::class,
        ]);

        Livewire::withoutLazyLoading();
        Livewire::actingAs(User::factory()->superuser()->create())
            ->test(NeedsAttention::class)
            ->assertSet('usersOffboardingSoon', 0);
    }

    public function test_adheres_to_full_multiple_companies_support_scoping()
    {
        [$companyA, $companyB] = Company::factory()->count(2)->create();

        // 3 overdue-audit assets in Company A, 5 in Company B.
        Asset::factory()->count(3)->for($companyA)->create(['next_audit_date' => now()->subDays(5)]);
        Asset::factory()->count(5)->for($companyB)->create(['next_audit_date' => now()->subDays(5)]);

        // 2 expiring licenses in each company.
        License::factory()->count(2)->for($companyA)->create([
            'expiration_date' => now()->addDays(10)->format('Y-m-d'),
        ]);
        License::factory()->count(2)->for($companyB)->create([
            'expiration_date' => now()->addDays(10)->format('Y-m-d'),
        ]);

        $adminInCompanyA = $companyA->users()->save(User::factory()->admin()->make());

        $this->settings->enableMultipleFullCompanySupport();

        Livewire::withoutLazyLoading();
        Livewire::actingAs($adminInCompanyA)
            ->test(NeedsAttention::class)
            ->assertSet('overdueAudits', 3)
            ->assertSet('licensesExpiringSoon', 2);
    }
}
