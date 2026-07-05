<?php

namespace Tests\Feature\Importing\Api;

use App\Models\Actionlog as ActionLog;
use App\Models\Asset;
use App\Models\Import;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\Concerns\TestsPermissionsRequirement;
use Tests\Support\Importing\CleansUpImportFiles;
use Tests\Support\Importing\UsersImportFileBuilder as ImportFileBuilder;

class ImportUsersTest extends ImportDataTestCase implements TestsPermissionsRequirement
{
    use CleansUpImportFiles;
    use WithFaker;

    protected function importFileResponse(array $parameters = []): TestResponse
    {
        if (! array_key_exists('import-type', $parameters)) {
            $parameters['import-type'] = 'user';
        }

        return parent::importFileResponse($parameters);
    }

    #[Test]
    public function test_requires_permission()
    {
        $this->actingAsForApi(User::factory()->create());

        $this->importFileResponse(['import' => 44])->assertForbidden();
    }

    #[Test]
    public function user_with_import_assets_permission_can_import_users(): void
    {
        $this->actingAsForApi(User::factory()->canImport()->create());

        $import = Import::factory()->users()->create();

        $this->importFileResponse(['import' => $import->id])->assertOk();
    }

    #[Test]
    public function import_users(): void
    {
        Notification::fake();

        $importFileBuilder = ImportFileBuilder::new();
        $row = $importFileBuilder->firstRow();
        $import = Import::factory()->users()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());
        $this->importFileResponse(['import' => $import->id, 'send-welcome' => 1])
            ->assertOk()
            ->assertExactJson([
                'payload' => null,
                'status' => 'success',
                'messages' => ['redirect_url' => route('users.index')],
            ]);

        $newUser = User::query()
            ->with(['companies', 'location'])
            ->where('username', $row['username'])
            ->sole();

        Notification::assertNothingSent();

