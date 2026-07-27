<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * Set by ->forCompany() and ->withoutCompany() to suppress the default
     * "attach a fresh Company via the pivot" behavior in configure(). Laravel
     * factory chain calls clone the factory, and clone copies protected
     * properties, so setting this in a chained state persists through create().
     */
    protected bool $skipDefaultCompanyAttach = false;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'activated' => 1,
            'address' => $this->faker->address(),
            'city' => $this->faker->city(),
            'country' => $this->faker->country(),
            'created_by' => 1,
            'display_name' => null,
            'email' => $this->faker->safeEmail(),
            'employee_num' => $this->faker->numberBetween(3500, 35050),
            'first_name' => $this->faker->firstName(),
            'jobtitle' => $this->faker->jobTitle(),
            'last_name' => $this->faker->lastName(),
            'locale' => 'en-US',
            'mobile' => $this->faker->phoneNumber(),
            'notes' => 'Created by DB seeder',
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'permissions' => '{}',
            'phone' => $this->faker->phoneNumber(),
            'state' => $this->faker->stateAbbr(),
            'username' => $this->faker->unique()->username(),
            'zip' => $this->faker->postcode(),
        ];
    }

    /**
     * By default every factory-created user is attached to a fresh Company via
     * the company_user pivot (the authoritative source of user-company
     * membership under FMCS). Use ->forCompany($company) to pin them to a
     * specific company, or ->withoutCompany() to leave them unattached
     * ("floater" users).
     *
     * We hook create() (not afterCreating) because chained state calls clone
     * the factory and register additional afterCreating closures that captured
     * $this at closure-creation time. Reading $this->skipDefaultCompanyAttach
     * at create() time gives us the final factory's flag, which is what we
     * want.
     */
    public function create($attributes = [], ?Model $parent = null)
    {
        $result = parent::create($attributes, $parent);

        $models = $result instanceof User
            ? [$result]
            : $result->all();

        foreach ($models as $user) {
            // Watson\Validating hooks the saving event and silently returns
            // false from save() when validation fails (its default is not to
            // throw). Laravel's Factory::store() doesn't check the return
            // value, so we can arrive here with an unpersisted user whose id
            // is still null. Attaching a pivot row for that user would insert
            // NULL into company_user.user_id and violate the NOT NULL. Skip.
            if (! $user->exists) {
                continue;
            }
            if ($this->skipDefaultCompanyAttach) {
                continue;
            }
            if ($user->companies()->count() > 0) {
                continue;
            }
            $company = Company::factory()->create();
            $user->companies()->attach($company->id);
            $user->syncLegacyCompanyIdMirror();
        }

        return $result;
    }

    /**
     * Preserve the skipDefaultCompanyAttach flag across chained states.
     * Laravel's Factory::newInstance() rebuilds the factory but does not carry
     * arbitrary properties, so we copy the flag ourselves.
     */
    protected function newInstance(array $arguments = [])
    {
        $factory = parent::newInstance($arguments);
        $factory->skipDefaultCompanyAttach = $this->skipDefaultCompanyAttach;

        return $factory;
    }

    /**
     * Attach the created user to a specific Company via the pivot. Accepts
     * either a Company model or a raw company id, so tests can call
     * ->forCompany($company) or ->forCompany($company->id) interchangeably.
     */
    public function forCompany(Company|int $company): static
    {
        $companyId = $company instanceof Company ? $company->id : $company;

        $factory = $this->afterCreating(function (User $user) use ($companyId) {
            $user->companies()->syncWithoutDetaching([$companyId]);
            $user->syncLegacyCompanyIdMirror();
        });
        $factory->skipDefaultCompanyAttach = true;

        return $factory;
    }

    /**
     * Create a user with no company_user pivot rows (a "floater" under FMCS
     * floater mode).
     */
    public function withoutCompany(): static
    {
        $factory = clone $this;
        $factory->skipDefaultCompanyAttach = true;

        return $factory;
    }

    public function deletedUser()
    {
        return $this->state(function () {
            return [
                'deleted_at' => $this->faker->dateTime(),
            ];
        });
    }

    public function firstAdmin()
    {
        return $this->state(function () {
            return [
                'first_name' => 'Admin',
                'last_name' => 'User',
                'username' => 'admin',
                'avatar' => '1.jpg',
                'permissions' => '{"superuser":"1"}',
            ];
        });
    }

    public function snipeAdmin()
    {
        return $this->state(function () {
            return [
                'first_name' => 'Snipe E.',
                'last_name' => 'Head',
                'username' => 'snipe',
                'avatar' => '2.jpg',
                'email' => 'snipe@snipe.net',
                'permissions' => '{"superuser":"1"}',
            ];
        });
    }

    public function testAdmin()
    {
        return $this->state(function () {
            return [
                'first_name' => 'Alison',
                'last_name' => 'Gianotto',
                'username' => 'agianotto@grokability.com',
                'avatar' => '2.jpg',
                'email' => 'agianotto@grokability.com',
                'permissions' => '{"superuser":"1"}',
            ];
        });
    }

    public function superuser()
    {
        return $this->appendPermission(['superuser' => '1']);
    }

    public function admin()
    {
        return $this->state(function () {
            return [
                'permissions' => '{"admin":"1"}',
                'manager_id' => function () {
                    return User::where('permissions->superuser', '1')->first() ?? User::factory()->firstAdmin();
                },
            ];
        });
    }

    public function viewAssets()
    {
        return $this->appendPermission(['assets.view' => '1']);
    }

    public function viewAssetHistory()
    {
        return $this->appendPermission(['assets.view' => '1']);
    }

    public function viewUserHistory()
    {
        return $this->appendPermission(['users.view' => '1']);
    }

    public function viewLocationHistory()
    {
        return $this->appendPermission(['locations.view' => '1']);
    }

    public function viewAccessoryHistory()
    {
        return $this->appendPermission(['accessories.view' => '1']);
    }

    public function viewLicenseHistory()
    {
        return $this->appendPermission(['licenses.view' => '1']);
    }

    public function viewComponentHistory()
    {
        return $this->appendPermission(['components.view' => '1']);
    }

    public function viewConsumableHistory()
    {
        return $this->appendPermission(['consumables.view' => '1']);
    }

    public function createAssets()
    {
        return $this->appendPermission(['assets.create' => '1']);
    }

    public function editAssets()
    {
        return $this->appendPermission(['assets.edit' => '1']);
    }

    public function deleteAssets()
    {
        return $this->appendPermission(['assets.delete' => '1']);
    }

    public function checkinAssets()
    {
        return $this->appendPermission(['assets.checkin' => '1']);
    }

    public function checkoutAssets()
    {
        return $this->appendPermission(['assets.checkout' => '1']);
    }

    public function viewRequestableAssets()
    {
        return $this->appendPermission(['assets.view.requestable' => '1']);
    }

    public function viewEncryptedCustomFields()
    {
        return $this->appendPermission(['assets.view.encrypted_custom_fields' => '1']);
    }

    public function deleteAssetModels()
    {
        return $this->appendPermission(['models.delete' => '1']);
    }

    public function viewAssetModels()
    {
        return $this->appendPermission(['models.view' => '1']);
    }

    public function viewAccessories()
    {
        return $this->appendPermission(['accessories.view' => '1']);
    }

    public function createAccessories()
    {
        return $this->appendPermission(['accessories.create' => '1']);
    }

    public function editAccessories()
    {
        return $this->appendPermission(['accessories.edit' => '1']);
    }

    public function deleteAccessories()
    {
        return $this->appendPermission(['accessories.delete' => '1']);
    }

    public function checkinAccessories()
    {
        return $this->appendPermission(['accessories.checkin' => '1']);
    }

    public function checkoutAccessories()
    {
        return $this->appendPermission(['accessories.checkout' => '1']);
    }

    public function viewConsumables()
    {
        return $this->appendPermission(['consumables.view' => '1']);
    }

    public function createConsumables()
    {
        return $this->appendPermission(['consumables.create' => '1']);
    }

    public function editConsumables()
    {
        return $this->appendPermission(['consumables.edit' => '1']);
    }

    public function deleteConsumables()
    {
        return $this->appendPermission(['consumables.delete' => '1']);
    }

    public function checkinConsumables()
    {
        return $this->appendPermission(['consumables.checkin' => '1']);
    }

    public function checkoutConsumables()
    {
        return $this->appendPermission(['consumables.checkout' => '1']);
    }

    public function deleteDepartments()
    {
        return $this->appendPermission(['departments.delete' => '1']);
    }

    public function viewDepartments()
    {
        return $this->appendPermission(['departments.view' => '1']);
    }

    public function viewLicenses()
    {
        return $this->appendPermission(['licenses.view' => '1']);
    }

    public function createLicenses()
    {
        return $this->appendPermission(['licenses.create' => '1']);
    }

    public function editLicenses()
    {
        return $this->appendPermission(['licenses.edit' => '1']);
    }

    public function deleteLicenses()
    {
        return $this->appendPermission(['licenses.delete' => '1']);
    }

    public function checkoutLicenses()
    {
        return $this->appendPermission(['licenses.checkout' => '1']);
    }

    public function checkinLicenses()
    {
        return $this->appendPermission(['licenses.checkin' => '1']);
    }

    public function viewKeysLicenses()
    {
        return $this->appendPermission(['licenses.keys' => '1']);
    }

    public function viewComponents()
    {
        return $this->appendPermission(['components.view' => '1']);
    }

    public function createComponents()
    {
        return $this->appendPermission(['components.create' => '1']);
    }

    public function editComponents()
    {
        return $this->appendPermission(['components.edit' => '1']);
    }

    public function deleteComponents()
    {
        return $this->appendPermission(['components.delete' => '1']);
    }

    public function checkinComponents()
    {
        return $this->appendPermission(['components.checkin' => '1']);
    }

    public function checkoutComponents()
    {
        return $this->appendPermission(['components.checkout' => '1']);
    }

    public function viewCompanies()
    {
        return $this->appendPermission(['companies.view' => '1']);
    }

    public function createCompanies()
    {
        return $this->appendPermission(['companies.create' => '1']);
    }

    public function deleteCompanies()
    {
        return $this->appendPermission(['companies.delete' => '1']);
    }

    public function editCompanies()
    {
        return $this->appendPermission(['companies.edit' => '1']);
    }

    public function viewUsers()
    {
        return $this->appendPermission(['users.view' => '1']);
    }

    public function createUsers()
    {
        return $this->appendPermission(['users.create' => '1']);
    }

    public function editUsers()
    {
        return $this->appendPermission(['users.edit' => '1']);
    }

    public function deleteUsers()
    {
        return $this->appendPermission(['users.delete' => '1']);
    }

    public function deleteCategories()
    {
        return $this->appendPermission(['categories.delete' => '1']);
    }

    public function deleteLocations()
    {
        return $this->appendPermission(['locations.delete' => '1']);
    }

    public function canEditOwnLocation()
    {
        return $this->appendPermission(['self.edit_location' => '1']);
    }

    public function canViewReports()
    {
        return $this->appendPermission(['reports.view' => '1']);
    }

    public function canImport()
    {
        return $this->appendPermission(['import' => '1']);
    }

    public function createCustomFields()
    {
        return $this->appendPermission(['customfields.create' => '1']);
    }

    public function viewCustomFields()
    {
        return $this->appendPermission(['customfields.view' => '1']);
    }

    public function deleteCustomFields()
    {
        return $this->appendPermission(['customfields.delete' => '1']);
    }

    public function deleteCustomFieldsets()
    {
        return $this->appendPermission(['customfields.delete' => '1']);
    }

    public function deleteDepreciations()
    {
        return $this->appendPermission(['depreciations.delete' => '1']);
    }

    public function deleteManufacturers()
    {
        return $this->appendPermission(['manufacturers.delete' => '1']);
    }

    public function deletePredefinedKits()
    {
        return $this->appendPermission(['kits.delete' => '1']);
    }

    public function editPredefinedKits()
    {
        return $this->appendPermission(['kits.edit' => '1']);
    }

    public function viewPredefinedKits()
    {
        return $this->appendPermission(['kits.view' => '1']);
    }

    public function deleteStatusLabels()
    {
        return $this->appendPermission(['statuslabels.delete' => '1']);
    }

    public function deleteSuppliers()
    {
        return $this->appendPermission(['suppliers.delete' => '1']);
    }

    public function auditAssets()
    {
        return $this->appendPermission(['assets.audit' => '1']);
    }

    public function manageAssetFiles()
    {
        return $this->appendPermission(['assets.files' => '1']);
    }

    public function manageModelFiles()
    {
        return $this->appendPermission(['models.files' => '1']);
    }

    public function manageLocationFiles()
    {
        return $this->appendPermission(['locations.files' => '1']);
    }

    public function manageCompanyFiles()
    {
        return $this->appendPermission(['companies.files' => '1']);
    }

    public function manageSupplierFiles()
    {
        return $this->appendPermission(['suppliers.files' => '1']);
    }

    private function appendPermission(array $permission)
    {
        return $this->state(function ($currentState) use ($permission) {
            return [
                'permissions' => json_encode(
                    array_merge(
                        json_decode($currentState['permissions'], true),
                        $permission
                    )
                ),
            ];
        });
    }

    /**
     * Named non-admin users with full control over ONE checkoutable resource
     * type. Useful for demo installs and docs screenshots that need to show
     * what a resource-scoped operator actually sees (nav items greyed out,
     * settings hidden, etc). No superuser flag, no admin flag.
     *
     * Passwords are the factory default. Usernames are the demo identifier.
     */
    public function assetManager()
    {
        return $this->state([
            'first_name' => 'Asset',
            'last_name' => 'Manager',
            'username' => 'assetmgr',
            'email' => 'assetmgr@demo.snipeitapp.com',
            'permissions' => json_encode([
                'assets.view' => '1',
                'assets.create' => '1',
                'assets.edit' => '1',
                'assets.delete' => '1',
                'assets.checkout' => '1',
                'assets.checkin' => '1',
                'assets.audit' => '1',
                'assets.view.requestable' => '1',
                'assets.view.encrypted_custom_fields' => '1',
                'assets.files' => '1',
                'models.view' => '1',
                'models.files' => '1',
            ]),
        ]);
    }

    public function licenseManager()
    {
        return $this->state([
            'first_name' => 'License',
            'last_name' => 'Manager',
            'username' => 'licensemgr',
            'email' => 'licensemgr@demo.snipeitapp.com',
            'permissions' => json_encode([
                'licenses.view' => '1',
                'licenses.create' => '1',
                'licenses.edit' => '1',
                'licenses.delete' => '1',
                'licenses.checkout' => '1',
                'licenses.checkin' => '1',
                'licenses.keys' => '1',
                'licenses.files' => '1',
            ]),
        ]);
    }

    public function accessoryManager()
    {
        return $this->state([
            'first_name' => 'Accessory',
            'last_name' => 'Manager',
            'username' => 'accessorymgr',
            'email' => 'accessorymgr@demo.snipeitapp.com',
            'permissions' => json_encode([
                'accessories.view' => '1',
                'accessories.create' => '1',
                'accessories.edit' => '1',
                'accessories.delete' => '1',
                'accessories.checkout' => '1',
                'accessories.checkin' => '1',
                'accessories.files' => '1',
            ]),
        ]);
    }

    public function consumableManager()
    {
        return $this->state([
            'first_name' => 'Consumable',
            'last_name' => 'Manager',
            'username' => 'consumablemgr',
            'email' => 'consumablemgr@demo.snipeitapp.com',
            'permissions' => json_encode([
                'consumables.view' => '1',
                'consumables.create' => '1',
                'consumables.edit' => '1',
                'consumables.delete' => '1',
                'consumables.checkout' => '1',
                'consumables.checkin' => '1',
                'consumables.files' => '1',
            ]),
        ]);
    }

    public function componentManager()
    {
        return $this->state([
            'first_name' => 'Component',
            'last_name' => 'Manager',
            'username' => 'componentmgr',
            'email' => 'componentmgr@demo.snipeitapp.com',
            'permissions' => json_encode([
                'components.view' => '1',
                'components.create' => '1',
                'components.edit' => '1',
                'components.delete' => '1',
                'components.checkout' => '1',
                'components.checkin' => '1',
                'components.files' => '1',
            ]),
        ]);
    }

    public function userManager()
    {
        return $this->state([
            'first_name' => 'User',
            'last_name' => 'Manager',
            'username' => 'usermgr',
            'email' => 'usermgr@demo.snipeitapp.com',
            'permissions' => json_encode([
                'users.view' => '1',
                'users.create' => '1',
                'users.edit' => '1',
                'users.delete' => '1',
                'users.files' => '1',
            ]),
        ]);
    }

    public function deleted(): self
    {
        return $this->state(['deleted_at' => $this->faker->dateTime()]);
    }
}
