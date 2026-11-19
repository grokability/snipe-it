<?php

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
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\PendingCommand;

beforeEach(function () {
    $this->runCommand = function (
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
        string $deleteCompanyType = 'keep'
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
    };
});

test('assigned asset is checked in and soft deleted', function () {
    $admin = User::factory()->superuser()->create();
    $company = Company::factory()->create();
    $user = User::factory()->forCompany($company->id)->create();
    $asset = Asset::factory()->for($company)->assignedToUser($user)->create();

    expect($asset->assigned_to)->not->toBeNull();

    ($this->runCommand)($admin, [$company->id], ['assets'])
        ->assertExitCode(0);

    $this->assertSoftDeleted($asset);
    expect($asset->fresh()->assigned_to)->toBeNull();
});

test('unassigned asset is soft deleted', function () {
    $admin = User::factory()->superuser()->create();
    $company = Company::factory()->create();
    $asset = Asset::factory()->for($company)->create();

    ($this->runCommand)($admin, [$company->id], ['assets'])
        ->assertExitCode(0);

    $this->assertSoftDeleted($asset);
});

test('asset is hard deleted', function () {
    $admin = User::factory()->superuser()->create();
    $company = Company::factory()->create();
    $asset = Asset::factory()->for($company)->assignedToUser()->create();

    ($this->runCommand)($admin, [$company->id], ['assets'], deleteType: 'hard')
        ->assertExitCode(0);

    $this->assertDatabaseMissing('assets', ['id' => $asset->id]);
});

test('asset checkin event fired when notifications enabled', function () {
    Event::fake([CheckoutableCheckedIn::class]);

    $admin = User::factory()->superuser()->create();
    $company = Company::factory()->create();
    $asset = Asset::factory()->for($company)->assignedToUser()->create();

    ($this->runCommand)($admin, [$company->id], ['assets'], sendNotifications: true)
        ->assertExitCode(0);

    Event::assertDispatched(CheckoutableCheckedIn::class, fn ($e) => $e->checkoutable->is($asset));
});

test('asset checkin event not fired when notifications suppressed', function () {
    Event::fake([CheckoutableCheckedIn::class]);

    $admin = User::factory()->superuser()->create();
    $company = Company::factory()->create();
    Asset::factory()->for($company)->assignedToUser()->create();

    ($this->runCommand)($admin, [$company->id], ['assets'], sendNotifications: false)
        ->assertExitCode(0);

    Event::assertNotDispatched(CheckoutableCheckedIn::class);
});

test('asset scoped to correct company', function () {
    $admin = User::factory()->superuser()->create();
    [$companyA, $companyB] = Company::factory()->count(2)->create();

    $assetA = Asset::factory()->for($companyA)->create();
    $assetB = Asset::factory()->for($companyB)->create();

    ($this->runCommand)($admin, [$companyA->id], ['assets'])
        ->assertExitCode(0);

    $this->assertSoftDeleted($assetA);
    $this->assertNotSoftDeleted($assetB);
});

test('asset delete clears all checkout acceptances', function () {
    $admin = User::factory()->superuser()->create();
    $company = Company::factory()->create();
    $asset = Asset::factory()->for($company)->create();

    CheckoutAcceptance::factory()->create([
        'checkoutable_type' => Asset::class,
        'checkoutable_id' => $asset->id,
    ]);

    ($this->runCommand)($admin, [$company->id], ['assets'])
        ->assertExitCode(0);

    $this->assertDatabaseMissing('checkout_acceptances', [
        'checkoutable_type' => Asset::class,
        'checkoutable_id' => $asset->id,
    ]);
});

