<?php

namespace Tests\Feature\Console;

use App\Mail\AcceptanceReRequestMail;
use App\Models\Accessory;
use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Category;
use App\Models\CheckoutAcceptance;
use App\Models\Company;
use App\Models\Component;
use App\Models\Consumable;
use App\Models\License;
use App\Models\LicenseSeat;
use App\Models\Location;
use App\Models\User;
use Database\Factories\CheckoutAcceptanceFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use Illuminate\Testing\PendingCommand;
use Tests\TestCase;

class RegenerateAcceptancesTest extends TestCase
{
    public function test_asset_assigned_directly_to_a_user_is_a_candidate(): void
    {
        $holder = User::factory()->create();
        $asset = $this->assetIn($this->acceptanceCategory('asset'), [
            'assigned_to' => $holder->id,
            'assigned_type' => User::class,
        ]);

        $this->artisan('snipeit:regenerate-acceptances', ['--no-interaction' => true])
            ->expectsTable(
                ['User', 'Item', 'Type', 'Units held', 'Qty'],
                [[$holder->present()->fullName, $asset->present()->name, 'Asset', 1, 1]],
            )
            ->assertExitCode(0);
    }

    public function test_asset_assigned_to_another_asset_resolves_to_that_assets_holder(): void
    {
        $holder = User::factory()->create();
        $laptop = $this->assetIn($this->acceptanceCategory('asset'), [
            'assigned_to' => $holder->id,
            'assigned_type' => User::class,
        ]);
        $dock = $this->assetIn($this->acceptanceCategory('asset'), [
            'assigned_to' => $laptop->id,
            'assigned_type' => Asset::class,
        ]);

        $this->artisan('snipeit:regenerate-acceptances', ['--no-interaction' => true])
            ->expectsOutputToContain($dock->present()->name)
            ->assertExitCode(0);
    }

    public function test_asset_assigned_to_an_unassigned_asset_is_not_a_candidate(): void
    {
        $parked = $this->assetIn($this->acceptanceCategory('asset'));
        $this->assetIn($this->acceptanceCategory('asset'), [
            'assigned_to' => $parked->id,
            'assigned_type' => Asset::class,
        ]);

        $this->artisan('snipeit:regenerate-acceptances', ['--no-interaction' => true])
            ->expectsOutput('No users currently hold items requiring acceptance in that scope.')
            ->assertExitCode(0);
    }

    public function test_asset_assigned_to_a_location_is_not_a_candidate(): void
    {
        $this->assetIn($this->acceptanceCategory('asset'), [
            'assigned_to' => Location::factory()->create()->id,
            'assigned_type' => Location::class,
        ]);

        $this->artisan('snipeit:regenerate-acceptances', ['--no-interaction' => true])
            ->expectsOutput('No users currently hold items requiring acceptance in that scope.')
            ->assertExitCode(0);
    }

    public function test_asset_in_a_category_not_requiring_acceptance_is_not_a_candidate(): void
    {
        $category = Category::factory()->create([
            'category_type' => 'asset',
            'require_acceptance' => false,
        ]);

        $this->assetIn($category, [
            'assigned_to' => User::factory()->create()->id,
            'assigned_type' => User::class,
        ]);

        $this->artisan('snipeit:regenerate-acceptances', ['--no-interaction' => true])
            ->expectsOutput('No users currently hold items requiring acceptance in that scope.')
            ->assertExitCode(0);
    }

    /**
     * `Asset::category()` is a hasOneThrough, so Laravel excludes assets whose AssetModel
     * is soft-deleted. A live checkout asks `requireAcceptance()`, which reads
     * `$this->model->category` through a `withTrashed()` belongsTo and creates the
     * acceptance row anyway — so the builder must reach the category the same way or it
     * regenerates a smaller set than a checkout creates.
     */
    public function test_asset_whose_model_is_soft_deleted_is_still_a_candidate(): void
    {
        $holder = User::factory()->create();
        $model = AssetModel::factory()->create(['category_id' => $this->acceptanceCategory('asset')->id]);
        $asset = Asset::factory()->create([
            'model_id' => $model->id,
            'assigned_to' => $holder->id,
            'assigned_type' => User::class,
        ]);

        $model->delete();

        $this->assertTrue((bool) $asset->fresh()->requireAcceptance());

        $this->artisan('snipeit:regenerate-acceptances', ['--no-interaction' => true])
            ->expectsTable(
                ['User', 'Item', 'Type', 'Units held', 'Qty'],
                [[$holder->present()->fullName, $asset->present()->name, 'Asset', 1, 1]],
            )
            ->assertExitCode(0);
    }

    public function test_soft_deleted_holders_are_not_candidates(): void
    {
        $holder = User::factory()->create();
        $this->assetIn($this->acceptanceCategory('asset'), [
            'assigned_to' => $holder->id,
            'assigned_type' => User::class,
        ]);
        $holder->delete();

        $this->artisan('snipeit:regenerate-acceptances', ['--no-interaction' => true])
            ->expectsOutput('No users currently hold items requiring acceptance in that scope.')
            ->assertExitCode(0);
    }

    public function test_license_seat_attached_only_to_an_asset_resolves_to_the_assets_holder(): void
    {
        $holder = User::factory()->create();
        $laptop = $this->assetIn($this->acceptanceCategory('asset'), [
            'assigned_to' => $holder->id,
            'assigned_type' => User::class,
        ]);
        $license = License::factory()->create(['category_id' => $this->acceptanceCategory('license')->id]);
        LicenseSeat::factory()->create([
            'license_id' => $license->id,
            'asset_id' => $laptop->id,
            'assigned_to' => null,
        ]);

        $this->artisan('snipeit:regenerate-acceptances', ['--no-interaction' => true])
            ->expectsOutputToContain($holder->present()->fullName)
            ->assertExitCode(0);
    }

    public function test_license_seat_carrying_both_columns_yields_one_pair(): void
    {
        $holder = User::factory()->create();
        $carrier = Category::factory()->create(['category_type' => 'asset', 'require_acceptance' => false]);
        $laptop = $this->assetIn($carrier, [
            'assigned_to' => $holder->id,
            'assigned_type' => User::class,
        ]);
        $license = License::factory()->create(['category_id' => $this->acceptanceCategory('license')->id]);
        LicenseSeat::factory()->create([
            'license_id' => $license->id,
            'asset_id' => $laptop->id,
            'assigned_to' => $holder->id,
        ]);

        $this->artisan('snipeit:regenerate-acceptances', ['--no-interaction' => true])
            ->expectsOutputToContain('To re-request: 1.')
            ->assertExitCode(0);
    }

