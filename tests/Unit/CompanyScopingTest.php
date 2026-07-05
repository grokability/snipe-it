<?php

namespace Tests\Unit;

use App\Models\Accessory;
use App\Models\Asset;
use App\Models\Company;
use App\Models\Component;
use App\Models\Consumable;
use App\Models\License;
use App\Models\LicenseSeat;
use App\Models\Maintenance;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CompanyScopingTest extends TestCase
{
    public static function models(): array
    {
        return [
            'Accessories' => [Accessory::class],
            'Assets' => [Asset::class],
            'Components' => [Component::class],
            'Consumables' => [Consumable::class],
            'Licenses' => [License::class],
        ];
    }

    #[DataProvider('models')]
    public function test_company_scoping($model)
    {
        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $modelA = $model::factory()->for($companyA)->create();
        $modelB = $model::factory()->for($companyB)->create();

        $superUser = $companyA->users()->save(User::factory()->superuser()->make());
        $userInCompanyA = $companyA->users()->save(User::factory()->make());
        $userInCompanyB = $companyB->users()->save(User::factory()->make());

        $this->settings->disableMultipleFullCompanySupport();

        $this->actingAs($superUser);
        $this->assertCanSee($modelA);
        $this->assertCanSee($modelB);

        $this->actingAs($userInCompanyA);
        $this->assertCanSee($modelA);
        $this->assertCanSee($modelB);

        $this->actingAs($userInCompanyB);
        $this->assertCanSee($modelA);
        $this->assertCanSee($modelB);

        $this->settings->enableMultipleFullCompanySupport();

        $this->actingAs($superUser);
        $this->assertCanSee($modelA);
        $this->assertCanSee($modelB);

        $this->actingAs($userInCompanyA);
        $this->assertCanSee($modelA);
        $this->assertCannotSee($modelB);

        $this->actingAs($userInCompanyB);
        $this->assertCannotSee($modelA);
        $this->assertCanSee($modelB);
    }

    public function test_maintenance_company_scoping()
    {
        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $maintenanceForCompanyA = Maintenance::factory()->for(Asset::factory()->for($companyA))->create();
        $maintenanceForCompanyB = Maintenance::factory()->for(Asset::factory()->for($companyB))->create();

        $superUser = $companyA->users()->save(User::factory()->superuser()->make());
        $userInCompanyA = $companyA->users()->save(User::factory()->make());
        $userInCompanyB = $companyB->users()->save(User::factory()->make());

        $this->settings->disableMultipleFullCompanySupport();

        $this->actingAs($superUser);
        $this->assertCanSee($maintenanceForCompanyA);
        $this->assertCanSee($maintenanceForCompanyB);

        $this->actingAs($userInCompanyA);
        $this->assertCanSee($maintenanceForCompanyA);
        $this->assertCanSee($maintenanceForCompanyB);

        $this->actingAs($userInCompanyB);
        $this->assertCanSee($maintenanceForCompanyA);
        $this->assertCanSee($maintenanceForCompanyB);

        $this->settings->enableMultipleFullCompanySupport();

        $this->actingAs($superUser);
        $this->assertCanSee($maintenanceForCompanyA);
        $this->assertCanSee($maintenanceForCompanyB);

        $this->actingAs($userInCompanyA);
        $this->assertCanSee($maintenanceForCompanyA);
        $this->assertCannotSee($maintenanceForCompanyB);

        $this->actingAs($userInCompanyB);
        $this->assertCannotSee($maintenanceForCompanyA);
        $this->assertCanSee($maintenanceForCompanyB);
    }

    public function test_license_seat_company_scoping()
    {
        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $licenseSeatA = LicenseSeat::factory()->for(Asset::factory()->for($companyA))->create();
        $licenseSeatB = LicenseSeat::factory()->for(Asset::factory()->for($companyB))->create();

        $superUser = $companyA->users()->save(User::factory()->superuser()->make());
        $userInCompanyA = $companyA->users()->save(User::factory()->make());
        $userInCompanyB = $companyB->users()->save(User::factory()->make());

        $this->settings->disableMultipleFullCompanySupport();

        $this->actingAs($superUser);
        $this->assertCanSee($licenseSeatA);
        $this->assertCanSee($licenseSeatB);

        $this->actingAs($userInCompanyA);
        $this->assertCanSee($licenseSeatA);
        $this->assertCanSee($licenseSeatB);

        $this->actingAs($userInCompanyB);
        $this->assertCanSee($licenseSeatA);
        $this->assertCanSee($licenseSeatB);

        $this->settings->enableMultipleFullCompanySupport();

        $this->actingAs($superUser);
        $this->assertCanSee($licenseSeatA);
        $this->assertCanSee($licenseSeatB);

        $this->actingAs($userInCompanyA);
        $this->assertCanSee($licenseSeatA);
        $this->assertCannotSee($licenseSeatB);

        $this->actingAs($userInCompanyB);
        $this->assertCannotSee($licenseSeatA);
        $this->assertCanSee($licenseSeatB);
    }

    #[DataProvider('models')]
    public function test_company_user_cannot_see_null_company_items_in_strict_mode($model)
    {
        $company = Company::factory()->create();
        $nullCompanyItem = $model::factory()->create(['company_id' => null]);
        $companyItem = $model::factory()->for($company)->create();
        $companyUser = $company->users()->save(User::factory()->make());

        $this->settings->enableMultipleFullCompanySupport();

        $this->actingAs($companyUser);
        $this->assertCannotSee($nullCompanyItem);
        $this->assertCanSee($companyItem);
    }

    #[DataProvider('models')]
    public function test_company_user_can_see_null_company_items_in_floater_mode($model)
    {
        $company = Company::factory()->create();
        $nullCompanyItem = $model::factory()->create(['company_id' => null]);
        $companyItem = $model::factory()->for($company)->create();
        $companyUser = $company->users()->save(User::factory()->make());

        $this->settings->enableFloaterMode();

        $this->actingAs($companyUser);
        $this->assertCanSee($nullCompanyItem);
        $this->assertCanSee($companyItem);
    }

    #[DataProvider('models')]
    public function test_null_company_user_cannot_see_company_items_in_strict_mode($model)
    {
        $company = Company::factory()->create();
        $nullCompanyItem = $model::factory()->create(['company_id' => null]);
        $companyItem = $model::factory()->for($company)->create();
        $nullCompanyUser = User::factory()->create(['company_id' => null]);

        $this->settings->enableMultipleFullCompanySupport();

        $this->actingAs($nullCompanyUser);
        $this->assertCanSee($nullCompanyItem);
        $this->assertCannotSee($companyItem);
    }

    #[DataProvider('models')]
    public function test_null_company_user_can_see_all_items_in_floater_mode($model)
    {
        $company = Company::factory()->create();
        $nullCompanyItem = $model::factory()->create(['company_id' => null]);
        $companyItem = $model::factory()->for($company)->create();
        $nullCompanyUser = User::factory()->create(['company_id' => null]);

        $this->settings->enableFloaterMode();

        $this->actingAs($nullCompanyUser);
        $this->assertCanSee($nullCompanyItem);
        $this->assertCanSee($companyItem);
    }

    public function test_company_scoped_user_can_see_null_company_users_in_floater_mode()
    {
        $company = Company::factory()->create();
        $companyUser = $company->users()->save(User::factory()->make());
        $nullCompanyUser = User::factory()->create(['company_id' => null]);

        $this->settings->enableFloaterMode();

        $this->actingAs($companyUser);
        $this->assertCanSee($nullCompanyUser);
    }

    public function test_company_scoped_user_cannot_see_null_company_users_in_strict_mode()
    {
        $company = Company::factory()->create();
        $companyUser = $company->users()->save(User::factory()->make());
        $nullCompanyUser = User::factory()->create(['company_id' => null]);

        $this->settings->enableMultipleFullCompanySupport();

        $this->actingAs($companyUser);
        $this->assertCannotSee($nullCompanyUser);
    }

    private function assertCanSee(Model $model)
    {
        $this->assertTrue(
            get_class($model)::all()->contains($model),
            'User was not able to see expected model'
        );
    }

    private function assertCannotSee(Model $model)
    {
        $this->assertFalse(
            get_class($model)::all()->contains($model),
            'User was able to see model from a different company'
        );
    }
}
