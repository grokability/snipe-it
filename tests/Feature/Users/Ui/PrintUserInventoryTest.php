<?php

namespace Tests\Feature\Users\Ui;

use App\Models\Asset;
use App\Models\Company;
use App\Models\User;
use Tests\TestCase;

class PrintUserInventoryTest extends TestCase
{
    public function testPermissionRequiredToPrintUserInventory()
    {
        $this->actingAs(User::factory()->create())
            ->get(route('users.print', User::factory()->create()))
            ->assertStatus(403);
    }

    public function testCanPrintUserInventory()
    {
        $actor = User::factory()->viewUsers()->create();

        $this->actingAs($actor)
            ->get(route('users.print', User::factory()->create()))
            ->assertOk()
            ->assertStatus(200);
    }

    public function testCannotPrintUserInventoryFromAnotherCompany()
    {
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $actor = User::factory()->for($companyA)->viewUsers()->create();
        $user = User::factory()->for($companyB)->create();

        $this->actingAs($actor)
            ->get(route('users.print', $user))
            ->assertStatus(302);
    }

    public function testPrintingUserInventoryDoesNotIncludeChildAssetsWhenDisabled()
    {
        $this->settings->disableShowingAssignedAssets();

        $actor = User::factory()->viewUsers()->create();
        $user = User::factory()->create();

        $parentAsset = Asset::factory()->assignedToUser($user)->create(['asset_tag' => 'parent-asset-tag']);
        Asset::factory()->assignedToAsset($parentAsset)->create(['asset_tag' => 'child-asset-tag']);

        $this->actingAs($actor)
            ->get(route('users.print', $user->id))
            ->assertSeeText('parent-asset-tag')
            ->assertDontSeeText('child-asset-tag');
    }

    public function testPrintingUserInventoryIncludesChildAssetsWhenEnabled()
    {
        $this->settings->enableShowingAssignedAssets();

        $actor = User::factory()->viewUsers()->create();
        $user = User::factory()->create();

        $parentAsset = Asset::factory()->assignedToUser($user)->create(['asset_tag' => 'parent-asset-tag']);
        Asset::factory()->assignedToAsset($parentAsset)->create(['asset_tag' => 'child-asset-tag']);

        $this->actingAs($actor)
            ->get(route('users.print', $user->id))
            ->assertSeeText('parent-asset-tag')
            ->assertSeeText('child-asset-tag');
    }
}