    public function test_license_seat_keys_on_the_assets_current_holder_not_a_stale_assigned_to(): void
    {
        $currentHolder = User::factory()->create();
        $staleHolder = User::factory()->create();
        $laptop = $this->assetIn($this->acceptanceCategory('asset'), [
            'assigned_to' => $currentHolder->id,
            'assigned_type' => User::class,
        ]);
        $license = License::factory()->create(['category_id' => $this->acceptanceCategory('license')->id]);
        LicenseSeat::factory()->create([
            'license_id' => $license->id,
            'asset_id' => $laptop->id,
            'assigned_to' => $staleHolder->id,
        ]);

        $this->artisan('snipeit:regenerate-acceptances', ['--no-interaction' => true])
            ->expectsOutputToContain($currentHolder->present()->fullName)
            ->doesntExpectOutputToContain($staleHolder->present()->fullName)
            ->assertExitCode(0);
    }

    public function test_license_seat_assigned_directly_to_a_user_is_a_candidate(): void
    {
        $holder = User::factory()->create();
        $license = License::factory()->create(['category_id' => $this->acceptanceCategory('license')->id]);
        LicenseSeat::factory()->create([
            'license_id' => $license->id,
            'asset_id' => null,
            'assigned_to' => $holder->id,
        ]);

        $this->artisan('snipeit:regenerate-acceptances', ['--no-interaction' => true])
            ->expectsOutputToContain($holder->present()->fullName)
            ->assertExitCode(0);
    }

    public function test_accessory_units_are_counted_per_pivot_row(): void
    {
        $holder = User::factory()->create();
        $accessory = Accessory::factory()->create(['category_id' => $this->acceptanceCategory('accessory')->id]);
        $accessory->checkouts()->createMany([
            ['assigned_to' => $holder->id, 'assigned_type' => User::class],
            ['assigned_to' => $holder->id, 'assigned_type' => User::class],
        ]);

        $this->artisan('snipeit:regenerate-acceptances', ['--no-interaction' => true])
            ->expectsTable(
                ['User', 'Item', 'Type', 'Units held', 'Qty'],
                [[$holder->present()->fullName, $accessory->present()->name, 'Accessory', 2, 2]],
            )
            ->assertExitCode(0);
    }

    public function test_accessory_checked_out_to_an_asset_resolves_to_that_assets_holder(): void
    {
        $holder = User::factory()->create();
        $laptop = $this->assetIn($this->acceptanceCategory('asset'), [
            'assigned_to' => $holder->id,
            'assigned_type' => User::class,
        ]);
        $accessory = Accessory::factory()->create(['category_id' => $this->acceptanceCategory('accessory')->id]);
        $accessory->checkouts()->create(['assigned_to' => $laptop->id, 'assigned_type' => Asset::class]);

        $this->artisan('snipeit:regenerate-acceptances', ['--no-interaction' => true])
            ->expectsOutputToContain($accessory->present()->name)
            ->assertExitCode(0);
    }

    public function test_accessory_checked_out_to_a_location_is_not_a_candidate(): void
    {
        $accessory = Accessory::factory()->create(['category_id' => $this->acceptanceCategory('accessory')->id]);
        $accessory->checkouts()->create([
            'assigned_to' => Location::factory()->create()->id,
            'assigned_type' => Location::class,
        ]);

        $this->artisan('snipeit:regenerate-acceptances', ['--no-interaction' => true])
            ->expectsOutput('No users currently hold items requiring acceptance in that scope.')
            ->assertExitCode(0);
    }

    public function test_consumable_units_are_counted_per_pivot_row(): void
    {
        $holder = User::factory()->create();
        $consumable = Consumable::factory()->create(['category_id' => $this->acceptanceCategory('consumable')->id]);
        $consumable->users()->attach([$holder->id, $holder->id], ['created_by' => $holder->id]);

        $this->artisan('snipeit:regenerate-acceptances', ['--no-interaction' => true])
            ->expectsTable(
                ['User', 'Item', 'Type', 'Units held', 'Qty'],
                [[$holder->present()->fullName, $consumable->present()->name, 'Consumable', 2, 2]],
            )
            ->assertExitCode(0);
    }

    public function test_component_units_sum_assigned_qty_across_the_users_assets(): void
    {
        $holder = User::factory()->create();
        $category = $this->acceptanceCategory('asset');
        $laptop = $this->assetIn($category, ['assigned_to' => $holder->id, 'assigned_type' => User::class]);
        $desktop = $this->assetIn($category, ['assigned_to' => $holder->id, 'assigned_type' => User::class]);

        $component = Component::factory()->create(['category_id' => $this->acceptanceCategory('component')->id]);
        $component->assets()->attach($laptop->id, ['assigned_qty' => 3, 'created_by' => $holder->id]);
        $component->assets()->attach($desktop->id, ['assigned_qty' => 3, 'created_by' => $holder->id]);

        $this->artisan('snipeit:regenerate-acceptances', ['--no-interaction' => true])
            ->expectsTable(
                ['User', 'Item', 'Type', 'Units held', 'Qty'],
                [
                    [$holder->present()->fullName, $laptop->present()->name, 'Asset', 1, 1],
                    [$holder->present()->fullName, $desktop->present()->name, 'Asset', 1, 1],
                    [$holder->present()->fullName, $component->present()->name, 'Component', 6, 6],
                ],
            )
            ->assertExitCode(0);
    }

    public function test_component_on_an_unassigned_asset_is_not_a_candidate(): void
    {
        $parked = $this->assetIn($this->acceptanceCategory('asset'));
        $component = Component::factory()->create(['category_id' => $this->acceptanceCategory('component')->id]);
        $component->assets()->attach($parked->id, ['assigned_qty' => 2, 'created_by' => 1]);

        $this->artisan('snipeit:regenerate-acceptances', ['--no-interaction' => true])
            ->expectsOutput('No users currently hold items requiring acceptance in that scope.')
            ->assertExitCode(0);
    }

    /**
     * Each builder names its own category path — `model.category` for assets,
     * `license.category` for seats, `category` for the other three — and its own
     * company column, so both filters are pinned per type rather than once.
     */
    public function test_assets_can_be_filtered_by_category(): void
    {
        $holder = User::factory()->create();
        $wanted = $this->acceptanceCategory('asset');

        $this->heldAsset($holder, $wanted, Company::factory()->create(), 'In scope asset');
        $this->heldAsset($holder, $this->acceptanceCategory('asset'), Company::factory()->create(), 'Out of scope asset');

        $this->artisan('snipeit:regenerate-acceptances', ['--no-interaction' => true, '--category' => [$wanted->id]])
            ->expectsOutputToContain('In scope asset')
            ->expectsOutput('To re-request: 1.')
            ->assertExitCode(0);
    }

