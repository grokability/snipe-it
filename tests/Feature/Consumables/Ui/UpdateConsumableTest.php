<?php

namespace Tests\Feature\Consumables\Ui;

use App\Models\Category;
use App\Models\Company;
use App\Models\Consumable;
use App\Models\Location;
use App\Models\Manufacturer;
use App\Models\Supplier;
use App\Models\User;
use Tests\TestCase;

class UpdateConsumableTest extends TestCase
{
    public function testRequiresPermissionToSeeEditConsumablePage()
    {
        $this->actingAs(User::factory()->create())
            ->get(route('consumables.edit', Consumable::factory()->create()))
            ->assertForbidden();
    }

    public function testDoesNotShowEditConsumablePageFromAnotherCompany()
    {
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();
        $consumableForCompanyA = Consumable::factory()->for($companyA)->create();
        $userForCompanyB = User::factory()->editConsumables()->for($companyB)->create();

        $this->actingAs($userForCompanyB)
            ->get(route('consumables.edit', $consumableForCompanyA))
            ->assertRedirect(route('consumables.index'));
    }

    public function testEditConsumablePageRenders()
    {
        $this->actingAs(User::factory()->editConsumables()->create())
            ->get(route('consumables.edit', Consumable::factory()->create()))
            ->assertOk()
            ->assertViewIs('consumables.edit');
    }

    public function testCannotUpdateConsumableBelongingToAnotherCompany()
    {
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $consumableForCompanyA = Consumable::factory()->for($companyA)->create();
        $userForCompanyB = User::factory()->editConsumables()->for($companyB)->create();

        $this->actingAs($userForCompanyB)
            ->put(route('consumables.update', $consumableForCompanyA), [
                //
            ])
            ->assertStatus(302);
    }

    public function testCannotSetQuantityToAmountLowerThanWhatIsCheckedOut()
    {
        $user = User::factory()->createConsumables()->editConsumables()->create();
        $consumable = Consumable::factory()->create(['qty' => 2]);

        $consumable->users()->attach($consumable->id, ['consumable_id' => $consumable->id, 'assigned_to' => $user->id]);
        $consumable->users()->attach($consumable->id, ['consumable_id' => $consumable->id, 'assigned_to' => $user->id]);

        $this->assertEquals(2, $consumable->numCheckedOut());

        $this->actingAs($user)
            ->put(route('consumables.update', $consumable->id), [
                'qty' => 1,
                'redirect_option' => 'index',
                'category_type' => 'consumable',
            ])
            ->assertSessionHasErrors('qty');

    }

    public function testCanUpdateConsumable()
    {
        $consumable = Consumable::factory()->create();

        $data = [
            'company_id' => Company::factory()->create()->id,
            'name' => 'My Consumable',
            'category_id' => Category::factory()->consumableInkCategory()->create()->id,
            'supplier_id' => Supplier::factory()->create()->id,
            'manufacturer_id' => Manufacturer::factory()->create()->id,
            'location_id' => Location::factory()->create()->id,
            'model_number' => '8765',
            'item_no' => '5678',
            'order_number' => '908',
            'purchase_date' => '2024-12-05',
            'purchase_cost' => '89.45',
            'qty' => '9',
            'min_amt' => '7',
            'notes' => 'Some Notes',
        ];

        $this->actingAs(User::factory()->createConsumables()->editConsumables()->create())
            ->put(route('consumables.update', $consumable), $data + [
                    'redirect_option' => 'index',
                    'category_type' => 'consumable',
                ])
            ->assertRedirect(route('consumables.index'));

        $this->assertDatabaseHas('consumables', $data);
    }
}