test('asset checkin clears linked license seats', function () {
    $admin = User::factory()->superuser()->create();
    $company = Company::factory()->create();
    $user = User::factory()->forCompany($company->id)->create();
    $asset = Asset::factory()->for($company)->assignedToUser($user)->create();
    $seat = LicenseSeat::factory()->create(['asset_id' => $asset->id, 'assigned_to' => $user->id]);

    ($this->runCommand)($admin, [$company->id], ['assets'])
        ->assertExitCode(0);

    expect($seat->fresh()->assigned_to)->toBeNull();
});

test('assigned license seat is checked in and license soft deleted', function () {
    $admin = User::factory()->superuser()->create();
    $company = Company::factory()->create();
    $license = License::factory()->for($company)->create();
    $seat = LicenseSeat::factory()->for($license)->assignedToUser()->create();

    expect($seat->assigned_to)->not->toBeNull();

    ($this->runCommand)($admin, [$company->id], ['licenses'])
        ->assertExitCode(0);

    $this->assertSoftDeleted($license);
    expect($seat->fresh()->assigned_to)->toBeNull();
    expect($seat->fresh()->asset_id)->toBeNull();
});

test('license hard delete removes seats and their checkout acceptances', function () {
    $admin = User::factory()->superuser()->create();
    $company = Company::factory()->create();
    $license = License::factory()->for($company)->create();
    $seat = LicenseSeat::factory()->for($license)->assignedToUser()->create();

    CheckoutAcceptance::factory()->create([
        'checkoutable_type' => LicenseSeat::class,
        'checkoutable_id' => $seat->id,
    ]);

    ($this->runCommand)($admin, [$company->id], ['licenses'], deleteType: 'hard')
        ->assertExitCode(0);

    $this->assertDatabaseMissing('licenses', ['id' => $license->id]);
    $this->assertDatabaseMissing('license_seats', ['id' => $seat->id]);
    $this->assertDatabaseMissing('checkout_acceptances', [
        'checkoutable_type' => LicenseSeat::class,
        'checkoutable_id' => $seat->id,
    ]);
});

test('license soft delete also soft deletes seats', function () {
    $admin = User::factory()->superuser()->create();
    $company = Company::factory()->create();
    $license = License::factory()->for($company)->create();
    $seat = LicenseSeat::factory()->for($license)->create();

    ($this->runCommand)($admin, [$company->id], ['licenses'])
        ->assertExitCode(0);

    $this->assertSoftDeleted($license);
    $this->assertSoftDeleted($seat);
});

test('license scoped to correct company', function () {
    $admin = User::factory()->superuser()->create();
    [$companyA, $companyB] = Company::factory()->count(2)->create();

    $licenseA = License::factory()->for($companyA)->create();
    $licenseB = License::factory()->for($companyB)->create();

    ($this->runCommand)($admin, [$companyA->id], ['licenses'])
        ->assertExitCode(0);

    $this->assertSoftDeleted($licenseA);
    $this->assertNotSoftDeleted($licenseB);
});

test('checked out accessory is checked in and soft deleted', function () {
    $admin = User::factory()->superuser()->create();
    $company = Company::factory()->create();
    $accessory = Accessory::factory()->for($company)->checkedOutToUser()->create();

    expect($accessory->checkouts->count())->toEqual(1);

    ($this->runCommand)($admin, [$company->id], ['accessories'])
        ->assertExitCode(0);

    $this->assertSoftDeleted($accessory);
    expect($accessory->fresh()->checkouts->count())->toEqual(0);
});

test('accessory scoped to correct company', function () {
    $admin = User::factory()->superuser()->create();
    [$companyA, $companyB] = Company::factory()->count(2)->create();

    $accessoryA = Accessory::factory()->for($companyA)->checkedOutToUser()->create();
    $accessoryB = Accessory::factory()->for($companyB)->checkedOutToUser()->create();

    ($this->runCommand)($admin, [$companyA->id], ['accessories'])
        ->assertExitCode(0);

    $this->assertSoftDeleted($accessoryA);
    $this->assertNotSoftDeleted($accessoryB);
});