    public function test_assets_can_be_filtered_by_company(): void
    {
        $holder = User::factory()->create();
        $category = $this->acceptanceCategory('asset');
        $wanted = Company::factory()->create();

        $this->heldAsset($holder, $category, $wanted, 'In scope asset');
        $this->heldAsset($holder, $category, Company::factory()->create(), 'Out of scope asset');

        $this->artisan('snipeit:regenerate-acceptances', ['--no-interaction' => true, '--company' => [$wanted->id]])
            ->expectsOutputToContain('In scope asset')
            ->expectsOutput('To re-request: 1.')
            ->assertExitCode(0);
    }

    public function test_license_seats_can_be_filtered_by_category(): void
    {
        $holder = User::factory()->create();
        $wanted = $this->acceptanceCategory('license');

        $this->heldLicenseSeat($holder, $wanted, Company::factory()->create(), 'In scope licence');
        $this->heldLicenseSeat($holder, $this->acceptanceCategory('license'), Company::factory()->create(), 'Out of scope licence');

        $this->artisan('snipeit:regenerate-acceptances', ['--no-interaction' => true, '--category' => [$wanted->id]])
            ->expectsOutputToContain('In scope licence')
            ->expectsOutput('To re-request: 1.')
            ->assertExitCode(0);
    }

    /**
     * `license_seats` has no `company_id` column, so this one hops through the license.
     */
    public function test_license_seats_can_be_filtered_by_company(): void
    {
        $holder = User::factory()->create();
        $category = $this->acceptanceCategory('license');
        $wanted = Company::factory()->create();

        $this->heldLicenseSeat($holder, $category, $wanted, 'In scope licence');
        $this->heldLicenseSeat($holder, $category, Company::factory()->create(), 'Out of scope licence');

        $this->artisan('snipeit:regenerate-acceptances', ['--no-interaction' => true, '--company' => [$wanted->id]])
            ->expectsOutputToContain('In scope licence')
            ->expectsOutput('To re-request: 1.')
            ->assertExitCode(0);
    }

    public function test_accessories_can_be_filtered_by_category(): void
    {
        $holder = User::factory()->create();
        $wanted = $this->acceptanceCategory('accessory');

        $this->heldAccessory($holder, $wanted, Company::factory()->create(), 'In scope accessory');
        $this->heldAccessory($holder, $this->acceptanceCategory('accessory'), Company::factory()->create(), 'Out of scope accessory');

        $this->artisan('snipeit:regenerate-acceptances', ['--no-interaction' => true, '--category' => [$wanted->id]])
            ->expectsOutputToContain('In scope accessory')
            ->expectsOutput('To re-request: 1.')
            ->assertExitCode(0);
    }

    public function test_accessories_can_be_filtered_by_company(): void
    {
        $holder = User::factory()->create();
        $category = $this->acceptanceCategory('accessory');
        $wanted = Company::factory()->create();

        $this->heldAccessory($holder, $category, $wanted, 'In scope accessory');
        $this->heldAccessory($holder, $category, Company::factory()->create(), 'Out of scope accessory');

        $this->artisan('snipeit:regenerate-acceptances', ['--no-interaction' => true, '--company' => [$wanted->id]])
            ->expectsOutputToContain('In scope accessory')
            ->expectsOutput('To re-request: 1.')
            ->assertExitCode(0);
    }

    public function test_consumables_can_be_filtered_by_category(): void
    {
        $holder = User::factory()->create();
        $wanted = $this->acceptanceCategory('consumable');

        $this->heldConsumable($holder, $wanted, Company::factory()->create(), 'In scope consumable');
        $this->heldConsumable($holder, $this->acceptanceCategory('consumable'), Company::factory()->create(), 'Out of scope consumable');

        $this->artisan('snipeit:regenerate-acceptances', ['--no-interaction' => true, '--category' => [$wanted->id]])
            ->expectsOutputToContain('In scope consumable')
            ->expectsOutput('To re-request: 1.')
            ->assertExitCode(0);
    }

    public function test_consumables_can_be_filtered_by_company(): void
    {
        $holder = User::factory()->create();
        $category = $this->acceptanceCategory('consumable');
        $wanted = Company::factory()->create();

        $this->heldConsumable($holder, $category, $wanted, 'In scope consumable');
        $this->heldConsumable($holder, $category, Company::factory()->create(), 'Out of scope consumable');

        $this->artisan('snipeit:regenerate-acceptances', ['--no-interaction' => true, '--company' => [$wanted->id]])
            ->expectsOutputToContain('In scope consumable')
            ->expectsOutput('To re-request: 1.')
            ->assertExitCode(0);
    }

    public function test_components_can_be_filtered_by_category(): void
    {
        $holder = User::factory()->create();
        $wanted = $this->acceptanceCategory('component');

        $this->heldComponent($holder, $wanted, Company::factory()->create(), 'In scope component');
        $this->heldComponent($holder, $this->acceptanceCategory('component'), Company::factory()->create(), 'Out of scope component');

        $this->artisan('snipeit:regenerate-acceptances', ['--no-interaction' => true, '--category' => [$wanted->id]])
            ->expectsOutputToContain('In scope component')
            ->expectsOutput('To re-request: 1.')
            ->assertExitCode(0);
    }

    public function test_components_can_be_filtered_by_company(): void
    {
        $holder = User::factory()->create();
        $category = $this->acceptanceCategory('component');
        $wanted = Company::factory()->create();

        $this->heldComponent($holder, $category, $wanted, 'In scope component');
        $this->heldComponent($holder, $category, Company::factory()->create(), 'Out of scope component');

        $this->artisan('snipeit:regenerate-acceptances', ['--no-interaction' => true, '--company' => [$wanted->id]])
            ->expectsOutputToContain('In scope component')
            ->expectsOutput('To re-request: 1.')
            ->assertExitCode(0);
    }

    /**
     * A CheckoutAcceptance factory already keyed to one (item, user) pair. The action
     * log the factory would otherwise write is irrelevant to classification, so it is
     * turned off rather than left to stamp the asset's assignment a second time.
     */
    private function acceptanceFor(Model $item, User $user): CheckoutAcceptanceFactory
    {
        return CheckoutAcceptance::factory()->withoutActionLog()->state([
            'checkoutable_type' => $item->getMorphClass(),
            'checkoutable_id' => $item->getKey(),
            'assigned_to_id' => $user->id,
        ]);
    }

