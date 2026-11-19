<?php

use App\Models\Asset;
use App\Models\Company;
use App\Models\User;
use Illuminate\Testing\PendingCommand;

beforeEach(function () {
    $this->runCommand = function (User $admin, array $companySelectionKeys, array $types, string $deleteType): PendingCommand {
        $searchLabel = 'Who are you? Search by username, first or last name.';
        $companiesLabel = 'Which companies would you like to check in and delete items for?';
        $typesLabel = 'What item types would you like to check in and delete?';
        $deleteLabel = 'How should items be deleted?';

        $hasNotifiable = ! empty(array_intersect($types, ['assets', 'licenses', 'accessories', 'components']));

        $cmd = $this->artisan('snipeit:checkin-delete-items')
            ->expectsConfirmation('Is this a dry run?', 'no')
            ->expectsQuestion($searchLabel, $admin->username)
            ->expectsQuestion($searchLabel, (string) $admin->id)
            ->expectsQuestion($companiesLabel, '')
            ->expectsQuestion($companiesLabel, $companySelectionKeys)
            ->expectsQuestion($typesLabel, $types)
            ->expectsQuestion($deleteLabel, $deleteType);

        if ($hasNotifiable) {
            $cmd->expectsConfirmation('Should we send checkin notifications?', 'no');
        }

        $cmd->expectsConfirmation('Should we clear related action logs?', 'no')
            ->expectsConfirmation('Should we also delete associated image and upload files?', 'no');

        // The company-deletion prompt only appears when real company ids
        // (not just __null__) end up in scope. __all__ expands to real
        // company ids, so the prompt does render for that case.
        $expandsToRealCompanies = in_array('__all__', $companySelectionKeys, true)
            || count(array_filter($companySelectionKeys, fn ($k) => is_int($k))) > 0;
        if ($expandsToRealCompanies) {
            $cmd->expectsQuestion('Should the selected companies also be deleted?', 'keep');
        }

        $cmd->expectsConfirmation('Should we run a backup before proceeding?', 'no');

        if ($admin->email) {
            $cmd->expectsConfirmation("Send an email report to {$admin->email}?", 'no');
        }

        return $cmd->expectsConfirmation('Are you sure you want to proceed? This cannot be undone.', 'yes');
    };
});

test('all companies sentinel covers every company plus unassigned', function () {
    $admin = User::factory()->superuser()->create();
    [$companyA, $companyB] = Company::factory()->count(2)->create();

    $assetA = Asset::factory()->for($companyA)->create();
    $assetB = Asset::factory()->for($companyB)->create();
    $orphanAsset = Asset::factory()->create(['company_id' => null]);

    ($this->runCommand)($admin, ['__all__'], ['assets'], 'soft')->assertExitCode(0);

    $this->assertSoftDeleted($assetA);
    $this->assertSoftDeleted($assetB);
    $this->assertSoftDeleted($orphanAsset);
});

test('all companies sentinel wins when combined with specific picks', function () {
    // Selecting __all__ alongside a specific company id shouldn't
    // narrow the scope. The __all__ branch resets the key list to
    // every company plus __null__, so the specific pick becomes a
    // no-op rather than a filter.
    $admin = User::factory()->superuser()->create();
    [$companyA, $companyB] = Company::factory()->count(2)->create();

    $assetA = Asset::factory()->for($companyA)->create();
    $assetB = Asset::factory()->for($companyB)->create();
    $orphanAsset = Asset::factory()->create(['company_id' => null]);

    ($this->runCommand)($admin, ['__all__', $companyA->id], ['assets'], 'soft')->assertExitCode(0);

    $this->assertSoftDeleted($assetA);
    $this->assertSoftDeleted($assetB);
    $this->assertSoftDeleted($orphanAsset);
});

test('specific company only still scopes correctly', function () {
    // Regression guard: __all__ handling must not accidentally
    // widen a specific-company selection.
    $admin = User::factory()->superuser()->create();
    [$companyA, $companyB] = Company::factory()->count(2)->create();

    $assetA = Asset::factory()->for($companyA)->create();
    $assetB = Asset::factory()->for($companyB)->create();
    $orphanAsset = Asset::factory()->create(['company_id' => null]);

    ($this->runCommand)($admin, [$companyA->id], ['assets'], 'soft')->assertExitCode(0);

    $this->assertSoftDeleted($assetA);
    $this->assertNotSoftDeleted($assetB);
    $this->assertNotSoftDeleted($orphanAsset);
});

test('null only still scopes to unassigned only', function () {
    // Regression guard: __null__ alone still means "no-company only".
    $admin = User::factory()->superuser()->create();
    [$companyA, $companyB] = Company::factory()->count(2)->create();

    $assetA = Asset::factory()->for($companyA)->create();
    $assetB = Asset::factory()->for($companyB)->create();
    $orphanAsset = Asset::factory()->create(['company_id' => null]);

    ($this->runCommand)($admin, ['__null__'], ['assets'], 'soft')->assertExitCode(0);

    $this->assertSoftDeleted($orphanAsset);
    $this->assertNotSoftDeleted($assetA);
    $this->assertNotSoftDeleted($assetB);
});
