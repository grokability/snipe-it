<?php

namespace Tests\Feature;

use App\Models\Accessory;
use App\Models\Asset;
use App\Models\Component;
use App\Models\Consumable;
use App\Models\License;
use App\Models\User;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    public function testUsersWithoutAdminAccessAreRedirected()
    {
        $this->actingAs(User::factory()->create())
            ->get(route('home'))
            ->assertRedirect(route('view-assets'));
    }

    public function testCountsAreLoadedCorrectlyForAdmins()
    {
        Asset::factory()->count(2)->create();
        Accessory::factory()->count(2)->create();
        License::factory()->count(2)->create();
        Consumable::factory()->count(2)->create();
        Component::factory()->count(2)->create();

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('home'))
            ->assertViewIs('dashboard')
            ->assertViewHas('counts', function ($value) {
                $accessoryCount = Accessory::count();
                $assetCount = Asset::count();
                $componentCount = Component::count();
                $consumableCount = Consumable::count();
                $licenseCount = License::assetcount();
                $userCount = User::count();

                $this->assertEquals($value['accessory'], $accessoryCount, 'Accessory count incorrect.');
                $this->assertEquals($value['asset'], $assetCount, 'Asset count incorrect.');
                $this->assertEquals($value['license'], $licenseCount, 'License count incorrect.');
                $this->assertEquals($value['consumable'], $consumableCount, 'Consumable count incorrect.');
                $this->assertEquals($value['component'], $componentCount, 'Component count incorrect.');
                $this->assertEquals($value['user'], $userCount, 'User count incorrect.');
                $this->assertEquals(
                    $value['grand_total'],
                    $accessoryCount + $assetCount + $consumableCount + $licenseCount,
                    'Grand total count incorrect.'
                );

                return true;
            });
    }
}