    public function test_pending_acceptance_covering_the_units_held_is_skipped(): void
    {
        $holder = User::factory()->create();
        $asset = $this->assetIn($this->acceptanceCategory('asset'), [
            'assigned_to' => $holder->id,
            'assigned_type' => User::class,
        ]);
        $this->acceptanceFor($asset, $holder)->pending()->create();

        $this->artisan('snipeit:regenerate-acceptances', ['--no-interaction' => true])
            ->expectsOutput('To re-request: 0.')
            ->expectsOutput('Already covered by a pending request: 1.')
            ->assertExitCode(0);
    }

    public function test_pair_that_already_accepted_is_re_requested(): void
    {
        $holder = User::factory()->create();
        $asset = $this->assetIn($this->acceptanceCategory('asset'), [
            'assigned_to' => $holder->id,
            'assigned_type' => User::class,
        ]);
        $this->acceptanceFor($asset, $holder)->accepted()->create();

        $this->artisan('snipeit:regenerate-acceptances', ['--no-interaction' => true])
            ->expectsOutput('To re-request: 1.')
            ->expectsOutput('Already covered by a pending request: 0.')
            ->assertExitCode(0);
    }

    public function test_trashed_acceptance_does_not_count_toward_pending_coverage(): void
    {
        $holder = User::factory()->create();
        $asset = $this->assetIn($this->acceptanceCategory('asset'), [
            'assigned_to' => $holder->id,
            'assigned_type' => User::class,
        ]);
        $this->acceptanceFor($asset, $holder)->pending()->create()->delete();

        $this->artisan('snipeit:regenerate-acceptances', ['--no-interaction' => true])
            ->expectsOutput('To re-request: 1.')
            ->assertExitCode(0);
    }

    public function test_partially_covered_accessory_re_requests_only_the_shortfall(): void
    {
        $holder = User::factory()->create();
        $accessory = Accessory::factory()->create(['category_id' => $this->acceptanceCategory('accessory')->id]);
        $accessory->checkouts()->createMany([
            ['assigned_to' => $holder->id, 'assigned_type' => User::class],
            ['assigned_to' => $holder->id, 'assigned_type' => User::class],
            ['assigned_to' => $holder->id, 'assigned_type' => User::class],
        ]);
        $pending = $this->acceptanceFor($accessory, $holder)->pending()->create(['qty' => 1]);

        $this->artisan('snipeit:regenerate-acceptances', ['--no-interaction' => true])
            ->expectsTable(
                ['User', 'Item', 'Type', 'Units held', 'Qty'],
                [[$holder->present()->fullName, $accessory->present()->name, 'Accessory', 3, 2]],
            )
            ->assertExitCode(0);

        $this->assertDatabaseHas('checkout_acceptances', [
            'id' => $pending->id,
            'qty' => 1,
            'accepted_at' => null,
            'declined_at' => null,
            'deleted_at' => null,
        ]);

        $expected = [
            'Accessory #'.$accessory->id.' -> user #'.$holder->id.' qty 1',
            'Accessory #'.$accessory->id.' -> user #'.$holder->id.' qty 2',
        ];

        $this->assertSame($expected, $this->acceptanceRows());
    }

    public function test_pending_acceptance_with_a_null_qty_covers_one_unit(): void
    {
        $holder = User::factory()->create();
        $accessory = Accessory::factory()->create(['category_id' => $this->acceptanceCategory('accessory')->id]);
        $accessory->checkouts()->createMany([
            ['assigned_to' => $holder->id, 'assigned_type' => User::class],
            ['assigned_to' => $holder->id, 'assigned_type' => User::class],
        ]);
        $this->acceptanceFor($accessory, $holder)->pending()->create(['qty' => null]);

        $this->artisan('snipeit:regenerate-acceptances', ['--no-interaction' => true])
            ->expectsTable(
                ['User', 'Item', 'Type', 'Units held', 'Qty'],
                [[$holder->present()->fullName, $accessory->present()->name, 'Accessory', 2, 1]],
            )
            ->assertExitCode(0);
    }

    /**
     * `acceptanceHistory()` deliberately over-fetches — it queries every candidate item id
     * against every candidate user id, a cross product — so rows belonging to other
     * holders of the same accessory arrive in the same result set and have to be split
     * apart in PHP. The other pair's row is worth 5 against this holder's single unit, so
     * a keying mistake flips this pair to "already covered" instead of shaving a unit off.
     */
    public function test_another_pairs_pending_acceptance_does_not_count_toward_this_pairs_coverage(): void
    {
        $holder = User::factory()->create();
        $accessory = Accessory::factory()->create(['category_id' => $this->acceptanceCategory('accessory')->id]);
        $accessory->checkouts()->create(['assigned_to' => $holder->id, 'assigned_type' => User::class]);
        $this->acceptanceFor($accessory, User::factory()->create())->pending()->create(['qty' => 5]);

        $this->artisan('snipeit:regenerate-acceptances', ['--no-interaction' => true])
            ->expectsTable(
                ['User', 'Item', 'Type', 'Units held', 'Qty'],
                [[$holder->present()->fullName, $accessory->present()->name, 'Accessory', 1, 1]],
            )
            ->assertExitCode(0);
    }

    public function test_pair_that_declined_is_re_requested_by_default(): void
    {
        $holder = User::factory()->create();
        $asset = $this->assetIn($this->acceptanceCategory('asset'), [
            'assigned_to' => $holder->id,
            'assigned_type' => User::class,
        ]);
        $this->acceptanceFor($asset, $holder)->declined()->create();

        $this->artisan('snipeit:regenerate-acceptances', ['--no-interaction' => true])
            ->expectsOutput('To re-request: 1.')
            ->expectsOutput('Previously declined: 1.')
            ->assertExitCode(0);
    }

    public function test_pair_that_declined_is_excluded_and_counted_under_exclude_declined(): void
    {
        $holder = User::factory()->create();
        $asset = $this->assetIn($this->acceptanceCategory('asset'), [
            'assigned_to' => $holder->id,
            'assigned_type' => User::class,
        ]);
        $this->acceptanceFor($asset, $holder)->declined()->create();

        $this->artisan('snipeit:regenerate-acceptances', ['--no-interaction' => true, '--exclude-declined' => true])
            ->expectsOutput('To re-request: 0.')
            ->expectsOutput('Previously declined: 1.')
            ->expectsOutput('Previously declined and excluded: 1.')
            ->assertExitCode(0);
    }

