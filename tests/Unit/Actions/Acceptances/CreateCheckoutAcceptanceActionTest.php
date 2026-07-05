<?php

namespace Tests\Unit\Actions\Acceptances;

use App\Actions\Acceptances\CreateCheckoutAcceptanceAction;
use App\Models\Asset;
use App\Models\User;
use Tests\TestCase;

class CreateCheckoutAcceptanceActionTest extends TestCase
{
    public function test_it_creates_a_pending_acceptance_associated_with_the_item_and_user(): void
    {
        $asset = Asset::factory()->create();
        $assignedUser = User::factory()->create();

        $acceptance = CreateCheckoutAcceptanceAction::run($asset, $assignedUser);

        $this->assertTrue($acceptance->exists);
        $this->assertTrue($acceptance->isPending());
        $this->assertTrue($acceptance->checkoutable->is($asset));
        $this->assertTrue($acceptance->assignedTo->is($assignedUser));
    }

    public function test_it_leaves_qty_and_alert_null_when_not_provided(): void
    {
        $acceptance = CreateCheckoutAcceptanceAction::run(Asset::factory()->create(), User::factory()->create());

        $this->assertNull($acceptance->qty);
        $this->assertNull($acceptance->alert_on_response_id);
    }

    public function test_it_stores_the_explicit_qty_and_alert_when_provided(): void
    {
        $asset = Asset::factory()->create();
        $assignedUser = User::factory()->create();
        $alertUser = User::factory()->create();

        $acceptance = CreateCheckoutAcceptanceAction::run($asset, $assignedUser, qty: 3, alertOnResponseId: $alertUser->id);

        $this->assertEquals(3, $acceptance->qty);
        $this->assertEquals($alertUser->id, $acceptance->alert_on_response_id);
    }
}
