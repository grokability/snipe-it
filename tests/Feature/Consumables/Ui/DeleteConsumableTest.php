<?php

namespace Tests\Feature\Consumables\Ui;

use App\Models\Company;
use App\Models\Consumable;
use App\Models\User;
use Tests\TestCase;

class DeleteConsumableTest extends TestCase
{
    public function testRequiresPermissionToDeleteConsumable()
    {
        $this->actingAs(User::factory()->create())
            ->delete(route('consumables.destroy', Consumable::factory()->create()->id))
            ->assertForbidden();
    }

    public function testCannotDeleteConsumableFromAnotherCompany()
    {
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $consumableForCompanyA = Consumable::factory()->for($companyA)->create();
        $userForCompanyB = User::factory()->deleteConsumables()->for($companyB)->create();

        $this->actingAs($userForCompanyB)
            ->delete(route('consumables.destroy', $consumableForCompanyA->id))
            ->assertRedirect(route('consumables.index'));

        $this->assertNotSoftDeleted($consumableForCompanyA);
    }

    public function testCanDeleteConsumable()
    {
        $consumable = Consumable::factory()->create();

        $this->actingAs(User::factory()->deleteConsumables()->create())
            ->delete(route('consumables.destroy', $consumable->id))
            ->assertRedirect(route('consumables.index'));

        $this->assertSoftDeleted($consumable);
    }
}