    public function test_a_decline_answered_again_later_is_not_treated_as_declined(): void
    {
        $holder = User::factory()->create();
        $asset = $this->assetIn($this->acceptanceCategory('asset'), [
            'assigned_to' => $holder->id,
            'assigned_type' => User::class,
        ]);
        $this->acceptanceFor($asset, $holder)->declined()->create();
        $this->acceptanceFor($asset, $holder)->accepted()->create();

        $this->artisan('snipeit:regenerate-acceptances', ['--no-interaction' => true, '--exclude-declined' => true])
            ->expectsOutput('To re-request: 1.')
            ->expectsOutput('Previously declined: 0.')
            ->assertExitCode(0);
    }

    public function test_dry_run_creates_nothing(): void
    {
        $holder = User::factory()->create();
        $this->assetIn($this->acceptanceCategory('asset'), [
            'assigned_to' => $holder->id,
            'assigned_type' => User::class,
        ]);

        $this->artisan('snipeit:regenerate-acceptances', ['--no-interaction' => true, '--dry-run' => true])
            ->expectsOutput('To re-request: 1.')
            ->expectsOutput('Nothing was created.')
            ->assertExitCode(0);

        $this->assertDatabaseEmpty('checkout_acceptances');
    }

    public function test_asset_re_request_is_created_with_a_null_qty(): void
    {
        $holder = User::factory()->create();
        $asset = $this->assetIn($this->acceptanceCategory('asset'), [
            'assigned_to' => $holder->id,
            'assigned_type' => User::class,
        ]);

        $this->artisan('snipeit:regenerate-acceptances', ['--no-interaction' => true])
            ->expectsOutput('Created: 1.')
            ->assertExitCode(0);

        $this->assertSame(
            ['Asset #'.$asset->id.' -> user #'.$holder->id.' qty null'],
            $this->acceptanceRows(),
        );
    }

    public function test_declined_pair_excluded_from_the_run_creates_nothing(): void
    {
        $holder = User::factory()->create();
        $asset = $this->assetIn($this->acceptanceCategory('asset'), [
            'assigned_to' => $holder->id,
            'assigned_type' => User::class,
        ]);
        $this->acceptanceFor($asset, $holder)->declined()->create();

        $this->artisan('snipeit:regenerate-acceptances', ['--no-interaction' => true, '--exclude-declined' => true])
            ->expectsOutput('Created: 0.')
            ->assertExitCode(0);

        $this->assertSame(
            ['Asset #'.$asset->id.' -> user #'.$holder->id.' qty null declined'],
            $this->acceptanceRows(),
        );
    }

    public function test_re_requested_row_carries_no_alert_recipient(): void
    {
        $admin = User::factory()->create();
        $holder = User::factory()->create();
        $asset = $this->assetIn($this->acceptanceCategory('asset', ['alert_on_response' => true]), [
            'assigned_to' => $holder->id,
            'assigned_type' => User::class,
        ]);
        $this->acceptanceFor($asset, $holder)->accepted()->withAlertingTo($admin)->create();

        $this->artisan('snipeit:regenerate-acceptances', ['--no-interaction' => true])->assertExitCode(0);

        $this->assertNull($this->newestAcceptance()->alert_on_response_id);
    }

    public function test_notify_sends_one_email_per_holder_however_many_rows_they_got(): void
    {
        Mail::fake();
        $company = Company::factory()->create();
        $alice = User::factory()->create(['email' => 'alice@example.test']);
        $bob = User::factory()->create(['email' => 'bob@example.test']);
        $this->heldAsset($alice, $this->acceptanceCategory('asset'), $company, 'Alice laptop');
        $this->heldAccessory($alice, $this->acceptanceCategory('accessory'), $company, 'Mouse');
        $this->heldAsset($bob, $this->acceptanceCategory('asset'), $company, 'Bob laptop');

        $this->artisan('snipeit:regenerate-acceptances', ['--no-interaction' => true, '--notify' => true])
            ->expectsOutput('Created: 3.')
            ->expectsOutput('Notified: 2.')
            ->assertExitCode(0);

        Mail::assertSent(AcceptanceReRequestMail::class, function (AcceptanceReRequestMail $mail) {
            return $mail->hasTo('alice@example.test')
                && count($mail->items) === 2
                && $mail->hasSubject(trans('mail.acceptance_re_request'))
                && str_contains($mail->render(), trans_choice('mail.acceptance_re_request_intro', 2, ['count' => 2]));
        });
        Mail::assertSent(
            AcceptanceReRequestMail::class,
            fn (AcceptanceReRequestMail $mail) => $mail->hasTo('bob@example.test') && count($mail->items) === 1,
        );
        Mail::assertSent(AcceptanceReRequestMail::class, 2);
    }

    public function test_the_email_names_every_item_the_holder_was_re_requested_for(): void
    {
        Mail::fake();
        $company = Company::factory()->create();
        $holder = User::factory()->create(['email' => 'holder@example.test']);
        $this->heldAsset($holder, $this->acceptanceCategory('asset'), $company, 'Alice laptop');
        $mouse = $this->heldAccessory($holder, $this->acceptanceCategory('accessory'), $company, 'Mouse');
        $mouse->checkouts()->create(['assigned_to' => $holder->id, 'assigned_type' => User::class]);

        $this->artisan('snipeit:regenerate-acceptances', ['--no-interaction' => true, '--notify' => true])->assertExitCode(0);

        Mail::assertSent(AcceptanceReRequestMail::class, function (AcceptanceReRequestMail $mail) {
            $body = $mail->render();

            return str_contains($body, 'Alice laptop')
                && str_contains($body, trans('general.asset'))
                && str_contains($body, 'Mouse ('.trans('general.accessory').') × 2')
                && ! str_contains($body, trans_choice('mail.acceptance_re_request_more_items', 1, ['count' => 1]));
        });
    }

    public function test_the_email_lists_ten_items_then_counts_the_rest(): void
    {
        Mail::fake();
        $company = Company::factory()->create();
        $category = $this->acceptanceCategory('asset');
        $holder = User::factory()->create(['email' => 'holder@example.test']);

        foreach (range(1, 12) as $number) {
            $this->heldAsset($holder, $category, $company, 'Laptop '.$number);
        }

        $this->artisan('snipeit:regenerate-acceptances', ['--no-interaction' => true, '--notify' => true])->assertExitCode(0);

        Mail::assertSent(AcceptanceReRequestMail::class, function (AcceptanceReRequestMail $mail) {
            $body = $mail->render();
            $named = array_filter(
                range(1, 12),
                fn (int $number) => str_contains($body, 'Laptop '.$number.' #'),
            );

            return count($mail->items) === 12
                && count($named) === 10
                && str_contains($body, trans_choice('mail.acceptance_re_request_more_items', 2, ['count' => 2]));
        });
    }

