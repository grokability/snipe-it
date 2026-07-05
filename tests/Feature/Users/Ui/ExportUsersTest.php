<?php

namespace Tests\Feature\Users\Ui;

use App\Models\Accessory;
use App\Models\Asset;
use App\Models\Company;
use App\Models\Consumable;
use App\Models\Group;
use App\Models\LicenseSeat;
use App\Models\Location;
use App\Models\User;
use Tests\TestCase;

class ExportUsersTest extends TestCase
{
    public function test_requires_permission()
    {
        $this->actingAs(User::factory()->create())
            ->get(route('users.export'))
            ->assertForbidden();
    }

    public function test_can_export_users_to_csv()
    {
        $creator = User::factory()->create(['first_name' => 'Han', 'last_name' => 'Solo']);
        $deptManager = User::factory()->create(['first_name' => 'Mace', 'last_name' => 'Windu']);

        $luke = User::factory()
            ->forCompany(['name' => 'Jedi'])
            ->forManager(['first_name' => 'Ben', 'last_name' => 'Kenobi'])
            ->forLocation(['name' => 'Space'])
            ->forDepartment(['name' => 'Lightsaber Fighting Dept', 'manager_id' => $deptManager->id])
            ->create([
                'jobtitle' => 'Jedi Master',
                'employee_num' => '789',
                'first_name' => 'Luke',
                'last_name' => 'Skywalker',
                'display_name' => 'Master Luke',
                'username' => 'lskywalker',
                'email' => 'skywalker@jedi.com',
                'phone' => '555-1234',
                'mobile' => '555-5678',
                'website' => 'https://jedi.com',
                'address' => '123 Moisture Farm',
                'city' => 'Anchorhead',
                'state' => 'TA',
                'country' => 'Outer Rim',
                'zip' => '12345',
                'notes' => 'Nice guy...',
                'vip' => 1,
                'remote' => 1,
                'autoassign_licenses' => 1,
                'ldap_import' => 1,
                'start_date' => '2020-01-01',
                'end_date' => '2030-12-31',
                'created_by' => $creator->id,
            ]);

        $luke->groups()->sync(
            Group::factory()->count(2)->sequence(
                ['name' => 'Jedi'],
                ['name' => 'Jedi Dance Crew'],
            )->create()
        );

        Asset::factory()->assignedToUser($luke)->count(2)->create();
        LicenseSeat::factory()->assignedToUser($luke)->count(2)->create();
        Accessory::factory()->checkedOutToUser($luke)->count(2)->create();
        Consumable::factory()->checkedOutToUser($luke)->count(2)->create();
        User::factory()->count(3)->create(['manager_id' => $luke->id]);
        Location::factory()->count(2)->create(['manager_id' => $luke->id]);

        $this->actingAs(User::factory()->viewUsers()->create())
            ->get(route('users.export'))
            ->assertOk()
            ->assertCsvHeader()
            ->assertSeeTextInStreamedResponse([
                'Jedi',
                'Jedi Master',
                '789',
                'Luke',
                'Skywalker',
                'Luke Skywalker',
                'lskywalker',
                'skywalker@jedi.com',
                'Ben Kenobi',
                'Space',
                'Lightsaber Fighting Dept',
                '2',
                'Jedi, Jedi Dance Crew',
                trans('general.user'),
                'Nice guy...',
                trans('general.yes'),
            ])
            ->assertSeePairsInStreamedResponse([
                trans('admin/users/table.first_name') => 'Luke',
                trans('admin/users/table.last_name') => 'Skywalker',
                trans('admin/users/table.display_name') => 'Master Luke',
                trans('admin/users/table.username') => 'lskywalker',
                trans('admin/users/table.email') => 'skywalker@jedi.com',
                trans('admin/companies/table.title') => 'Jedi',
                trans('general.groups') => 'Jedi, Jedi Dance Crew',
                trans('admin/users/table.title') => 'Jedi Master',
                trans('general.employee_number') => '789',
                trans('admin/users/table.manager') => 'Ben Kenobi',
                trans('admin/users/table.location') => 'Space',
                trans('general.department') => 'Lightsaber Fighting Dept',
                trans('general.assets') => '2',
                trans('general.accessories') => '2',
                trans('general.consumables') => '2',
                trans('general.licenses') => '2',
                trans('general.notes') => 'Nice guy...',
                trans('admin/users/table.phone') => '555-1234',
                trans('admin/users/table.mobile') => '555-5678',
                trans('general.website') => 'https://jedi.com',
                trans('general.address') => '123 Moisture Farm',
                trans('general.city') => 'Anchorhead',
                trans('general.state') => 'TA',
                trans('general.country') => 'Outer Rim',
                trans('general.zip') => '12345',
                trans('general.importer.vip') => trans('general.yes'),
                trans('admin/users/general.remote') => trans('general.yes'),
                trans('general.autoassign_licenses') => trans('general.yes'),
                trans('general.ldap_sync') => trans('general.yes'),
                trans('admin/users/table.managed_users') => '3',
                trans('admin/users/table.managed_locations') => '2',
                trans('admin/users/general.department_manager') => 'Mace Windu',
                trans('general.created_by') => 'Han Solo',
                trans('general.start_date') => '2020-01-01',
                trans('general.end_date') => '2030-12-31',
            ]);
    }

