<?php

namespace Tests\Feature\Accessories\Ui;

use App\Models\Accessory;
use App\Models\Company;
use App\Models\User;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class DeleteAccessoryTest extends TestCase
{
    public function testRequiresPermissionToDeleteAccessory()
    {
        $this->actingAs(User::factory()->create())
            ->delete(route('accessories.destroy', Accessory::factory()->create()->id))
            ->assertForbidden();
    }

    public function testCannotDeleteAccessoryFromAnotherCompany()
    {
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();
        $accessoryForCompanyA = Accessory::factory()->for($companyA)->create();
        $userForCompanyB = User::factory()->for($companyB)->deleteAccessories()->create();

        $this->actingAs($userForCompanyB)->delete(route('accessories.destroy', $accessoryForCompanyA->id));

        $this->assertFalse($accessoryForCompanyA->refresh()->trashed(), 'Accessory should not be deleted');
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

        $this->actingAs(User::factory()->deleteAccessories()->create())
            ->delete(route('accessories.destroy', $accessory->id))
            ->assertSessionHas('error')
            ->assertRedirect(route('accessories.index'));

        $this->assertFalse($accessory->refresh()->trashed(), 'Accessory should not be deleted');
    }

    public function testCanDeleteAccessory()
    {

        $accessory = Accessory::factory()->create();

        $this->actingAs(User::factory()->deleteAccessories()->create())
            ->delete(route('accessories.destroy', $accessory->id))
            ->assertRedirect(route('accessories.index'));

        $this->assertTrue($accessory->refresh()->trashed(), 'Accessory should be deleted');
    }
}