    public function test_nothing_is_emailed_without_the_notify_flag(): void
    {
        Mail::fake();
        $holder = User::factory()->create();
        $this->assetIn($this->acceptanceCategory('asset'), [
            'assigned_to' => $holder->id,
            'assigned_type' => User::class,
        ]);

        $this->artisan('snipeit:regenerate-acceptances', ['--no-interaction' => true])
            ->expectsOutput('Created: 1.')
            ->doesntExpectOutput('Notified: 1.')
            ->assertExitCode(0);

        Mail::assertNothingSent();
    }

    public function test_dry_run_with_notify_emails_nobody(): void
    {
        Mail::fake();
        $holder = User::factory()->create();
        $this->assetIn($this->acceptanceCategory('asset'), [
            'assigned_to' => $holder->id,
            'assigned_type' => User::class,
        ]);

        $this->artisan('snipeit:regenerate-acceptances', ['--no-interaction' => true, '--dry-run' => true, '--notify' => true])
            ->expectsOutput('Nothing was created.')
            ->expectsOutput('Notified: 0.')
            ->assertExitCode(0);

        Mail::assertNothingSent();
    }

    public function test_holder_without_an_email_still_gets_their_row_and_is_reported(): void
    {
        Mail::fake();
        $holder = User::factory()->create(['email' => '']);
        $asset = $this->assetIn($this->acceptanceCategory('asset'), [
            'assigned_to' => $holder->id,
            'assigned_type' => User::class,
        ]);

        $this->artisan('snipeit:regenerate-acceptances', ['--no-interaction' => true, '--notify' => true])
            ->expectsOutput('Created: 1.')
            ->expectsOutput('Notified: 0.')
            ->expectsOutput('The following users do not have an email address:')
            ->expectsTable(['ID', 'Name'], [[$holder->id, $holder->present()->fullName]])
            ->assertExitCode(0);

        Mail::assertNothingSent();
        $this->assertSame(
            ['Asset #'.$asset->id.' -> user #'.$holder->id.' qty null'],
            $this->acceptanceRows(),
        );
    }

    public function test_the_no_email_table_is_not_printed_when_every_holder_has_an_email(): void
    {
        Mail::fake();
        $holder = User::factory()->create(['email' => 'holder@example.test']);
        $this->assetIn($this->acceptanceCategory('asset'), [
            'assigned_to' => $holder->id,
            'assigned_type' => User::class,
        ]);

        $this->artisan('snipeit:regenerate-acceptances', ['--no-interaction' => true, '--notify' => true])
            ->expectsOutput('Notified: 1.')
            ->doesntExpectOutput('The following users do not have an email address:')
            ->assertExitCode(0);
    }

    /**
     * The wizard's prompts, pre-answered in the order `runWizard()` walks them.
     *
     * `multiselect()` falls back to a single `choice()` call under test, so each picker
     * is one `expectsQuestion` taking an array of ids. The two pickers are only asked
     * when there is something to pick, so a fixture with no company never sees the
     * company step.
     *
     * @param  array<int, int>  $categoryIds
     * @param  array<int, int>  $companyIds
     */
    private function runWizard(
        array $categoryIds = [],
        array $companyIds = [],
        bool $excludeDeclined = false,
        bool $notify = false,
        bool $confirm = true,
    ): PendingCommand {
        return $this->artisan('snipeit:regenerate-acceptances')
            ->expectsQuestion('Which categories should this run cover?', $categoryIds)
            ->expectsQuestion('Which companies should this run cover?', $companyIds)
            ->expectsConfirmation('Skip holders whose latest response was a decline?', $excludeDeclined ? 'yes' : 'no')
            ->expectsConfirmation('Email each affected holder?', $notify ? 'yes' : 'no')
            ->expectsConfirmation('Create these acceptance requests?', $confirm ? 'yes' : 'no');
    }

    public function test_wizard_creates_the_rows_it_previewed_when_confirmed(): void
    {
        $company = Company::factory()->create();
        $holder = User::factory()->create();
        $asset = $this->heldAsset($holder, $this->acceptanceCategory('asset'), $company, 'Wizard laptop');

        $this->runWizard()
            ->expectsOutput('Created: 1.')
            ->assertExitCode(0);

        $this->assertSame(
            ['Asset #'.$asset->id.' -> user #'.$holder->id.' qty null'],
            $this->acceptanceRows(),
        );
    }

    public function test_wizard_creates_nothing_when_the_operator_aborts(): void
    {
        $company = Company::factory()->create();
        $holder = User::factory()->create();
        $this->heldAsset($holder, $this->acceptanceCategory('asset'), $company, 'Wizard laptop');

        $this->runWizard(confirm: false)
            ->expectsOutput('Nothing was created.')
            ->doesntExpectOutput('Created: 1.')
            ->assertExitCode(0);

        $this->assertSame([], $this->acceptanceRows());
    }

    public function test_wizard_sends_nothing_when_the_operator_aborts(): void
    {
        Mail::fake();
        $company = Company::factory()->create();
        $holder = User::factory()->create(['email' => 'holder@example.test']);
        $this->heldAsset($holder, $this->acceptanceCategory('asset'), $company, 'Wizard laptop');

        $this->runWizard(notify: true, confirm: false)->assertExitCode(0);

        Mail::assertNothingSent();
    }

    /**
     * The preview is a dry run, so a confirmed wizard run scans twice.
     *
     * "Nothing was created." is the dry pass's footer and "Created: 1." the real one;
     * seeing both in a run the operator confirmed is what proves the preview happened
     * before the rows did.
     */
    public function test_wizard_previews_as_a_dry_run_then_creates_for_real(): void
    {
        $company = Company::factory()->create();
        $holder = User::factory()->create();
        $this->heldAsset($holder, $this->acceptanceCategory('asset'), $company, 'Wizard laptop');

        $this->runWizard()
            ->expectsOutput('Nothing was created.')
            ->expectsOutput('Created: 1.')
            ->assertExitCode(0);

        $this->assertCount(1, $this->acceptanceRows());
    }

