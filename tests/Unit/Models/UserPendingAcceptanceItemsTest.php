<?php

namespace Tests\Unit\Models;

use App\Models\Accessory;
use App\Models\Asset;
use App\Models\CheckoutAcceptance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserPendingAcceptanceItemsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_only_items_with_pending_acceptances_for_the_user(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $pendingAssetAcceptance = CheckoutAcceptance::factory()
            ->pending()
            ->for($user, 'assignedTo')
            ->create();

        $pendingAccessoryAcceptance = CheckoutAcceptance::factory()
            ->forAccessory()
            ->pending()
            ->for($user, 'assignedTo')
            ->create();

        CheckoutAcceptance::factory()
            ->accepted()
            ->for($user, 'assignedTo')
            ->create();

        CheckoutAcceptance::factory()
            ->pending()
            ->for($otherUser, 'assignedTo')
            ->create();

        $items = $user->getAssignedItemsWithPendingAcceptance();

        $this->assertCount(2, $items);
        $this->assertTrue($items->contains(fn ($item) => $item instanceof Asset && $item->is($pendingAssetAcceptance->checkoutable)));
        $this->assertTrue($items->contains(fn ($item) => $item instanceof Accessory && $item->is($pendingAccessoryAcceptance->checkoutable)));
    }

    public function test_it_returns_unique_items_when_multiple_pending_acceptances_exist_for_the_same_item(): void
    {
        $user = User::factory()->create();
        $asset = Asset::factory()->create();

        CheckoutAcceptance::factory()
            ->pending()
            ->for($asset, 'checkoutable')
            ->for($user, 'assignedTo')
            ->create();

        CheckoutAcceptance::factory()
            ->pending()
            ->for($asset, 'checkoutable')
            ->for($user, 'assignedTo')
            ->create();

        $items = $user->getAssignedItemsWithPendingAcceptance();

        $this->assertCount(1, $items);
        $this->assertTrue($items->first() instanceof Asset);
        $this->assertTrue($items->first()->is($asset));
    }
}