test('checked out component is checked in and soft deleted', function () {
    $admin = User::factory()->superuser()->create();
    $company = Company::factory()->create();
    $component = Component::factory()->for($company)->checkedOutToAsset()->create();

    $this->assertDatabaseHas('components_assets', ['component_id' => $component->id]);

    ($this->runCommand)($admin, [$company->id], ['components'])
        ->assertExitCode(0);

    $this->assertSoftDeleted($component);
    $this->assertDatabaseMissing('components_assets', ['component_id' => $component->id]);
});

test('consumable is soft deleted', function () {
    $admin = User::factory()->superuser()->create();
    $company = Company::factory()->create();
    $consumable = Consumable::factory()->for($company)->create();

    ($this->runCommand)($admin, [$company->id], ['consumables'])
        ->assertExitCode(0);

    $this->assertSoftDeleted($consumable);
});

test('consumable is hard deleted', function () {
    $admin = User::factory()->superuser()->create();
    $company = Company::factory()->create();
    $consumable = Consumable::factory()->for($company)->create();

    ($this->runCommand)($admin, [$company->id], ['consumables'], deleteType: 'hard')
        ->assertExitCode(0);

    $this->assertDatabaseMissing('consumables', ['id' => $consumable->id]);
});

test('user is soft deleted', function () {
    $admin = User::factory()->superuser()->create();
    $company = Company::factory()->create();
    $user = User::factory()->forCompany($company->id)->create(['activated' => 1]);

    ($this->runCommand)($admin, [$company->id], ['users'])
        ->assertExitCode(0);

    $this->assertSoftDeleted($user);
});

test('user delete nulls license seat assignments', function () {
    $admin = User::factory()->superuser()->create();
    $company = Company::factory()->create();
    $user = User::factory()->forCompany($company->id)->create(['activated' => 1]);
    $seat = LicenseSeat::factory()->create(['assigned_to' => $user->id]);

    ($this->runCommand)($admin, [$company->id], ['users'])
        ->assertExitCode(0);

    expect($seat->fresh()->assigned_to)->toBeNull();
});

test('user delete removes accessory checkout records', function () {
    $admin = User::factory()->superuser()->create();
    $company = Company::factory()->create();
    $user = User::factory()->forCompany($company->id)->create(['activated' => 1]);
    Accessory::factory()->for($company)->checkedOutToUser($user)->create();

    $this->assertDatabaseHas('accessories_checkout', ['assigned_to' => $user->id]);

    ($this->runCommand)($admin, [$company->id], ['users'])
        ->assertExitCode(0);

    $this->assertDatabaseMissing('accessories_checkout', ['assigned_to' => $user->id]);
});

test('user delete removes consumable assignments', function () {
    $admin = User::factory()->superuser()->create();
    $company = Company::factory()->create();
    $user = User::factory()->forCompany($company->id)->create(['activated' => 1]);
    Consumable::factory()->for($company)->checkedOutToUser($user)->create();

    $this->assertDatabaseHas('consumables_users', ['assigned_to' => $user->id]);

    ($this->runCommand)($admin, [$company->id], ['users'])
        ->assertExitCode(0);

    $this->assertDatabaseMissing('consumables_users', ['assigned_to' => $user->id]);
});

test('user delete removes checkout acceptances', function () {
    $admin = User::factory()->superuser()->create();
    $company = Company::factory()->create();
    $user = User::factory()->forCompany($company->id)->create(['activated' => 1]);

    CheckoutAcceptance::factory()->create(['assigned_to_id' => $user->id]);

    ($this->runCommand)($admin, [$company->id], ['users'])
        ->assertExitCode(0);

    $this->assertDatabaseMissing('checkout_acceptances', ['assigned_to_id' => $user->id]);
});

