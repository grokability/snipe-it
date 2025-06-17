<?php

namespace Tests\Feature\Notifications\Email;

use App\Models\Accessory;
use App\Models\Asset;
use App\Models\Consumable;
use App\Models\License;
use App\Models\LicenseSeat;
use App\Models\User;
use App\Notifications\CurrentInventory;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class CurrentInventoryTest extends TestCase
{
    public function test_must_have_permission_to_send_current_inventory_email()
    {
        $this->actingAs(User::factory()->create())
            ->post(route('users.email', User::factory()->create()->id))
            ->assertForbidden();
    }

    public function test_handles_attempt_to_send_to_user_without_email_address()
    {
        $user = User::factory()->create(['email' => null]);

        $this->actingAs(User::factory()->viewUsers()->create())
            ->post(route('users.email', $user->id))
            ->assertSessionHas('error');
    }

    public function test_can_send_users_current_inventory()
    {
        Notification::fake();

        $user = User::factory()->create(['email' => 'hi@there.com']);

        $this->actingAs(User::factory()->viewUsers()->create())
            ->post(route('users.email', $user->id))
            ->assertSessionHas('success');

        Notification::assertCount(1);
        Notification::assertSentTo($user, CurrentInventory::class);
    }

    public function test_current_inventory_contents()
    {
        $user = User::factory()->create();

        Asset::factory()->assignedToUser($user)->create(['asset_tag' => 'complex-asset-tag']);
        Accessory::factory()->checkedOutToUser($user)->create(['name' => 'Complex Accessory Name']);
        LicenseSeat::factory()->for(License::factory()->state(['name' => 'Complex License Name']))->assignedToUser($user)->create();
        Consumable::factory()->checkedOutToUser($user)->create(['name' => 'Complex Consumable Name']);

        $emailContents = (new CurrentInventory($user))->toMail()->render();

        $this->assertStringContainsString('complex-asset-tag', $emailContents);
        $this->assertStringContainsString('Complex Accessory Name', $emailContents);
        $this->assertStringContainsString('Complex License Name', $emailContents);
        $this->assertStringContainsString('Complex Consumable Name', $emailContents);
    }

    public function test_current_inventory_does_not_include_child_assets_when_disabled()
    {
        $this->settings->disableShowingAssignedAssets();

        $user = User::factory()->create();

        $parentAsset = Asset::factory()->assignedToUser($user)->create(['asset_tag' => 'parent-asset-tag']);
        Asset::factory()->assignedToAsset($parentAsset)->create(['asset_tag' => 'child-asset-tag']);

        $emailContents = (new CurrentInventory($user))->toMail()->render();

        $this->assertStringContainsString('parent-asset-tag', $emailContents);
        $this->assertStringNotContainsString('child-asset-tag', $emailContents);
    }

    public function test_current_inventory_includes_child_assets_when_enabled()
    {
        $this->settings->enableShowingAssignedAssets();

        $user = User::factory()->create();

        $parentAsset = Asset::factory()->assignedToUser($user)->create(['asset_tag' => 'parent-asset-tag']);
        Asset::factory()->assignedToAsset($parentAsset)->create(['asset_tag' => 'child-asset-tag']);

        $emailContents = (new CurrentInventory($user))->toMail()->render();

        $this->assertStringContainsString('parent-asset-tag', $emailContents);
        $this->assertStringContainsString('child-asset-tag', $emailContents);
    }
}
