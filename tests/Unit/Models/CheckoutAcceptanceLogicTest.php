<?php

namespace Tests\Unit\Models;

use App\Models\CheckoutAcceptance;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Tests\TestCase;

class CheckoutAcceptanceLogicTest extends TestCase
{
    public function test_is_pending_true_when_not_accepted_or_declined(): void
    {
        $user = User::factory()->create();
        $acceptance = CheckoutAcceptance::factory()->pending()->create(['assigned_to_id' => $user->id]);

        $this->assertTrue($acceptance->isPending());
    }

    public function test_is_not_pending_when_accepted(): void
    {
        $acceptance = CheckoutAcceptance::factory()->accepted()->create();

        $this->assertFalse($acceptance->isPending());
    }

    public function test_is_checked_out_to_user(): void
    {
        $user = User::factory()->create();
        $acceptance = CheckoutAcceptance::factory()->pending()->create(['assigned_to_id' => $user->id]);

        $this->assertTrue($acceptance->isCheckedOutTo($user));
        $this->assertFalse($acceptance->isCheckedOutTo(User::factory()->create()));
    }

    public function test_checkoutable_item_type_attribute(): void
    {
        $acceptance = CheckoutAcceptance::factory()->pending()->create();

        $this->assertIsString($acceptance->checkoutable_item_type);
    }

    public function test_scope_for_user_pending_and_declined(): void
    {
        $user = User::factory()->create();
        CheckoutAcceptance::factory()->pending()->create(['assigned_to_id' => $user->id]);

        $this->assertInstanceOf(Collection::class, CheckoutAcceptance::forUser($user)->get());
        $this->assertInstanceOf(Collection::class, CheckoutAcceptance::pending()->get());
        $this->assertInstanceOf(Collection::class, CheckoutAcceptance::declined()->get());
    }
}