test('admin user is skipped during user deletion', function () {
    $company = Company::factory()->create();
    $admin = User::factory()->superuser()->forCompany($company->id)->create(['activated' => 1]);

    ($this->runCommand)($admin, [$company->id], ['users'])
        ->assertExitCode(0);

    $this->assertDatabaseHas('users', ['id' => $admin->id, 'deleted_at' => null]);
});

test('user avatar delete cannot escape avatars prefix', function () {
    Storage::fake('public');
    Storage::disk('public')->put('barcodes/target.png', 'legitimate barcode asset');
    Storage::disk('public')->put('avatars/target.png', 'legitimate avatar file');

    $admin = User::factory()->superuser()->create();
    $company = Company::factory()->create();
    $user = User::factory()->forCompany($company->id)->create([
        'activated' => 1,
        'avatar' => 'https://x/../../../barcodes/target.png',
    ]);

    ($this->runCommand)($admin, [$company->id], ['users'], deleteFiles: true)
        ->assertExitCode(0);

    Storage::disk('public')->assertExists('barcodes/target.png');
});

test('user delete does not touch disk for external avatar', function () {
    Storage::fake('public');
    Storage::disk('public')->put('avatars/photo.jpg', 'legitimate other-user avatar');

    $admin = User::factory()->superuser()->create();
    $company = Company::factory()->create();
    $user = User::factory()->forCompany($company->id)->create([
        'activated' => 1,
        'avatar' => 'https://lh3.googleusercontent.com/a/some-path/photo.jpg',
    ]);

    ($this->runCommand)($admin, [$company->id], ['users'], deleteFiles: true)
        ->assertExitCode(0);

    Storage::disk('public')->assertExists('avatars/photo.jpg');
});

test('user delete removes legitimate avatar file', function () {
    Storage::fake('public');
    Storage::disk('public')->put('avatars/user-42.png', 'legitimate avatar');

    $admin = User::factory()->superuser()->create();
    $company = Company::factory()->create();
    $user = User::factory()->forCompany($company->id)->create([
        'activated' => 1,
        'avatar' => 'user-42.png',
    ]);

    ($this->runCommand)($admin, [$company->id], ['users'], deleteFiles: true)
        ->assertExitCode(0);

    Storage::disk('public')->assertMissing('avatars/user-42.png');
});

test('clear logs removes asset action logs', function () {
    $admin = User::factory()->superuser()->create();
    $company = Company::factory()->create();
    $user = User::factory()->create();
    $asset = Asset::factory()->for($company)->assignedToUser($user)->create();

    ($this->runCommand)($admin, [$company->id], ['assets'], clearLogs: true)
        ->assertExitCode(0);

    $this->assertDatabaseMissing('action_logs', [
        'item_type' => Asset::class,
        'item_id' => $asset->id,
    ]);
});

test('asset hard delete removes maintenance records', function () {
    $admin = User::factory()->superuser()->create();
    $company = Company::factory()->create();
    $asset = Asset::factory()->for($company)->create();
    $maintenance = Maintenance::factory()->create(['asset_id' => $asset->id]);

    ($this->runCommand)($admin, [$company->id], ['assets'], deleteType: 'hard')
        ->assertExitCode(0);

    $this->assertDatabaseMissing('maintenances', ['id' => $maintenance->id]);
});

test('asset hard delete removes accessory checkouts to asset', function () {
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

    ($this->runCommand)($admin, [$company->id], ['assets'], deleteType: 'hard')
        ->assertExitCode(0);

    $this->assertDatabaseMissing('accessories_checkout', [
        'assigned_to' => $asset->id,
        'assigned_type' => Asset::class,
    ]);
});

test('asset hard delete nulls child asset assignments', function () {
    $admin = User::factory()->superuser()->create();
    $company = Company::factory()->create();
    $target = Asset::factory()->for($company)->create();
    $child = Asset::factory()->create(['assigned_to' => $target->id, 'assigned_type' => Asset::class]);

    ($this->runCommand)($admin, [$company->id], ['assets'], deleteType: 'hard')
        ->assertExitCode(0);

    expect($child->fresh()->assigned_to)->toBeNull();
    expect($child->fresh()->assigned_type)->toBeNull();
});

