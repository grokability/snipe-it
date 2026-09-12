<?php

namespace Tests\Unit\Models\Consumable;

use App\Models\CheckoutAcceptance;
use App\Models\Consumable;
use App\Models\User;
use Tests\TestCase;

class ConsumableDeletionTest extends TestCase
{
    public function test_deleting_a_consumable_clears_its_pending_acceptances()
    {
        $user = User::factory()->create();
        $consumable = Consumable::factory()->requiringAcceptance()->checkedOutToUser($user)->create();

        $acceptance = $this->pendingAcceptanceFor($consumable, $user);

        $consumable->delete();

        $this->assertSoftDeleted($consumable);
        $this->assertAcceptanceWasSoftDeleted($acceptance);
    }

    public function test_deleting_a_consumable_leaves_other_consumables_acceptances_alone()
    {
        $user = User::factory()->create();

        $deleted = Consumable::factory()->requiringAcceptance()->checkedOutToUser($user)->create();
        $untouched = Consumable::factory()->requiringAcceptance()->checkedOutToUser($user)->create();

        $deletedAcceptance = $this->pendingAcceptanceFor($deleted, $user);
        $untouchedAcceptance = $this->pendingAcceptanceFor($untouched, $user);

        $deleted->delete();

        $this->assertAcceptanceWasSoftDeleted($deletedAcceptance);
        $this->assertAcceptanceSurvived($untouchedAcceptance);
    }

    public function test_deleting_a_consumable_leaves_answered_acceptances_alone()
    {
        $user = User::factory()->create();
        $consumable = Consumable::factory()->requiringAcceptance()->checkedOutToUser($user)->create();

        $accepted = CheckoutAcceptance::factory()
            ->withoutActionLog()
            ->accepted()
            ->for($user, 'assignedTo')
            ->for($consumable, 'checkoutable')
            ->create();

        $declined = CheckoutAcceptance::factory()
            ->withoutActionLog()
            ->declined()
            ->for($user, 'assignedTo')
            ->for($consumable, 'checkoutable')
            ->create();

        $consumable->delete();

        $this->assertAcceptanceSurvived($accepted);
        $this->assertAcceptanceSurvived($declined);
    }

    private function pendingAcceptanceFor(Consumable $consumable, User $user): CheckoutAcceptance
    {
        return CheckoutAcceptance::factory()
            ->withoutActionLog()
            ->pending()
            ->for($user, 'assignedTo')
            ->for($consumable, 'checkoutable')
            ->create();
    }

    private function assertAcceptanceWasSoftDeleted(CheckoutAcceptance $acceptance): void
    {
        $this->assertNotNull(
            CheckoutAcceptance::withTrashed()->find($acceptance->id)?->deleted_at,
            'Expected the pending acceptance to be soft-deleted when the consumable was deleted.'
        );
    }

    private function assertAcceptanceSurvived(CheckoutAcceptance $acceptance): void
    {
        $this->assertNull(
            CheckoutAcceptance::withTrashed()->find($acceptance->id)?->deleted_at,
            'Expected this acceptance to be left alone when the consumable was deleted.'
        );
    }
}
