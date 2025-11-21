<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use \Auth;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
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
            'company_id' => Company::factory(),
            'country' => $this->faker->country(),
            'email' => $this->faker->safeEmail(),
            'employee_num' => $this->faker->numberBetween(3500, 35050),
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'display_name' => null,
            'jobtitle' => $this->faker->jobTitle(),
            'locale' => 'en-US',
            'notes' => 'Created by DB seeder',
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'permissions' => '{}',
            'phone' => $this->faker->phoneNumber(),
            'mobile' => $this->faker->phoneNumber(),
            'state' => $this->faker->stateAbbr(),
            'username' => $this->faker->unique()->username(),
            'zip' => $this->faker->postcode(),
            'created_by' => 1,
        ];
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

    public function deleted(): self
    {
        return $this->state(['deleted_at' => $this->faker->dateTime()]);
    }
}
