<?php

namespace Tests\Feature\Users\Ui;

use App\Models\CheckoutAcceptance;
use App\Models\User;
use Tests\TestCase;

class RestoreUserTest extends TestCase
{
    public function test_permission_needed_to_restore_user()
    {
        $trashedUser = User::factory()->trashed()->create();

        $this->actingAs(User::factory()->create())
            ->post(route('users.restore.store', ['userId' => $trashedUser->id]))
            ->assertForbidden();
    }

    public function test_cannot_restore_non_deleted_user()
    {
        $nonTrashedUser = User::factory()->create();

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('users.restore.store', ['userId' => $nonTrashedUser->id]))
            ->assertSessionHas('error', trans('general.not_deleted', ['item_type' => trans('general.user')]));
    }

    public function test_can_restore_user()
    {
        $user = User::factory()->trashed()->create();

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('users.restore.store', ['userId' => $user->id]));

        $this->assertFalse($user->fresh()->trashed());
    }

    public function test_restoring_user_does_not_restore_pending_checkout_acceptances()
    {
        $checkoutAcceptance = CheckoutAcceptance::factory()->pending()->create();

        $user = $checkoutAcceptance->assignedTo;

        $user->delete();

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('users.restore.store', ['userId' => $user->id]));

        $this->assertFalse($user->fresh()->trashed());

        $this->assertTrue($checkoutAcceptance->fresh()->trashed());
    }
}
