<?php

namespace Tests\Feature\Consumables\Ui;

use App\Models\Company;
use App\Models\Consumable;
use App\Models\User;
use Tests\TestCase;

class DeleteConsumableTest extends TestCase
{
    public function test_requires_permission_to_delete_consumable()
    {
        $this->actingAs(User::factory()->create())
            ->delete(route('consumables.destroy', Consumable::factory()->create()->id))
            ->assertForbidden();
    }

    public function test_cannot_delete_consumable_from_another_company()
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

    public function test_can_delete_consumable()
    {
        $consumable = Consumable::factory()->create();

        $this->actingAs(User::factory()->deleteConsumables()->create())
            ->delete(route('consumables.destroy', $consumable->id))
            ->assertRedirect(route('consumables.index'));

        $this->assertSoftDeleted($consumable);
    }
}
