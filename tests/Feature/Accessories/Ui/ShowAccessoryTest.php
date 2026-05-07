<?php

namespace Tests\Feature\Accessories\Ui;

use App\Models\Accessory;
use App\Models\Company;
use App\Models\User;
use Tests\TestCase;

class ShowAccessoryTest extends TestCase
{
    public function test_requires_permission_to_view_accessory()
    {
        $this->actingAs(User::factory()->create())
            ->get(route('accessories.show', Accessory::factory()->create()))
            ->assertForbidden();
    }

    public function test_cannot_view_accessory_from_another_company()
    {
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();
        $accessoryForCompanyA = Accessory::factory()->for($companyA)->create();
        $userForCompanyB = User::factory()->for($companyB)->viewAccessories()->create();

        $this->actingAs($userForCompanyB)
            ->get(route('accessories.show', $accessoryForCompanyA))
            ->assertStatus(302);
    }

    public function test_can_view_accessory()
    {
        $accessory = Accessory::factory()->create();

        $this->actingAs(User::factory()->viewAccessories()->create())
            ->get(route('accessories.show', $accessory))
            ->assertOk()
            ->assertViewIs('accessories.view')
            ->assertViewHas(['accessory' => $accessory]);
    }

    public function test_page_renders()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('accessories.show', Accessory::factory()->create()))
            ->assertOk();

    }

    public function test_handles_accessory_creator_not_existing()
    {
        $accessory = Accessory::factory()->create(['created_by' => 999999]);

        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('accessories.show', $accessory))
            ->assertOk();
    }
}
