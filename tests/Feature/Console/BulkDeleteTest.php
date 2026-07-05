<?php

namespace Tests\Feature\Console;

use App\Events\CheckoutableCheckedIn;
use App\Mail\BulkDeleteReportMail;
use App\Models\Accessory;
use App\Models\Asset;
use App\Models\CheckoutAcceptance;
use App\Models\Company;
use App\Models\Component;
use App\Models\Consumable;
use App\Models\License;
use App\Models\LicenseSeat;
use App\Models\Maintenance;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Testing\PendingCommand;
use Tests\TestCase;

class BulkDeleteTest extends TestCase
{
    // ---------------------------------------------------------------------------
    // Prompt sequence helpers
    // ---------------------------------------------------------------------------

    /**
     * Builds a PendingCommand with the full prompt sequence pre-answered.
     *
     * Search() falls back as two steps: ask() for the search term, then
     * choice() to select from matching results. Multiselect() and select()
     * each fall back as a single choice() call.
     */
    private function runCommand(
        User $admin,
        array $companyIds,
        array $types,
        string $deleteType = 'soft',
        bool $dryRun = false,
        bool $sendNotifications = false,
        bool $clearLogs = false,
        bool $deleteFiles = false,
        bool $doBackup = false,
        bool $confirm = true,
        bool $sendEmailReport = false,
        string $deleteCompanyType = 'keep',
    ): PendingCommand {
        $hasNotifiableTypes = ! empty(array_intersect($types, ['assets', 'licenses', 'accessories', 'components']));

        $searchLabel = 'Who are you? Search by username, first or last name.';
        $companiesLabel = 'Which companies would you like to check in and delete items for?';
        $typesLabel = 'What item types would you like to check in and delete?';
        $deleteLabel = 'How should items be deleted?';

        $cmd = $this->artisan('snipeit:checkin-delete-items')
            // Step 1: dry run confirm
            ->expectsConfirmation('Is this a dry run?', $dryRun ? 'yes' : 'no')
            // Step 2: search() — ask() for the search term, then choice() to pick from results
            // Using expectsQuestion for both to avoid triggering choices validation
            ->expectsQuestion($searchLabel, $admin->username)
            ->expectsQuestion($searchLabel, (string) $admin->id)
            // Step 3: multisearch for companies — ask() for search term, then multiselectFallback()
            ->expectsQuestion($companiesLabel, '')
            ->expectsQuestion($companiesLabel, $companyIds)
            // Step 4: multiselect for item types
            ->expectsQuestion($typesLabel, $types)
            // Step 5: select for delete mode
            ->expectsQuestion($deleteLabel, $deleteType);

        // Step 6: notification confirm only shown for checkiable types
        if ($hasNotifiableTypes) {
            $cmd->expectsConfirmation('Should we send checkin notifications?', $sendNotifications ? 'yes' : 'no');
        }

        // Steps 7–11 and final confirm
        $cmd->expectsConfirmation('Should we clear related action logs?', $clearLogs ? 'yes' : 'no');

        // Step 8: file deletion prompt — only shown when deleteType !== 'none'
        if ($deleteType !== 'none') {
            $cmd->expectsConfirmation('Should we also delete associated image and upload files?', $deleteFiles ? 'yes' : 'no');
        }

        // Step 9: company deletion — only shown when real companies (not just __null__) were selected
        if (! empty($companyIds)) {
            $cmd->expectsQuestion('Should the selected companies also be deleted?', $deleteCompanyType);
        }

        $cmd->expectsConfirmation('Should we run a backup before proceeding?', $doBackup ? 'yes' : 'no');

        // Email report prompt — only shown when admin has an email address
        if ($admin->email) {
            $cmd->expectsConfirmation("Send an email report to {$admin->email}?", $sendEmailReport ? 'yes' : 'no');
        }

        if (! $dryRun) {
            $cmd->expectsConfirmation('Are you sure you want to proceed? This cannot be undone.', $confirm ? 'yes' : 'no');
        }

        return $cmd;
    }

    // ---------------------------------------------------------------------------
    // Assets
    // ---------------------------------------------------------------------------