test('license hard delete removes kit entries', function () {
    $admin = User::factory()->superuser()->create();
    $company = Company::factory()->create();
    $license = License::factory()->for($company)->create();
    DB::table('kits_licenses')->insert(['kit_id' => 1, 'license_id' => $license->id, 'quantity' => 1]);

    ($this->runCommand)($admin, [$company->id], ['licenses'], deleteType: 'hard')
        ->assertExitCode(0);

    $this->assertDatabaseMissing('kits_licenses', ['license_id' => $license->id]);
});

test('accessory hard delete removes kit entries', function () {
    $admin = User::factory()->superuser()->create();
    $company = Company::factory()->create();
    $accessory = Accessory::factory()->for($company)->create();
    DB::table('kits_accessories')->insert(['kit_id' => 1, 'accessory_id' => $accessory->id, 'quantity' => 1]);

    ($this->runCommand)($admin, [$company->id], ['accessories'], deleteType: 'hard')
        ->assertExitCode(0);

    $this->assertDatabaseMissing('kits_accessories', ['accessory_id' => $accessory->id]);
});

test('consumable hard delete removes kit entries', function () {
    $admin = User::factory()->superuser()->create();
    $company = Company::factory()->create();
    $consumable = Consumable::factory()->for($company)->create();
    DB::table('kits_consumables')->insert(['kit_id' => 1, 'consumable_id' => $consumable->id, 'quantity' => 1]);

    ($this->runCommand)($admin, [$company->id], ['consumables'], deleteType: 'hard')
        ->assertExitCode(0);

    $this->assertDatabaseMissing('kits_consumables', ['consumable_id' => $consumable->id]);
});

test('dry run does not delete or checkin assets', function () {
    $admin = User::factory()->superuser()->create();
    $company = Company::factory()->create();
    $user = User::factory()->forCompany($company->id)->create();
    $asset = Asset::factory()->for($company)->assignedToUser($user)->create();

    ($this->runCommand)($admin, [$company->id], ['assets'], dryRun: true)
        ->assertExitCode(0);

    $this->assertNotSoftDeleted($asset);
    expect($asset->fresh()->assigned_to)->not->toBeNull();
});

test('declining final confirm makes no changes', function () {
    $admin = User::factory()->superuser()->create();
    $company = Company::factory()->create();
    $asset = Asset::factory()->for($company)->create();

    ($this->runCommand)($admin, [$company->id], ['assets'], confirm: false)
        ->assertExitCode(0);

    $this->assertNotSoftDeleted($asset);
});

test('multiple companies are all processed', function () {
    $admin = User::factory()->superuser()->create();
    [$companyA, $companyB] = Company::factory()->count(2)->create();

    $assetA = Asset::factory()->for($companyA)->create();
    $assetB = Asset::factory()->for($companyB)->create();
    $assetC = Asset::factory()->create(); // no company — should be untouched

    ($this->runCommand)($admin, [$companyA->id, $companyB->id], ['assets'])
        ->assertExitCode(0);

    $this->assertSoftDeleted($assetA);
    $this->assertSoftDeleted($assetB);
    $this->assertNotSoftDeleted($assetC);
});

test('checkin only mode checks in without deleting', function () {
    $admin = User::factory()->superuser()->create();
    $company = Company::factory()->create();
    $user = User::factory()->forCompany($company->id)->create();
    $asset = Asset::factory()->for($company)->assignedToUser($user)->create();

    ($this->runCommand)($admin, [$company->id], ['assets'], deleteType: 'none')
        ->assertExitCode(0);

    $this->assertNotSoftDeleted($asset);
    expect($asset->fresh()->assigned_to)->toBeNull();
});