    public function test_multi_company_user_exports_pipe_separated_company_names()
    {
        [$companyA, $companyB] = Company::factory()->sequence(
            ['name' => 'Rebel Alliance'],
            ['name' => 'Galactic Senate'],
        )->count(2)->create();

        $user = User::factory()->create(['company_id' => $companyA->id]);
        $user->companies()->sync([$companyA->id, $companyB->id]);

        $this->actingAs(User::factory()->viewUsers()->create())
            ->get(route('users.export'))
            ->assertOk()
            ->assertCsvHeader()
            ->assertSeePairsInStreamedResponse([
                trans('admin/users/table.first_name') => $user->first_name,
                trans('admin/companies/table.title') => 'Rebel Alliance|Galactic Senate',
            ]);
    }

    public function test_fmcs_export_excludes_other_company_users()
    {
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $actorCompany = $companyA;
        $inScope  = User::factory()->create();
        $inScope->companies()->sync([$actorCompany->id]);

        $outScope = User::factory()->create();
        $outScope->companies()->sync([$companyB->id]);

        $actor = User::factory()->viewUsers()->create();
        $actor->companies()->sync([$actorCompany->id]);

        $this->actingAs($actor)
            ->get(route('users.export'))
            ->assertOk()
            ->assertSeeTextInStreamedResponse([$inScope->username])
            ->assertDontSeeTextInStreamedResponse([$outScope->username]);
    }

    public function test_fmcs_export_excludes_null_company_users_when_floater_off()
    {
        $this->settings->enableMultipleFullCompanySupport();
        $this->settings->disableFloaterMode();

        $company = Company::factory()->create();

        $inScope    = User::factory()->create();
        $inScope->companies()->sync([$company->id]);

        $nullCompany = User::factory()->create(['company_id' => null]);

        $actor = User::factory()->viewUsers()->create();
        $actor->companies()->sync([$company->id]);

        $this->actingAs($actor)
            ->get(route('users.export'))
            ->assertOk()
            ->assertSeeTextInStreamedResponse([$inScope->username])
            ->assertDontSeeTextInStreamedResponse([$nullCompany->username]);
    }

    public function test_fmcs_export_includes_null_company_users_when_floater_on()
    {
        $this->settings->enableMultipleFullCompanySupport();
        $this->settings->enableFloaterMode();

        $company = Company::factory()->create();

        $inScope    = User::factory()->create();
        $inScope->companies()->sync([$company->id]);

        $nullCompany = User::factory()->create(['company_id' => null]);

        $actor = User::factory()->viewUsers()->create();
        $actor->companies()->sync([$company->id]);

        $this->actingAs($actor)
            ->get(route('users.export'))
            ->assertOk()
            ->assertSeeTextInStreamedResponse([$inScope->username, $nullCompany->username]);
    }
}