        $this->assertEquals($row['email'], $newUser->email);
        $this->assertEquals($row['firstName'], $newUser->first_name);
        $this->assertEquals($row['lastName'], $newUser->last_name);
        $this->assertEquals($row['displayName'], $newUser->display_name);
        $this->assertEquals($row['employeeNumber'], $newUser->employee_num);
        $this->assertEquals($row['companyName'], $newUser->companies->first()->name);
        $this->assertEquals($row['location'], $newUser->location->name);
        $this->assertEquals($row['phoneNumber'], $newUser->phone);
        $this->assertEquals($row['position'], $newUser->jobtitle);
        $this->assertFalse(Hash::isHashed($newUser->password));
        $this->assertEquals('', $newUser->website);
        $this->assertEquals('', $newUser->country);
        $this->assertEquals('', $newUser->address);
        $this->assertEquals('', $newUser->city);
        $this->assertEquals('', $newUser->state);
        $this->assertEquals('', $newUser->zip);
        $this->assertNull($newUser->permissions);
        $this->assertNull($newUser->avatar);
        $this->assertNull($newUser->notes);
        $this->assertNull($newUser->skin);
        $this->assertNull($newUser->department_id);
        $this->assertNull($newUser->two_factor_secret);
        $this->assertNull($newUser->idap_import);
        $this->assertEquals('en-US', $newUser->locale);
        $this->assertEquals(1, $newUser->show_in_list);
        $this->assertEquals(0, $newUser->two_factor_enrolled);
        $this->assertEquals(0, $newUser->two_factor_optin);
        $this->assertEquals(0, $newUser->remote);
        $this->assertEquals(0, $newUser->autoassign_licenses);
        $this->assertEquals(0, $newUser->vip);
        $this->assertEquals(0, $newUser->enable_sounds);
        $this->assertEquals(0, $newUser->enable_confetti);
        $this->assertNull($newUser->start_date);
        $this->assertNull($newUser->end_date);
        $this->assertNull($newUser->scim_externalid);
        $this->assertNull($newUser->manager_id);
        $this->assertNull($newUser->activation_code);
        $this->assertNull($newUser->last_login);
        $this->assertNull($newUser->persist_code);
        $this->assertNull($newUser->reset_password_code);
        $this->assertEquals(0, $newUser->activated);
    }

    #[Test]
    public function will_ignore_unknown_columns_when_file_contains_unknown_columns(): void
    {
        $row = ImportFileBuilder::new()->definition();
        $row['unknownColumnInCsvFile'] = 'foo';

        $importFileBuilder = new ImportFileBuilder([$row]);

        $this->actingAsForApi(User::factory()->superuser()->create());

        $import = Import::factory()->users()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->importFileResponse(['import' => $import->id])->assertOk();
    }

    #[Test]
    public function will_not_create_new_user_when_user_with_user_name_already_exist(): void
    {
        $user = User::factory()->create(['username' => Str::random()]);
        $importFileBuilder = ImportFileBuilder::times(4)->replace(['username' => $user->username]);
        $import = Import::factory()->users()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());
        $this->importFileResponse(['import' => $import->id])->assertOk();

        $probablyNewUsers = User::query()
            ->where('username', $user->username)
            ->get();

        $this->assertCount(1, $probablyNewUsers);
    }

    #[Test]
    public function will_generate_username_when_username_field_is_missing(): void
    {
        $importFileBuilder = ImportFileBuilder::new()->forget('username');
        $row = $importFileBuilder->firstRow();
        $import = Import::factory()->users()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());
        $this->importFileResponse(['import' => $import->id])->assertOk();

        $newUser = User::query()
            ->where('email', $row['email'])
            ->sole();

        $generatedUsername = User::generateFormattedNameFromFullName("{$row['firstName']} {$row['lastName']}")['username'];

        $this->assertEquals($generatedUsername, $newUser->username);
    }

    #[Test]
    public function will_update_location_of_all_assets_assigned_to_user(): void
    {
        $user = User::factory()->create(['username' => Str::random()]);
        $assetsAssignedToUser = Asset::factory()->create(['assigned_to' => $user->id, 'assigned_type' => User::class]);
        $importFileBuilder = ImportFileBuilder::new(['username' => $user->username]);
        $import = Import::factory()->users()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());
        $this->importFileResponse(['import' => $import->id, 'import-update' => true])->assertOk();

        $userLocation = Location::query()->where('name', $importFileBuilder->firstRow()['location'])->sole(['id']);

        $this->assertEquals(
            $userLocation->id,
            $assetsAssignedToUser->refresh()->location_id
        );
    }

    #[Test]
    public function when_required_columns_are_missing_in_import_file(): void
    {
        $importFileBuilder = ImportFileBuilder::new(['firstName' => ''])->forget(['username']);
        $import = Import::factory()->users()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());

        $this->importFileResponse(['import' => $import->id])
            ->assertInternalServerError()
            ->assertExactJson([
                'status' => 'import-errors',
                'payload' => null,
                'messages' => [
                    '' => [
                        'User' => [
                            'first_name' => ['The first name field is required.'],
                        ],
                    ],
                ],
            ]);

        $newUsers = User::query()
            ->where('email', $importFileBuilder->firstRow()['email'])
            ->get();

        $this->assertCount(0, $newUsers);
    }

    #[Test]
    public function update_user_from_import(): void
    {
        $user = User::factory()->create(['username' => Str::random()])->refresh();
        $importFileBuilder = ImportFileBuilder::new(['username' => $user->username]);

        $row = $importFileBuilder->firstRow();
        $import = Import::factory()->users()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());
        $this->importFileResponse(['import' => $import->id, 'import-update' => true])->assertOk();

        $updatedUser = User::query()->with(['companies', 'location'])->find($user->id);
        $updatedAttributes = [
            'first_name',
            'display_name',
            'email',
            'last_name',
            'employee_num',
            'location_id',
            'updated_at',
            'phone',
            'jobtitle',
        ];

        $this->assertEquals($row['email'], $updatedUser->email);
        $this->assertEquals($row['firstName'], $updatedUser->first_name);
        $this->assertEquals($row['displayName'], $updatedUser->display_name);
        $this->assertEquals($row['lastName'], $updatedUser->last_name);
        $this->assertEquals($row['employeeNumber'], $updatedUser->employee_num);
        $this->assertEquals($row['companyName'], $updatedUser->companies->first()->name);
        $this->assertEquals($row['location'], $updatedUser->location->name);
        $this->assertEquals($row['phoneNumber'], $updatedUser->phone);
        $this->assertEquals($row['position'], $updatedUser->jobtitle);
        $this->assertTrue(Hash::isHashed($updatedUser->password));

        $this->assertEquals(
            Arr::except($user->attributesToArray(), $updatedAttributes),
            Arr::except($updatedUser->attributesToArray(), $updatedAttributes),
        );
    }

    #[Test]
    public function update_mode_logs_user_update_in_actionlog(): void
    {
        $this->actingAsForApi(User::factory()->superuser()->create());

        $initialFile = ImportFileBuilder::new();
        $initialRow = $initialFile->firstRow();
        $initialImport = Import::factory()->users()->create([
            'file_path' => $initialFile->saveToImportsDirectory(),
        ]);

        $this->importFileResponse(['import' => $initialImport->id])->assertOk();

        $user = User::query()->where('username', $initialRow['username'])->sole();

        $updatedRow = array_merge($initialRow, [
            'position' => $initialRow['position'].' Updated',
        ]);

        $updateFile = new ImportFileBuilder([$updatedRow]);
        $updateImport = Import::factory()->users()->create([
            'file_path' => $updateFile->saveToImportsDirectory(),
        ]);

        $this->importFileResponse([
            'import' => $updateImport->id,
            'import-update' => true,
        ])->assertOk();

        $user->refresh();
        $this->assertEquals($updatedRow['position'], $user->jobtitle);

        $updateLog = ActionLog::query()
            ->where('item_type', User::class)
            ->where('item_id', $user->id)
            ->where('action_type', 'update')
            ->latest('id')
            ->first();

        $this->assertNotNull($updateLog, 'Expected an update action log entry after user importer update mode.');
    }

    /**
     * Some of these should mismatch on purpose to ensure the mapping is working
     */
    #[Test]
    public function custom_column_mapping(): void
    {
        $faker = ImportFileBuilder::new()->definition();
        $row = [
            'companyName' => $faker['username'],
            'email' => $faker['position'],
            'employeeNumber' => $faker['phoneNumber'],
            'firstName' => $faker['location'],
            'lastName' => $faker['lastName'],
            'location' => $faker['firstName'],
            'phoneNumber' => $faker['employeeNumber'],
            'position' => $faker['email'],
            'username' => $faker['companyName'],
            'dumbName' => $faker['displayName'],
        ];

        $importFileBuilder = new ImportFileBuilder([$row]);
        $import = Import::factory()->users()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());

        $this->importFileResponse([
            'import' => $import->id,
            'column-mappings' => [
                'Company' => 'username',
                'email' => 'jobtitle',
                'Employee Number' => 'phone_number',
                'First Name' => 'location',
                'Last Name' => 'last_name',
                'Location' => 'first_name',
                'Phone Number' => 'employee_num',
                'Job Title' => 'email',
                'Username' => 'company',
                'dumbName' => 'display_name',
            ],
        ])->assertOk()
            ->json();

        $newUser = User::query()
            ->with(['companies', 'location'])
            ->where('username', $row['companyName'])
            ->sole();

        $this->assertEquals($row['position'], $newUser->email);
        $this->assertEquals($row['location'], $newUser->first_name);
        $this->assertEquals($row['lastName'], $newUser->last_name);
        $this->assertEquals($row['dumbName'], $newUser->display_name);
        $this->assertEquals($row['email'], $newUser->jobtitle);
        $this->assertEquals($row['phoneNumber'], $newUser->employee_num);
        $this->assertEquals($row['username'], $newUser->companies->first()->name);
        $this->assertEquals($row['firstName'], $newUser->location->name);
        $this->assertEquals($row['employeeNumber'], $newUser->phone);
        $this->assertFalse(Hash::isHashed($newUser->password));
        $this->assertEquals('', $newUser->website);
        $this->assertEquals('', $newUser->country);
        $this->assertEquals('', $newUser->address);
        $this->assertEquals('', $newUser->city);
        $this->assertEquals('', $newUser->state);
        $this->assertEquals('', $newUser->zip);
        $this->assertNull($newUser->permissions);
        $this->assertNull($newUser->avatar);
        $this->assertNull($newUser->notes);
        $this->assertNull($newUser->skin);
        $this->assertNull($newUser->department_id);
        $this->assertNull($newUser->two_factor_secret);
        $this->assertNull($newUser->idap_import);
        $this->assertEquals('en-US', $newUser->locale);
        $this->assertEquals(1, $newUser->show_in_list);
        $this->assertEquals(0, $newUser->two_factor_enrolled);
        $this->assertEquals(0, $newUser->two_factor_optin);
        $this->assertEquals(0, $newUser->remote);
        $this->assertEquals(0, $newUser->autoassign_licenses);
        $this->assertEquals(0, $newUser->vip);
        $this->assertEquals(0, $newUser->enable_sounds);
        $this->assertEquals(0, $newUser->enable_confetti);
        $this->assertNull($newUser->start_date);
        $this->assertNull($newUser->end_date);
        $this->assertNull($newUser->scim_externalid);
        $this->assertNull($newUser->manager_id);
        $this->assertNull($newUser->activation_code);
        $this->assertNull($newUser->last_login);
        $this->assertNull($newUser->persist_code);
        $this->assertNull($newUser->reset_password_code);
        $this->assertEquals(0, $newUser->activated);
    }

    #[Test]
    public function import_only_user_cannot_overwrite_auth_fields_when_updating(): void
    {
        $victim = User::factory()->create([
            'username' => 'victim_user',
            'email' => 'original@example.com',
        ]);

        $importFileBuilder = new ImportFileBuilder([
            array_merge(ImportFileBuilder::new()->definition(), [
                'username' => 'victim_user',
                'email' => 'hijacked@evil.com',
            ]),
        ]);

        $this->actingAsForApi(User::factory()->canImport()->create());
        $import = Import::factory()->users()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);
        $this->importFileResponse(['import' => $import->id, 'import-update' => true])->assertOk();

        $this->assertEquals('original@example.com', $victim->refresh()->email);
    }

    #[Test]
    public function user_with_import_and_edit_users_permission_can_update_auth_fields(): void
    {
        $target = User::factory()->create([
            'username' => 'target_user',
            'email' => 'original@example.com',
        ]);

        $importFileBuilder = new ImportFileBuilder([
            array_merge(ImportFileBuilder::new()->definition(), [
                'username' => 'target_user',
                'email' => 'updated@example.com',
            ]),
        ]);

        $this->actingAsForApi(User::factory()->canImport()->editUsers()->create());
        $import = Import::factory()->users()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);
        $this->importFileResponse(['import' => $import->id, 'import-update' => true])->assertOk();

        $this->assertEquals('updated@example.com', $target->refresh()->email);
    }

    #[Test]
    public function display_name_falls_back_to_full_name_when_column_is_missing_from_csv(): void
    {
        $importFileBuilder = ImportFileBuilder::new()->forget('displayName');
        $row = $importFileBuilder->firstRow();
        $import = Import::factory()->users()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());
        $this->importFileResponse(['import' => $import->id])->assertOk();

        $newUser = User::query()->where('username', $row['username'])->sole();

        $this->assertEquals("{$row['firstName']} {$row['lastName']}", $newUser->display_name);
    }

    #[Test]
    public function display_name_falls_back_to_full_name_when_column_is_empty_in_csv(): void
    {
        $importFileBuilder = ImportFileBuilder::new(['displayName' => '']);
        $row = $importFileBuilder->firstRow();
        $import = Import::factory()->users()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());
        $this->importFileResponse(['import' => $import->id])->assertOk();

        $newUser = User::query()->where('username', $row['username'])->sole();

        $this->assertEquals("{$row['firstName']} {$row['lastName']}", $newUser->display_name);
    }
}
