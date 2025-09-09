<?php

namespace Tests\Feature\Importing\Api;

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
        if (!array_key_exists('import-type', $parameters)) {
            $parameters['import-type'] = 'user';
        }

        return parent::importFileResponse($parameters);
    }

    #[Test]
    public function testRequiresPermission()
    {
        $this->actingAsForApi(User::factory()->create());

        $this->importFileResponse(['import' => 44])->assertForbidden();
    }

    #[Test]
    public function userWithImportAssetsPermissionCanImportUsers(): void
    {
        $this->actingAsForApi(User::factory()->canImport()->create());

        $import = Import::factory()->users()->create();

        $this->importFileResponse(['import' => $import->id])->assertOk();
    }

    #[Test]
    public function importUsers(): void
    {
        Notification::fake();

        $importFileBuilder = ImportFileBuilder::new();
        $row = $importFileBuilder->firstRow();
        $import = Import::factory()->users()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());
        $this->importFileResponse(['import' => $import->id, 'send-welcome' => 1])
            ->assertOk()
            ->assertExactJson([
                'payload'  => null,
                'status'   => 'success',
                'messages' => ['redirect_url' => route('users.index')]
            ]);

        $newUser = User::query()
            ->with(['company', 'location'])
            ->where('username', $row['username'])
            ->sole();

        Notification::assertNothingSent();

        $this->assertEquals($row['email'], $newUser->email);
        $this->assertEquals($row['firstName'], $newUser->first_name);
        $this->assertEquals($row['lastName'], $newUser->last_name);
        $this->assertEquals($row['displayName'], $newUser->display_name);
        $this->assertEquals($row['employeeNumber'], $newUser->employee_num);
        $this->assertEquals($row['companyName'], $newUser->company->name);
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
    public function willIgnoreUnknownColumnsWhenFileContainsUnknownColumns(): void
    {
        $row = ImportFileBuilder::new()->definition();
        $row['unknownColumnInCsvFile'] = 'foo';

        $importFileBuilder = new ImportFileBuilder([$row]);

        $this->actingAsForApi(User::factory()->superuser()->create());

        $import = Import::factory()->users()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->importFileResponse(['import' => $import->id])->assertOk();
    }

    #[Test]
    public function willNotCreateNewUserWhenUserWithUserNameAlreadyExist(): void
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
    public function willGenerateUsernameWhenUsernameFieldIsMissing(): void
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
    public function willUpdateLocationOfAllAssetsAssignedToUser(): void
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
    public function whenRequiredColumnsAreMissingInImportFile(): void
    {
        $importFileBuilder = ImportFileBuilder::new(['firstName' => ''])->forget(['username']);
        $import = Import::factory()->users()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());

        $this->importFileResponse(['import' => $import->id])
            ->assertInternalServerError()
            ->assertExactJson([
                'status'   => 'import-errors',
                'payload'  => null,
                'messages' => [
                    '' => [
                        'User' => [
                            'first_name' => ['The first name field is required.'],
                        ]
                    ]
                ]
            ]);

        $newUsers = User::query()
            ->where('email', $importFileBuilder->firstRow()['email'])
            ->get();

        $this->assertCount(0, $newUsers);
    }

    #[Test]
    public function updateUserFromImport(): void
    {
        $user = User::factory()->create(['username' => Str::random()])->refresh();
        $importFileBuilder = ImportFileBuilder::new(['username'  => $user->username]);

        $row = $importFileBuilder->firstRow();
        $import = Import::factory()->users()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());
        $this->importFileResponse(['import' => $import->id, 'import-update' => true])->assertOk();

        $updatedUser = User::query()->with(['company', 'location'])->find($user->id);
        $updatedAttributes = [
            'first_name',
            'display_name',
            'email',
            'last_name',
            'employee_num',
            'company',
            'location_id',
            'company_id',
            'updated_at',
            'phone',
            'jobtitle',
        ];

        $this->assertEquals($row['email'], $updatedUser->email);
        $this->assertEquals($row['firstName'], $updatedUser->first_name);
        $this->assertEquals($row['displayName'], $updatedUser->display_name);
        $this->assertEquals($row['lastName'], $updatedUser->last_name);
        $this->assertEquals($row['employeeNumber'], $updatedUser->employee_num);
        $this->assertEquals($row['companyName'], $updatedUser->company->name);
        $this->assertEquals($row['location'], $updatedUser->location->name);
        $this->assertEquals($row['phoneNumber'], $updatedUser->phone);
        $this->assertEquals($row['position'], $updatedUser->jobtitle);
        $this->assertTrue(Hash::isHashed($updatedUser->password));

        $this->assertEquals(
            Arr::except($user->attributesToArray(), $updatedAttributes),
            Arr::except($updatedUser->attributesToArray(), $updatedAttributes),
        );
    }


    /**
     * Some of these should mismatch on purpose to ensure the mapping is working
     * @return void
     */
    #[Test]
    public function customColumnMapping(): void
    {
        $faker = ImportFileBuilder::new()->definition();
        $row = [
            'companyName'    => $faker['username'],
            'email'          => $faker['position'],
            'employeeNumber' => $faker['phoneNumber'],
            'firstName'       => $faker['location'],
            'lastName'       => $faker['lastName'],
            'location'       => $faker['firstName'],
            'phoneNumber'    => $faker['employeeNumber'],
            'position'       => $faker['email'],
            'username'       => $faker['companyName'],
            'dumbName'       => $faker['displayName'],
        ];

        $importFileBuilder = new ImportFileBuilder([$row]);
        $import = Import::factory()->users()->create(['file_path' => $importFileBuilder->saveToImportsDirectory()]);

        $this->actingAsForApi(User::factory()->superuser()->create());

        $this->importFileResponse([
            'import' => $import->id,
            'column-mappings' => [
                'Company'         => 'username',
                'email'           => 'jobtitle',
                'Employee Number' => 'phone_number',
                'First Name'      => 'location',
                'Last Name'       => 'last_name',
                'Location'        => 'first_name',
                'Phone Number'    => 'employee_num',
                'Job Title'       => 'email',
                'Username'        => 'company',
                'dumbName'    => 'display_name',
            ]
        ])->assertOk()
            ->json();


        $newUser = User::query()
            ->with(['company', 'location'])
            ->where('username', $row['companyName'])
            ->sole();

        $this->assertEquals($row['position'], $newUser->email);
        $this->assertEquals($row['location'], $newUser->first_name);
        $this->assertEquals($row['lastName'], $newUser->last_name);
        $this->assertEquals($row['dumbName'], $newUser->display_name);
        $this->assertEquals($row['email'], $newUser->jobtitle);
        $this->assertEquals($row['phoneNumber'], $newUser->employee_num);
        $this->assertEquals($row['username'], $newUser->company->name);
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
}