    public function test_assigned_asset_is_checked_in_and_soft_deleted(): void
    {
        $admin = User::factory()->superuser()->create();
        $company = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $company->id]);
        $asset = Asset::factory()->for($company)->assignedToUser($user)->create();

        $this->assertNotNull($asset->assigned_to);

        $this->runCommand($admin, [$company->id], ['assets'])
            ->assertExitCode(0);

        $this->assertSoftDeleted($asset);
        $this->assertNull($asset->fresh()->assigned_to);
    }

    public function test_unassigned_asset_is_soft_deleted(): void
    {
        $admin = User::factory()->superuser()->create();
        $company = Company::factory()->create();
        $asset = Asset::factory()->for($company)->create();

        $this->runCommand($admin, [$company->id], ['assets'])
            ->assertExitCode(0);

        $this->assertSoftDeleted($asset);
    }

    public function test_asset_is_hard_deleted(): void
    {
        $admin = User::factory()->superuser()->create();
        $company = Company::factory()->create();
        $asset = Asset::factory()->for($company)->assignedToUser()->create();

        $this->runCommand($admin, [$company->id], ['assets'], deleteType: 'hard')
            ->assertExitCode(0);

        $this->assertDatabaseMissing('assets', ['id' => $asset->id]);
    }

    public function test_asset_checkin_event_fired_when_notifications_enabled(): void
    {
        Event::fake([CheckoutableCheckedIn::class]);

        $admin = User::factory()->superuser()->create();
        $company = Company::factory()->create();
        $asset = Asset::factory()->for($company)->assignedToUser()->create();

        $this->runCommand($admin, [$company->id], ['assets'], sendNotifications: true)
            ->assertExitCode(0);

        Event::assertDispatched(CheckoutableCheckedIn::class, fn ($e) => $e->checkoutable->is($asset));
    }

    public function test_asset_checkin_event_not_fired_when_notifications_suppressed(): void
    {
        Event::fake([CheckoutableCheckedIn::class]);

        $admin = User::factory()->superuser()->create();
        $company = Company::factory()->create();
        Asset::factory()->for($company)->assignedToUser()->create();

        $this->runCommand($admin, [$company->id], ['assets'], sendNotifications: false)
            ->assertExitCode(0);

        Event::assertNotDispatched(CheckoutableCheckedIn::class);
    }

    public function test_asset_scoped_to_correct_company(): void
    {
        $admin = User::factory()->superuser()->create();
        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $assetA = Asset::factory()->for($companyA)->create();
        $assetB = Asset::factory()->for($companyB)->create();

        $this->runCommand($admin, [$companyA->id], ['assets'])
            ->assertExitCode(0);

        $this->assertSoftDeleted($assetA);
        $this->assertNotSoftDeleted($assetB);
    }

    public function test_asset_delete_clears_all_checkout_acceptances(): void
    {
        $admin = User::factory()->superuser()->create();
        $company = Company::factory()->create();
        $asset = Asset::factory()->for($company)->create();

        CheckoutAcceptance::factory()->create([
            'checkoutable_type' => Asset::class,
            'checkoutable_id' => $asset->id,
        ]);

        $this->runCommand($admin, [$company->id], ['assets'])
            ->assertExitCode(0);

        $this->assertDatabaseMissing('checkout_acceptances', [
            'checkoutable_type' => Asset::class,
            'checkoutable_id' => $asset->id,
        ]);
    }

    public function test_asset_checkin_clears_linked_license_seats(): void
    {
        $admin = User::factory()->superuser()->create();
        $company = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $company->id]);
        $asset = Asset::factory()->for($company)->assignedToUser($user)->create();
        $seat = LicenseSeat::factory()->create(['asset_id' => $asset->id, 'assigned_to' => $user->id]);

        $this->runCommand($admin, [$company->id], ['assets'])
            ->assertExitCode(0);

        $this->assertNull($seat->fresh()->assigned_to);
    }

    // ---------------------------------------------------------------------------
    // Licenses
    // ---------------------------------------------------------------------------

    public function test_assigned_license_seat_is_checked_in_and_license_soft_deleted(): void
    {
        $admin = User::factory()->superuser()->create();
        $company = Company::factory()->create();
        $license = License::factory()->for($company)->create();
        $seat = LicenseSeat::factory()->for($license)->assignedToUser()->create();

        $this->assertNotNull($seat->assigned_to);

        $this->runCommand($admin, [$company->id], ['licenses'])
            ->assertExitCode(0);

        $this->assertSoftDeleted($license);
        $this->assertNull($seat->fresh()->assigned_to);
        $this->assertNull($seat->fresh()->asset_id);
    }

    public function test_license_hard_delete_removes_seats_and_their_checkout_acceptances(): void
    {
        $admin = User::factory()->superuser()->create();
        $company = Company::factory()->create();
        $license = License::factory()->for($company)->create();
        $seat = LicenseSeat::factory()->for($license)->assignedToUser()->create();

        CheckoutAcceptance::factory()->create([
            'checkoutable_type' => LicenseSeat::class,
            'checkoutable_id' => $seat->id,
        ]);

        $this->runCommand($admin, [$company->id], ['licenses'], deleteType: 'hard')
            ->assertExitCode(0);

        $this->assertDatabaseMissing('licenses', ['id' => $license->id]);
        $this->assertDatabaseMissing('license_seats', ['id' => $seat->id]);
        $this->assertDatabaseMissing('checkout_acceptances', [
            'checkoutable_type' => LicenseSeat::class,
            'checkoutable_id' => $seat->id,
        ]);
    }

    public function test_license_soft_delete_also_soft_deletes_seats(): void
    {
        $admin = User::factory()->superuser()->create();
        $company = Company::factory()->create();
        $license = License::factory()->for($company)->create();
        $seat = LicenseSeat::factory()->for($license)->create();

        $this->runCommand($admin, [$company->id], ['licenses'])
            ->assertExitCode(0);

        $this->assertSoftDeleted($license);
        $this->assertSoftDeleted($seat);
    }

    public function test_license_scoped_to_correct_company(): void
    {
        $admin = User::factory()->superuser()->create();
        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $licenseA = License::factory()->for($companyA)->create();
        $licenseB = License::factory()->for($companyB)->create();

        $this->runCommand($admin, [$companyA->id], ['licenses'])
            ->assertExitCode(0);

        $this->assertSoftDeleted($licenseA);
        $this->assertNotSoftDeleted($licenseB);
    }

    // ---------------------------------------------------------------------------
    // Accessories
    // ---------------------------------------------------------------------------

    public function test_checked_out_accessory_is_checked_in_and_soft_deleted(): void
    {
        $admin = User::factory()->superuser()->create();
        $company = Company::factory()->create();
        $accessory = Accessory::factory()->for($company)->checkedOutToUser()->create();

        $this->assertEquals(1, $accessory->checkouts->count());

        $this->runCommand($admin, [$company->id], ['accessories'])
            ->assertExitCode(0);

        $this->assertSoftDeleted($accessory);
        $this->assertEquals(0, $accessory->fresh()->checkouts->count());
    }

    public function test_accessory_scoped_to_correct_company(): void
    {
        $admin = User::factory()->superuser()->create();
        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $accessoryA = Accessory::factory()->for($companyA)->checkedOutToUser()->create();
        $accessoryB = Accessory::factory()->for($companyB)->checkedOutToUser()->create();

        $this->runCommand($admin, [$companyA->id], ['accessories'])
            ->assertExitCode(0);

        $this->assertSoftDeleted($accessoryA);
        $this->assertNotSoftDeleted($accessoryB);
    }

    // ---------------------------------------------------------------------------
    // Components
    // ---------------------------------------------------------------------------

    public function test_checked_out_component_is_checked_in_and_soft_deleted(): void
    {
        $admin = User::factory()->superuser()->create();
        $company = Company::factory()->create();
        $component = Component::factory()->for($company)->checkedOutToAsset()->create();

        $this->assertDatabaseHas('components_assets', ['component_id' => $component->id]);

        $this->runCommand($admin, [$company->id], ['components'])
            ->assertExitCode(0);

        $this->assertSoftDeleted($component);
        $this->assertDatabaseMissing('components_assets', ['component_id' => $component->id]);
    }

    // ---------------------------------------------------------------------------
    // Consumables
    // ---------------------------------------------------------------------------

    public function test_consumable_is_soft_deleted(): void
    {
        $admin = User::factory()->superuser()->create();
        $company = Company::factory()->create();
        $consumable = Consumable::factory()->for($company)->create();

        $this->runCommand($admin, [$company->id], ['consumables'])
            ->assertExitCode(0);

        $this->assertSoftDeleted($consumable);
    }

    public function test_consumable_is_hard_deleted(): void
    {
        $admin = User::factory()->superuser()->create();
        $company = Company::factory()->create();
        $consumable = Consumable::factory()->for($company)->create();

        $this->runCommand($admin, [$company->id], ['consumables'], deleteType: 'hard')
            ->assertExitCode(0);

        $this->assertDatabaseMissing('consumables', ['id' => $consumable->id]);
    }

    // ---------------------------------------------------------------------------
    // Users
    // ---------------------------------------------------------------------------

    public function test_user_is_soft_deleted(): void
    {
        $admin = User::factory()->superuser()->create();
        $company = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $company->id, 'activated' => 1]);

        $this->runCommand($admin, [$company->id], ['users'])
            ->assertExitCode(0);

        $this->assertSoftDeleted($user);
    }

    public function test_user_delete_nulls_license_seat_assignments(): void
    {
        $admin = User::factory()->superuser()->create();
        $company = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $company->id, 'activated' => 1]);
        $seat = LicenseSeat::factory()->create(['assigned_to' => $user->id]);

        $this->runCommand($admin, [$company->id], ['users'])
            ->assertExitCode(0);

        $this->assertNull($seat->fresh()->assigned_to);
    }

    public function test_user_delete_removes_accessory_checkout_records(): void
    {
        $admin = User::factory()->superuser()->create();
        $company = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $company->id, 'activated' => 1]);
        Accessory::factory()->for($company)->checkedOutToUser($user)->create();

        $this->assertDatabaseHas('accessories_checkout', ['assigned_to' => $user->id]);

        $this->runCommand($admin, [$company->id], ['users'])
            ->assertExitCode(0);

        $this->assertDatabaseMissing('accessories_checkout', ['assigned_to' => $user->id]);
    }

    public function test_user_delete_removes_consumable_assignments(): void
    {
        $admin = User::factory()->superuser()->create();
        $company = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $company->id, 'activated' => 1]);
        Consumable::factory()->for($company)->checkedOutToUser($user)->create();

        $this->assertDatabaseHas('consumables_users', ['assigned_to' => $user->id]);

        $this->runCommand($admin, [$company->id], ['users'])
            ->assertExitCode(0);

        $this->assertDatabaseMissing('consumables_users', ['assigned_to' => $user->id]);
    }

    public function test_user_delete_removes_checkout_acceptances(): void
    {
        $admin = User::factory()->superuser()->create();
        $company = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $company->id, 'activated' => 1]);

        CheckoutAcceptance::factory()->create(['assigned_to_id' => $user->id]);

        $this->runCommand($admin, [$company->id], ['users'])
            ->assertExitCode(0);

        $this->assertDatabaseMissing('checkout_acceptances', ['assigned_to_id' => $user->id]);
    }

    public function test_admin_user_is_skipped_during_user_deletion(): void
    {
        $company = Company::factory()->create();
        $admin = User::factory()->superuser()->create(['company_id' => $company->id, 'activated' => 1]);

        $this->runCommand($admin, [$company->id], ['users'])
            ->assertExitCode(0);

        $this->assertDatabaseHas('users', ['id' => $admin->id, 'deleted_at' => null]);
    }

    // ---------------------------------------------------------------------------
    // Action log clearing
    // ---------------------------------------------------------------------------

    public function test_clear_logs_removes_asset_action_logs(): void
    {
        $admin = User::factory()->superuser()->create();
        $company = Company::factory()->create();
        $user = User::factory()->create();
        $asset = Asset::factory()->for($company)->assignedToUser($user)->create();

        $this->runCommand($admin, [$company->id], ['assets'], clearLogs: true)
            ->assertExitCode(0);

        $this->assertDatabaseMissing('action_logs', [
            'item_type' => Asset::class,
            'item_id' => $asset->id,
        ]);
    }

    // ---------------------------------------------------------------------------
    // Maintenance / related table cleanup
    // ---------------------------------------------------------------------------

    public function test_asset_hard_delete_removes_maintenance_records(): void
    {
        $admin = User::factory()->superuser()->create();
        $company = Company::factory()->create();
        $asset = Asset::factory()->for($company)->create();
        $maintenance = Maintenance::factory()->create(['asset_id' => $asset->id]);

        $this->runCommand($admin, [$company->id], ['assets'], deleteType: 'hard')
            ->assertExitCode(0);

        $this->assertDatabaseMissing('maintenances', ['id' => $maintenance->id]);
    }

    public function test_asset_hard_delete_removes_accessory_checkouts_to_asset(): void
    {
        $admin = User::factory()->superuser()->create();
        $company = Company::factory()->create();
        $asset = Asset::factory()->for($company)->create();
        $accessory = Accessory::factory()->for($company)->create();
        $accessory->checkouts()->create([
            'accessory_id' => $accessory->id,
            'assigned_to' => $asset->id,
            'assigned_type' => Asset::class,
            'created_by' => $admin->id,
        ]);

        $this->assertDatabaseHas('accessories_checkout', [
            'assigned_to' => $asset->id,
            'assigned_type' => Asset::class,
        ]);

        $this->runCommand($admin, [$company->id], ['assets'], deleteType: 'hard')
            ->assertExitCode(0);

        $this->assertDatabaseMissing('accessories_checkout', [
            'assigned_to' => $asset->id,
            'assigned_type' => Asset::class,
        ]);
    }

    public function test_asset_hard_delete_nulls_child_asset_assignments(): void
    {
        $admin = User::factory()->superuser()->create();
        $company = Company::factory()->create();
        $target = Asset::factory()->for($company)->create();
        $child = Asset::factory()->create(['assigned_to' => $target->id, 'assigned_type' => Asset::class]);

        $this->runCommand($admin, [$company->id], ['assets'], deleteType: 'hard')
            ->assertExitCode(0);

        $this->assertNull($child->fresh()->assigned_to);
        $this->assertNull($child->fresh()->assigned_type);
    }

    public function test_license_hard_delete_removes_kit_entries(): void
    {
        $admin = User::factory()->superuser()->create();
        $company = Company::factory()->create();
        $license = License::factory()->for($company)->create();
        DB::table('kits_licenses')->insert(['kit_id' => 1, 'license_id' => $license->id, 'quantity' => 1]);

        $this->runCommand($admin, [$company->id], ['licenses'], deleteType: 'hard')
            ->assertExitCode(0);

        $this->assertDatabaseMissing('kits_licenses', ['license_id' => $license->id]);
    }

    public function test_accessory_hard_delete_removes_kit_entries(): void
    {
        $admin = User::factory()->superuser()->create();
        $company = Company::factory()->create();
        $accessory = Accessory::factory()->for($company)->create();
        DB::table('kits_accessories')->insert(['kit_id' => 1, 'accessory_id' => $accessory->id, 'quantity' => 1]);

        $this->runCommand($admin, [$company->id], ['accessories'], deleteType: 'hard')
            ->assertExitCode(0);

        $this->assertDatabaseMissing('kits_accessories', ['accessory_id' => $accessory->id]);
    }

    public function test_consumable_hard_delete_removes_kit_entries(): void
    {
        $admin = User::factory()->superuser()->create();
        $company = Company::factory()->create();
        $consumable = Consumable::factory()->for($company)->create();
        DB::table('kits_consumables')->insert(['kit_id' => 1, 'consumable_id' => $consumable->id, 'quantity' => 1]);

        $this->runCommand($admin, [$company->id], ['consumables'], deleteType: 'hard')
            ->assertExitCode(0);

        $this->assertDatabaseMissing('kits_consumables', ['consumable_id' => $consumable->id]);
    }

    // ---------------------------------------------------------------------------
    // Dry run
    // ---------------------------------------------------------------------------

    public function test_dry_run_does_not_delete_or_checkin_assets(): void
    {
        $admin = User::factory()->superuser()->create();
        $company = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $company->id]);
        $asset = Asset::factory()->for($company)->assignedToUser($user)->create();

        $this->runCommand($admin, [$company->id], ['assets'], dryRun: true)
            ->assertExitCode(0);

        $this->assertNotSoftDeleted($asset);
        $this->assertNotNull($asset->fresh()->assigned_to);
    }

    // ---------------------------------------------------------------------------
    // Final confirm / abort
    // ---------------------------------------------------------------------------

    public function test_declining_final_confirm_makes_no_changes(): void
    {
        $admin = User::factory()->superuser()->create();
        $company = Company::factory()->create();
        $asset = Asset::factory()->for($company)->create();

        $this->runCommand($admin, [$company->id], ['assets'], confirm: false)
            ->assertExitCode(0);

        $this->assertNotSoftDeleted($asset);
    }

    // ---------------------------------------------------------------------------
    // Multi-company / checkin-only mode
    // ---------------------------------------------------------------------------

    public function test_multiple_companies_are_all_processed(): void
    {
        $admin = User::factory()->superuser()->create();
        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $assetA = Asset::factory()->for($companyA)->create();
        $assetB = Asset::factory()->for($companyB)->create();
        $assetC = Asset::factory()->create(); // no company — should be untouched

        $this->runCommand($admin, [$companyA->id, $companyB->id], ['assets'])
            ->assertExitCode(0);

        $this->assertSoftDeleted($assetA);
        $this->assertSoftDeleted($assetB);
        $this->assertNotSoftDeleted($assetC);
    }

    public function test_checkin_only_mode_checks_in_without_deleting(): void
    {
        $admin = User::factory()->superuser()->create();
        $company = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $company->id]);
        $asset = Asset::factory()->for($company)->assignedToUser($user)->create();

        $this->runCommand($admin, [$company->id], ['assets'], deleteType: 'none')
            ->assertExitCode(0);

        $this->assertNotSoftDeleted($asset);
        $this->assertNull($asset->fresh()->assigned_to);
    }

    // ---------------------------------------------------------------------------
    // Company deletion
    // ---------------------------------------------------------------------------

    public function test_company_is_soft_deleted_when_requested(): void
    {
        $admin = User::factory()->superuser()->create();
        $company = Company::factory()->create();
        Asset::factory()->for($company)->create();

        $this->runCommand($admin, [$company->id], ['assets'], deleteCompanyType: 'soft')
            ->assertExitCode(0);

        $this->assertSoftDeleted($company);
    }

    public function test_company_is_hard_deleted_when_requested(): void
    {
        $admin = User::factory()->superuser()->create();
        $company = Company::factory()->create();
        Asset::factory()->for($company)->create();

        $this->runCommand($admin, [$company->id], ['assets'], deleteCompanyType: 'hard')
            ->assertExitCode(0);

        $this->assertDatabaseMissing('companies', ['id' => $company->id]);
    }

    public function test_company_is_kept_when_not_requested(): void
    {
        $admin = User::factory()->superuser()->create();
        $company = Company::factory()->create();
        Asset::factory()->for($company)->create();

        $this->runCommand($admin, [$company->id], ['assets'], deleteCompanyType: 'keep')
            ->assertExitCode(0);

        $this->assertDatabaseHas('companies', ['id' => $company->id, 'deleted_at' => null]);
    }

    // ---------------------------------------------------------------------------
    // Multi-company user handling
    // ---------------------------------------------------------------------------

    public function test_user_in_multiple_companies_is_partially_disassociated(): void
    {
        $admin = User::factory()->superuser()->create();
        [$companyA, $companyB] = Company::factory()->count(2)->create();
        $user = User::factory()->create(['company_id' => $companyA->id, 'activated' => 1]);
        $user->companies()->syncWithoutDetaching([$companyA->id, $companyB->id]);

        $this->runCommand($admin, [$companyA->id], ['users'])
            ->assertExitCode(0);

        // User should NOT have been deleted
        $this->assertDatabaseHas('users', ['id' => $user->id, 'deleted_at' => null]);
        // companyA pivot entry should be removed
        $this->assertDatabaseMissing('company_user', ['user_id' => $user->id, 'company_id' => $companyA->id]);
        // companyB pivot entry should remain
        $this->assertDatabaseHas('company_user', ['user_id' => $user->id, 'company_id' => $companyB->id]);
    }

    public function test_user_in_only_selected_company_is_fully_deleted(): void
    {
        $admin = User::factory()->superuser()->create();
        $company = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $company->id, 'activated' => 1]);
        $user->companies()->syncWithoutDetaching([$company->id]);

        $this->runCommand($admin, [$company->id], ['users'])
            ->assertExitCode(0);

        $this->assertSoftDeleted($user);
    }

    public function test_user_hard_delete_removes_company_user_pivot_entries(): void
    {
        $admin = User::factory()->superuser()->create();
        $company = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $company->id, 'activated' => 1]);
        $user->companies()->syncWithoutDetaching([$company->id]);

        $this->assertDatabaseHas('company_user', ['user_id' => $user->id, 'company_id' => $company->id]);

        $this->runCommand($admin, [$company->id], ['users'], deleteType: 'hard')
            ->assertExitCode(0);

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertDatabaseMissing('company_user', ['user_id' => $user->id]);
    }

    // ---------------------------------------------------------------------------
    // Email report
    // ---------------------------------------------------------------------------

    public function test_email_report_is_sent_when_requested(): void
    {
        Mail::fake();

        $admin = User::factory()->superuser()->create();
        $company = Company::factory()->create();
        Asset::factory()->for($company)->create();

        $this->runCommand($admin, [$company->id], ['assets'], sendEmailReport: true)
            ->assertExitCode(0);

        Mail::assertSent(BulkDeleteReportMail::class, fn ($mail) => $mail->hasTo($admin->email));
    }

    public function test_email_report_is_not_sent_when_declined(): void
    {
        Mail::fake();

        $admin = User::factory()->superuser()->create();
        $company = Company::factory()->create();
        Asset::factory()->for($company)->create();

        $this->runCommand($admin, [$company->id], ['assets'], sendEmailReport: false)
            ->assertExitCode(0);

        Mail::assertNothingSent();
    }
}
