<?php

namespace Tests\Feature\Accessories\Api;

use App\Models\Accessory;
use App\Models\Company;
use App\Models\User;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\TestsFullMultipleCompaniesSupport;
use Tests\Concerns\TestsPermissionsRequirement;
use Tests\TestCase;

class DeleteAccessoriesTest extends TestCase implements TestsFullMultipleCompaniesSupport, TestsPermissionsRequirement
{
    public function testRequiresPermission()
    {
        $accessory = Accessory::factory()->create();

        $this->actingAsForApi(User::factory()->create())
            ->deleteJson(route('api.accessories.destroy', $accessory))
            ->assertForbidden();

        $this->assertNotSoftDeleted($accessory);
    }

    public function testAdheresToFullMultipleCompaniesSupportScoping()
    {
        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $accessoryA = Accessory::factory()->for($companyA)->create();
        $accessoryB = Accessory::factory()->for($companyB)->create();
        $accessoryC = Accessory::factory()->for($companyB)->create();

        $superUser = $companyA->users()->save(User::factory()->superuser()->make());
        $userInCompanyA = $companyA->users()->save(User::factory()->deleteAccessories()->make());
        $userInCompanyB = $companyB->users()->save(User::factory()->deleteAccessories()->make());

        $this->settings->enableMultipleFullCompanySupport();

        $this->actingAsForApi($userInCompanyA)
            ->deleteJson(route('api.accessories.destroy', $accessoryB))
            ->assertStatusMessageIs('error');

        $this->actingAsForApi($userInCompanyB)
            ->deleteJson(route('api.accessories.destroy', $accessoryA))
            ->assertStatusMessageIs('error');

        $this->actingAsForApi($superUser)
            ->deleteJson(route('api.accessories.destroy', $accessoryC))
            ->assertStatusMessageIs('success');

        $this->assertNotSoftDeleted($accessoryA);
        $this->assertNotSoftDeleted($accessoryB);
        $this->assertSoftDeleted($accessoryC);
    }

    public static function checkedOutAccessories()
    {
        yield 'checked out to user' => [fn() => Accessory::factory()->checkedOutToUser()->create()];
        yield 'checked out to asset' => [fn() => Accessory::factory()->checkedOutToAsset()->create()];
        yield 'checked out to location' => [fn() => Accessory::factory()->checkedOutToLocation()->create()];
    }

    #[DataProvider('checkedOutAccessories')]
    public function testCannotDeleteAccessoryThatHasCheckouts($data)
    {
        $accessory = $data();

        $this->actingAsForApi(User::factory()->deleteAccessories()->create())
            ->deleteJson(route('api.accessories.destroy', $accessory))
            ->assertStatusMessageIs('error');

        $this->assertNotSoftDeleted($accessory);
    }

    public function testCanDeleteAccessory()
    {
        $accessory = Accessory::factory()->create();

        $this->actingAsForApi(User::factory()->deleteAccessories()->create())
            ->deleteJson(route('api.accessories.destroy', $accessory))
            ->assertStatusMessageIs('success');

        $this->assertSoftDeleted($accessory);
    }
}