    public function test_wizard_answers_carry_into_the_run(): void
    {
        Mail::fake();
        $company = Company::factory()->create();
        $holder = User::factory()->create(['email' => 'holder@example.test']);
        $this->heldAsset($holder, $this->acceptanceCategory('asset'), $company, 'Wizard laptop');

        $this->runWizard(notify: true)
            ->expectsOutput('Notified: 1.')
            ->assertExitCode(0);

        Mail::assertSent(AcceptanceReRequestMail::class, 1);
    }

    public function test_wizard_excludes_decliners_when_the_operator_says_so(): void
    {
        $company = Company::factory()->create();
        $holder = User::factory()->create();
        $asset = $this->heldAsset($holder, $this->acceptanceCategory('asset'), $company, 'Wizard laptop');
        $this->acceptanceFor($asset, $holder)->declined()->create();

        $this->runWizard(excludeDeclined: true)
            ->expectsOutput('Previously declined and excluded: 1.')
            ->assertExitCode(0);

        $this->assertSame(
            ['Asset #'.$asset->id.' -> user #'.$holder->id.' qty null declined'],
            $this->acceptanceRows(),
        );
    }

    /**
     * Decision 16's announce-and-skip: a flag answers its step, and the step is not asked.
     *
     * Every prompt the wizard could reach is left unanswered here except the final
     * confirm. If a skipped step were actually asked, it would reach the mocked question
     * helper and throw rather than silently taking a default.
     */
    public function test_wizard_announces_and_skips_every_step_a_flag_answered(): void
    {
        Mail::fake();
        $company = Company::factory()->create();
        $category = $this->acceptanceCategory('asset');
        $holder = User::factory()->create(['email' => 'holder@example.test']);
        $this->heldAsset($holder, $category, $company, 'Wizard laptop');

        $this->artisan('snipeit:regenerate-acceptances', [
            '--category' => [$category->id],
            '--company' => [$company->id],
            '--exclude-declined' => true,
            '--notify' => true,
        ])
            ->expectsConfirmation('Create these acceptance requests?', 'yes')
            ->expectsOutput('--category passed — skipping')
            ->expectsOutput('--company passed — skipping')
            ->expectsOutput('--exclude-declined passed — skipping')
            ->expectsOutput('--notify passed — skipping')
            ->expectsOutput('Notified: 1.')
            ->assertExitCode(0);
    }

    public function test_interactive_dry_run_stops_at_the_preview_without_confirming(): void
    {
        $company = Company::factory()->create();
        $holder = User::factory()->create();
        $this->heldAsset($holder, $this->acceptanceCategory('asset'), $company, 'Wizard laptop');

        $this->artisan('snipeit:regenerate-acceptances', ['--dry-run' => true])
            ->expectsQuestion('Which categories should this run cover?', [])
            ->expectsQuestion('Which companies should this run cover?', [])
            ->expectsConfirmation('Skip holders whose latest response was a decline?', 'no')
            ->expectsConfirmation('Email each affected holder?', 'no')
            ->expectsOutput('Nothing was created.')
            ->assertExitCode(0);

        $this->assertSame([], $this->acceptanceRows());
    }

    /**
     * Decision C parity: a --no-interaction run is slice 3's run, unchanged.
     *
     * The wizard never starts, so no prompt is pre-answered here; reaching one would
     * throw. The flag defaults are the ones that applied before the wizard existed.
     */
    public function test_no_interaction_run_behaves_exactly_as_it_did_before_the_wizard(): void
    {
        Mail::fake();
        $company = Company::factory()->create();
        $holder = User::factory()->create(['email' => 'holder@example.test']);
        $asset = $this->heldAsset($holder, $this->acceptanceCategory('asset'), $company, 'Wizard laptop');

        $this->artisan('snipeit:regenerate-acceptances', ['--no-interaction' => true])
            ->expectsOutput('Created: 1.')
            ->doesntExpectOutput('Notified: 1.')
            ->assertExitCode(0);

        $this->assertSame(
            ['Asset #'.$asset->id.' -> user #'.$holder->id.' qty null'],
            $this->acceptanceRows(),
        );
        Mail::assertNothingSent();
    }

