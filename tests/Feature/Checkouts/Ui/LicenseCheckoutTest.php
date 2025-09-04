<?php

namespace Tests\Feature\Checkouts\Ui;

use App\Models\Asset;
use App\Models\License;
use App\Models\LicenseSeat;
use App\Models\User;
use Tests\TestCase;

class LicenseCheckoutTest extends TestCase
{
    public function testPageRenders()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('licenses.checkout', License::factory()->create()->id))
            ->assertOk();
    }

    public function testNotesAreStoredInActionLogOnCheckoutToAsset()
    {
        $admin = User::factory()->superuser()->create();
        $asset = Asset::factory()->create();
        $licenseSeat = LicenseSeat::factory()->create();

        $this->actingAs($admin)
            ->post(route('licenses.checkout', $licenseSeat->license), [
                'checkout_to_type' => 'asset',
                'assigned_to' => null,
                'asset_id' => $asset->id,
                'notes' => 'oh hi there',
            ]);

        $this->assertDatabaseHas('action_logs', [
            'action_type' => 'checkout',
            'target_id' => $asset->id,
            'target_type' => Asset::class,
            'item_id' => $licenseSeat->license->id,
            'item_type' => License::class,
            'note' => 'oh hi there',
        ]);
        $this->assertHasTheseActionLogs($licenseSeat->license, ['add seats', 'create', 'checkout']); // TODO - TOTALLY out-of-order
    }

    public function testNotesAreStoredInActionLogOnCheckoutToUser()
    {
        $admin = User::factory()->superuser()->create();
        $licenseSeat = LicenseSeat::factory()->create();

        $this->actingAs($admin)
            ->post(route('licenses.checkout', $licenseSeat->license), [
                'checkout_to_type' => 'user',
                'assigned_to' => $admin->id,
                'asset_id' => null,
                'notes' => 'oh hi there',
            ]);

        $this->assertDatabaseHas('action_logs', [
            'action_type' => 'checkout',
            'target_id' => $admin->id,
            'target_type' => User::class,
            'item_id' => $licenseSeat->license->id,
            'item_type' => License::class,
            'note' => 'oh hi there',
        ]);
        $this->assertHasTheseActionLogs($licenseSeat->license, ['add seats', 'create', 'checkout']); //FIXME - out-of-order
    }

    public function testLicenseCheckoutPagePostIsRedirectedIfRedirectSelectionIsIndex()
    {
        $license = License::factory()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->from(route('licenses.checkout', $license))
            ->post(route('licenses.checkout', $license), [
                'assigned_to' =>  User::factory()->create()->id,
                'redirect_option' => 'index',
                'assigned_qty' => 1,
            ])
            ->assertStatus(302)
            ->assertRedirect(route('licenses.index'));
    }

    public function testLicenseCheckoutPagePostIsRedirectedIfRedirectSelectionIsItem()
    {
        $license = License::factory()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->from(route('licenses.checkout', $license))
            ->post(route('licenses.checkout', $license), [
                'assigned_to' =>  User::factory()->create()->id,
                'redirect_option' => 'item',
            ])
            ->assertStatus(302)
            ->assertRedirect(route('licenses.show', $license));
    }

    public function testLicenseCheckoutPagePostIsRedirectedIfRedirectSelectionIsUserTarget()
    {
        $user = User::factory()->create();
        $license = License::factory()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->from(route('licenses.checkout', $license))
            ->post(route('licenses.checkout' , $license), [
                'assigned_to' =>  $user->id,
                'redirect_option' => 'target',
            ])
            ->assertStatus(302)
            ->assertRedirect(route('users.show', $user));
    }
    public function testLicenseCheckoutPagePostIsRedirectedIfRedirectSelectionIsAssetTarget()
    {
        $asset = Asset::factory()->create();
        $license = License::factory()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->from(route('licenses.checkout', $license))
            ->post(route('licenses.checkout' , $license), [
                'asset_id' =>  $asset->id,
                'redirect_option' => 'target',
            ])
            ->assertStatus(302)
            ->assertRedirect(route('hardware.show', $asset));
    }
}