test('company is soft deleted when requested', function () {
    $admin = User::factory()->superuser()->create();
    $company = Company::factory()->create();
    Asset::factory()->for($company)->create();

    ($this->runCommand)($admin, [$company->id], ['assets'], deleteCompanyType: 'soft')
        ->assertExitCode(0);

    $this->assertSoftDeleted($company);
});

test('company is hard deleted when requested', function () {
    $admin = User::factory()->superuser()->create();
    $company = Company::factory()->create();
    Asset::factory()->for($company)->create();

    ($this->runCommand)($admin, [$company->id], ['assets'], deleteCompanyType: 'hard')
        ->assertExitCode(0);

    $this->assertDatabaseMissing('companies', ['id' => $company->id]);
});

test('company is kept when not requested', function () {
    $admin = User::factory()->superuser()->create();
    $company = Company::factory()->create();
    Asset::factory()->for($company)->create();

    ($this->runCommand)($admin, [$company->id], ['assets'], deleteCompanyType: 'keep')
        ->assertExitCode(0);

    $this->assertDatabaseHas('companies', ['id' => $company->id, 'deleted_at' => null]);
});

test('user in multiple companies is partially disassociated', function () {
    $admin = User::factory()->superuser()->create();
    [$companyA, $companyB] = Company::factory()->count(2)->create();
    $user = User::factory()->forCompany($companyA->id)->create(['activated' => 1]);
    $user->companies()->syncWithoutDetaching([$companyA->id, $companyB->id]);

    ($this->runCommand)($admin, [$companyA->id], ['users'])
        ->assertExitCode(0);

    // User should NOT have been deleted
    $this->assertDatabaseHas('users', ['id' => $user->id, 'deleted_at' => null]);

    // companyA pivot entry should be removed
    $this->assertDatabaseMissing('company_user', ['user_id' => $user->id, 'company_id' => $companyA->id]);

    // companyB pivot entry should remain
    $this->assertDatabaseHas('company_user', ['user_id' => $user->id, 'company_id' => $companyB->id]);
});

test('user in only selected company is fully deleted', function () {
    $admin = User::factory()->superuser()->create();
    $company = Company::factory()->create();
    $user = User::factory()->forCompany($company->id)->create(['activated' => 1]);
    $user->companies()->syncWithoutDetaching([$company->id]);

    ($this->runCommand)($admin, [$company->id], ['users'])
        ->assertExitCode(0);

    $this->assertSoftDeleted($user);
});

test('user hard delete removes company user pivot entries', function () {
    $admin = User::factory()->superuser()->create();
    $company = Company::factory()->create();
    $user = User::factory()->forCompany($company->id)->create(['activated' => 1]);
    $user->companies()->syncWithoutDetaching([$company->id]);

    $this->assertDatabaseHas('company_user', ['user_id' => $user->id, 'company_id' => $company->id]);

    ($this->runCommand)($admin, [$company->id], ['users'], deleteType: 'hard')
        ->assertExitCode(0);

    $this->assertDatabaseMissing('users', ['id' => $user->id]);
    $this->assertDatabaseMissing('company_user', ['user_id' => $user->id]);
});

test('email report is sent when requested', function () {
    Mail::fake();

    $admin = User::factory()->superuser()->create();
    $company = Company::factory()->create();
    Asset::factory()->for($company)->create();

    ($this->runCommand)($admin, [$company->id], ['assets'], sendEmailReport: true)
        ->assertExitCode(0);

    Mail::assertSent(BulkDeleteReportMail::class, fn ($mail) => $mail->hasTo($admin->email));
});

test('email report is not sent when declined', function () {
    Mail::fake();

    $admin = User::factory()->superuser()->create();
    $company = Company::factory()->create();
    Asset::factory()->for($company)->create();

    ($this->runCommand)($admin, [$company->id], ['assets'], sendEmailReport: false)
        ->assertExitCode(0);

    Mail::assertNothingSent();
});
