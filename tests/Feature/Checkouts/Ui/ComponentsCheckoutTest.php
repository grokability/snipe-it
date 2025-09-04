<?php

namespace Tests\Feature\Checkouts\Ui;

use App\Events\CheckoutableCheckedOut;
use App\Models\Asset;
use App\Models\Company;
use App\Models\Component;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class ComponentsCheckoutTest extends TestCase
{
    public function testCheckingOutComponentRequiresCorrectPermission()
    {
        $this->actingAs(User::factory()->create())
            ->post(route('components.checkout.store', [
                'componentID' => Component::factory()->checkedOutToAsset()->create()->id,
            ]))
            ->assertForbidden();
    }

    public function testPageRenders()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('components.checkout.show', Component::factory()->create()->id))
            ->assertOk();
    }

    public function test_cannot_checkout_across_companies_when_full_company_support_enabled()
    {
        Event::fake([CheckoutableCheckedOut::class]);

        $this->settings->enableMultipleFullCompanySupport();

        [$assetCompany, $componentCompany] = Company::factory()->count(2)->create();

        $asset = Asset::factory()->for($assetCompany)->create();
        $component = Component::factory()->for($componentCompany)->create();

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('components.checkout.store', $component), [
                'asset_id' => $asset->id,
                'assigned_qty' => '1',
                'redirect_option' => 'index',
            ]);

        Event::assertNotDispatched(CheckoutableCheckedOut::class);
    }

    public function testComponentCheckoutPagePostIsRedirectedIfRedirectSelectionIsIndex()
    {
        $component = Component::factory()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->from(route('components.index'))
            ->post(route('components.checkout.store', $component), [
                'asset_id' => Asset::factory()->create()->id,
                'redirect_option' => 'index',
                'assigned_qty' => 1,
            ])
            ->assertStatus(302)
            ->assertRedirect(route('components.index'));
        $this->assertHasTheseActionLogs($component, ['create', 'checkout']);
    }

    public function testComponentCheckoutPagePostIsRedirectedIfRedirectSelectionIsItem()
    {
        $component = Component::factory()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->from(route('components.index'))
            ->post(route('components.checkout.store' , $component), [
                'asset_id' =>  Asset::factory()->create()->id,
                'redirect_option' => 'item',
                'assigned_qty' => 1,
            ])
            ->assertStatus(302)
            ->assertRedirect(route('components.show', $component));
        $this->assertHasTheseActionLogs($component, ['create', 'checkout']);
    }

    public function testComponentCheckoutPagePostIsRedirectedIfRedirectSelectionIsTarget()
    {
        $asset = Asset::factory()->create();
        $component = Component::factory()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->from(route('components.index'))
            ->post(route('components.checkout.store' , $component), [
                'asset_id' => $asset->id,
                'redirect_option' => 'target',
                'assigned_qty' => 1,
            ])
            ->assertStatus(302)
            ->assertRedirect(route('hardware.show', $asset));
        $this->assertHasTheseActionLogs($component, ['create', 'checkout']);
    }
}