    /**
     * One realistic install, run for real.
     *
     * Every test above proves one rule against one builder. This one proves the five
     * builders *compose*: that a run over a mixed install creates exactly the rows it
     * should and no others. It exists because a key-collision bug between the builders
     * passed every rule test and surfaced only because one fixture happened to hold two
     * types — rule tests cannot see the seams, so this asserts the created row set.
     *
     * Bob is the composition case: one asset he holds is a candidate in its own right
     * *and* the route to three more (a seat, an accessory and a component).
     */
    public function test_a_mixed_install_creates_exactly_the_rows_it_should(): void
    {
        $company = Company::factory()->create();
        $assetCategory = $this->acceptanceCategory('asset');
        $licenseCategory = $this->acceptanceCategory('license');
        $accessoryCategory = $this->acceptanceCategory('accessory');
        $consumableCategory = $this->acceptanceCategory('consumable');
        $componentCategory = $this->acceptanceCategory('component');

        // Alice holds three types, and one of her three mice is already spoken for.
        $alice = User::factory()->create();
        $aliceLaptop = $this->heldAsset($alice, $assetCategory, $company, 'Alice laptop');
        $mouse = $this->heldAccessory($alice, $accessoryCategory, $company, 'Mouse');
        $mouse->checkouts()->createMany([
            ['assigned_to' => $alice->id, 'assigned_type' => User::class],
            ['assigned_to' => $alice->id, 'assigned_type' => User::class],
        ]);
        $toner = $this->heldConsumable($alice, $consumableCategory, $company, 'Toner');
        $this->acceptanceFor($mouse, $alice)->pending()->create(['qty' => 1]);

        // A fourth mouse sits at a location, which is nobody's to accept.
        $mouse->checkouts()->create([
            'assigned_to' => Location::factory()->create()->id,
            'assigned_type' => Location::class,
        ]);

        // Bob holds one asset, and it carries a seat, an accessory and a component.
        $bob = User::factory()->create();
        $bobLaptop = $this->heldAsset($bob, $assetCategory, $company, 'Bob laptop');
        $bobSeat = LicenseSeat::factory()->create([
            'license_id' => License::factory()->create([
                'name' => 'CAD',
                'category_id' => $licenseCategory->id,
                'company_id' => $company->id,
            ])->id,
            'asset_id' => $bobLaptop->id,
            'assigned_to' => null,
        ]);
        $keyboard = Accessory::factory()->create([
            'name' => 'Keyboard',
            'category_id' => $accessoryCategory->id,
            'company_id' => $company->id,
        ]);
        $keyboard->checkouts()->create([
            'assigned_to' => $bobLaptop->id,
            'assigned_type' => Asset::class,
        ]);
        $memory = Component::factory()->create([
            'name' => 'RAM',
            'category_id' => $componentCategory->id,
            'company_id' => $company->id,
        ]);
        $memory->assets()->attach($bobLaptop->id, ['assigned_qty' => 2, 'created_by' => $bob->id]);

        // Carol declined last time and still holds the asset, so a default run re-asks her.
        $carol = User::factory()->create();
        $carolPhone = $this->heldAsset($carol, $assetCategory, $company, 'Carol phone');
        $this->acceptanceFor($carolPhone, $carol)->declined()->create();

        // Dan is already covered by an in-flight request.
        $dan = User::factory()->create();
        $danTablet = $this->heldAsset($dan, $assetCategory, $company, 'Dan tablet');
        $this->acceptanceFor($danTablet, $dan)->pending()->create();

        // And this one is on the shelf.
        $this->assetIn($assetCategory);

        $this->artisan('snipeit:regenerate-acceptances', ['--no-interaction' => true])
            ->expectsOutput('To re-request: 8.')
            ->expectsOutput('  Asset: 3')
            ->expectsOutput('  LicenseSeat: 1')
            ->expectsOutput('  Accessory: 2')
            ->expectsOutput('  Consumable: 1')
            ->expectsOutput('  Component: 1')
            ->expectsOutput('Previously declined: 1.')
            ->expectsOutput('Already covered by a pending request: 1.')
            ->expectsOutput('Created: 8.')
            ->assertExitCode(0);

        $expected = [
            // the eight rows this run created
            'Asset #'.$aliceLaptop->id.' -> user #'.$alice->id.' qty null',
            'Accessory #'.$mouse->id.' -> user #'.$alice->id.' qty 2',
            'Consumable #'.$toner->id.' -> user #'.$alice->id.' qty 1',
            'Asset #'.$bobLaptop->id.' -> user #'.$bob->id.' qty null',
            'LicenseSeat #'.$bobSeat->id.' -> user #'.$bob->id.' qty null',
            'Accessory #'.$keyboard->id.' -> user #'.$bob->id.' qty 1',
            'Component #'.$memory->id.' -> user #'.$bob->id.' qty 2',
            'Asset #'.$carolPhone->id.' -> user #'.$carol->id.' qty null',
            // and the three it left alone
            'Accessory #'.$mouse->id.' -> user #'.$alice->id.' qty 1',
            'Asset #'.$carolPhone->id.' -> user #'.$carol->id.' qty null declined',
            'Asset #'.$danTablet->id.' -> user #'.$dan->id.' qty null',
        ];
        sort($expected);

        $this->assertSame($expected, $this->acceptanceRows());
    }

    /**
     * Every acceptance row in the database, one readable line each and sorted, so an
     * assertion states the whole row set rather than an insertion order.
     *
     * @return array<int, string>
     */
    private function acceptanceRows(): array
    {
        $rows = CheckoutAcceptance::query()
            ->get()
            ->map(fn (CheckoutAcceptance $acceptance) => sprintf(
                '%s #%d -> user #%d qty %s%s',
                class_basename($acceptance->checkoutable_type),
                $acceptance->checkoutable_id,
                $acceptance->assigned_to_id,
                $acceptance->qty ?? 'null',
                match (true) {
                    $acceptance->declined_at !== null => ' declined',
                    $acceptance->accepted_at !== null => ' accepted',
                    default => '',
                },
            ))
            ->all();

        sort($rows);

        return $rows;
    }

    private function newestAcceptance(): CheckoutAcceptance
    {
        return CheckoutAcceptance::query()->orderByDesc('id')->firstOrFail();
    }

    private function heldAsset(User $holder, Category $category, Company $company, string $name): Asset
    {
        return Asset::factory()->create([
            'name' => $name,
            'model_id' => AssetModel::factory()->create(['category_id' => $category->id]),
            'company_id' => $company->id,
            'assigned_to' => $holder->id,
            'assigned_type' => User::class,
        ]);
    }

    private function heldLicenseSeat(User $holder, Category $category, Company $company, string $name): LicenseSeat
    {
        $license = License::factory()->create([
            'name' => $name,
            'category_id' => $category->id,
            'company_id' => $company->id,
        ]);

        return LicenseSeat::factory()->create([
            'license_id' => $license->id,
            'asset_id' => null,
            'assigned_to' => $holder->id,
        ]);
    }

    private function heldAccessory(User $holder, Category $category, Company $company, string $name): Accessory
    {
        $accessory = Accessory::factory()->create([
            'name' => $name,
            'category_id' => $category->id,
            'company_id' => $company->id,
        ]);

        $accessory->checkouts()->create([
            'assigned_to' => $holder->id,
            'assigned_type' => User::class,
        ]);

        return $accessory;
    }

    private function heldConsumable(User $holder, Category $category, Company $company, string $name): Consumable
    {
        $consumable = Consumable::factory()->create([
            'name' => $name,
            'category_id' => $category->id,
            'company_id' => $company->id,
        ]);

        $consumable->users()->attach($holder->id, ['created_by' => $holder->id]);

        return $consumable;
    }

    /**
     * A component's holder is always reached through an asset, so the carrier asset goes
     * in a category that does not require acceptance — otherwise it becomes a candidate
     * in its own right and the assertions count it.
     */
    private function heldComponent(User $holder, Category $category, Company $company, string $name): Component
    {
        $carrier = $this->assetIn(
            Category::factory()->create(['category_type' => 'asset', 'require_acceptance' => false]),
            ['assigned_to' => $holder->id, 'assigned_type' => User::class],
        );

        $component = Component::factory()->create([
            'name' => $name,
            'category_id' => $category->id,
            'company_id' => $company->id,
        ]);

        $component->assets()->attach($carrier->id, ['assigned_qty' => 1, 'created_by' => $holder->id]);

        return $component;
    }

    private function acceptanceCategory(string $type, array $attributes = []): Category
    {
        return Category::factory()->create(array_merge([
            'category_type' => $type,
            'require_acceptance' => true,
        ], $attributes));
    }

    private function assetIn(Category $category, array $attributes = []): Asset
    {
        return Asset::factory()->create(array_merge([
            'model_id' => AssetModel::factory()->create(['category_id' => $category->id]),
        ], $attributes));
    }
}
