<?php

namespace Tests\Feature\Requests\Ui;

use App\Models\Asset;
use App\Models\CheckoutRequest;
use App\Models\User;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class CancelRequestTest extends TestCase
{
    public function test_user_can_cancel_their_own_pending_request(): void
    {
        $asset = Asset::factory()->create();
        $user = User::factory()->create();
        CheckoutRequest::factory()->create(['requestable_id' => $asset->id, 'requestable_type' => Asset::class, 'user_id' => $user->id]);

        $this->actingAs($user)
            ->post(route('account/request-item', ['itemType' => 'asset', 'itemId' => $asset->id]))
            ->assertRedirect();

        $this->assertNotNull(
            CheckoutRequest::where('requestable_id', $asset->id)
                ->where('user_id', $user->id)
                ->whereNotNull('canceled_at')
                ->first()
        );
    }

    public function test_non_admin_cannot_use_cancel_by_admin_to_cancel_another_users_request(): void
    {
        $asset = Asset::factory()->create();
        $victim = User::factory()->create();
        CheckoutRequest::factory()->create(['requestable_id' => $asset->id, 'requestable_type' => Asset::class, 'user_id' => $victim->id]);

        $this->actingAs(User::factory()->create())
            ->post(route('account/request-item', [
                'itemType' => 'asset',
                'itemId' => $asset->id,
                'cancel_by_admin' => '1',
                'requestingUser' => $victim->id,
            ]))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertNull(
            CheckoutRequest::where('requestable_id', $asset->id)
                ->where('user_id', $victim->id)
                ->whereNotNull('canceled_at')
                ->first()
        );
    }

    public function test_user_with_own_pending_request_cannot_cancel_another_users_request_via_requestinguser_param(): void
    {
        $asset = Asset::factory()->create();
        $actor = User::factory()->create();
        $victim = User::factory()->create();

        CheckoutRequest::factory()->create(['requestable_id' => $asset->id, 'requestable_type' => Asset::class, 'user_id' => $actor->id]);
        CheckoutRequest::factory()->create(['requestable_id' => $asset->id, 'requestable_type' => Asset::class, 'user_id' => $victim->id]);

        $this->actingAs($actor)
            ->post(route('account/request-item', [
                'itemType' => 'asset',
                'itemId' => $asset->id,
                'cancel_by_admin' => '0',
                'requestingUser' => $victim->id,
            ]))
            ->assertRedirect();

        // Actor's own request is cancelled
        $this->assertNotNull(
            CheckoutRequest::where('requestable_id', $asset->id)
                ->where('user_id', $actor->id)
                ->whereNotNull('canceled_at')
                ->first()
        );

        // Victim's request is untouched
        $this->assertNull(
            CheckoutRequest::where('requestable_id', $asset->id)
                ->where('user_id', $victim->id)
                ->whereNotNull('canceled_at')
                ->first()
        );
    }

    public function test_superuser_can_cancel_another_users_request_via_cancel_by_admin(): void
    {
        $asset = Asset::factory()->create();
        $victim = User::factory()->create();
        CheckoutRequest::factory()->create(['requestable_id' => $asset->id, 'requestable_type' => Asset::class, 'user_id' => $victim->id]);

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('account/request-item', [
                'itemType' => 'asset',
                'itemId' => $asset->id,
                'cancel_by_admin' => '1',
                'requestingUser' => $victim->id,
            ]))
            ->assertRedirect();

        $this->assertNotNull(
            CheckoutRequest::where('requestable_id', $asset->id)
                ->where('user_id', $victim->id)
                ->whereNotNull('canceled_at')
                ->first()
        );
    }

    public function test_cancel_succeeds_and_logs_warning_when_notification_throws(): void
    {
        $asset = Asset::factory()->create();
        $user = User::factory()->create();
        CheckoutRequest::factory()->create(['requestable_id' => $asset->id, 'requestable_type' => Asset::class, 'user_id' => $user->id]);

        $this->settings->enableAlertEmail();
        config(['app.lock_passwords' => false]);

        $this->mock(ChannelManager::class, function ($mock) {
            $mock->shouldReceive('send')->andThrow(new \Exception('Mail server down'));
        });

        Log::spy();

        $this->actingAs($user)
            ->post(route('account/request-item', ['itemType' => 'asset', 'itemId' => $asset->id]))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertNotNull(
            CheckoutRequest::where('requestable_id', $asset->id)
                ->where('user_id', $user->id)
                ->whereNotNull('canceled_at')
                ->first()
        );

        Log::shouldHaveReceived('warning')->once();
    }

    public function test_new_request_succeeds_and_logs_warning_when_notification_throws(): void
    {
        $asset = Asset::factory()->create();
        $user = User::factory()->create();

        $this->settings->enableAlertEmail();
        config(['app.lock_passwords' => false]);

        $this->mock(ChannelManager::class, function ($mock) {
            $mock->shouldReceive('send')->andThrow(new \Exception('Mail server down'));
        });

        Log::spy();

        $this->actingAs($user)
            ->post(route('account/request-item', ['itemType' => 'asset', 'itemId' => $asset->id]))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertNotNull(
            CheckoutRequest::where('requestable_id', $asset->id)
                ->where('user_id', $user->id)
                ->whereNull('canceled_at')
                ->first()
        );

        Log::shouldHaveReceived('warning')->once();
    }

    public function test_admin_can_cancel_another_users_request_via_cancel_by_admin(): void
    {
        $asset = Asset::factory()->create();
        $victim = User::factory()->create();
        CheckoutRequest::factory()->create(['requestable_id' => $asset->id, 'requestable_type' => Asset::class, 'user_id' => $victim->id]);

        $this->actingAs(User::factory()->admin()->create())
            ->post(route('account/request-item', [
                'itemType' => 'asset',
                'itemId' => $asset->id,
                'cancel_by_admin' => '1',
                'requestingUser' => $victim->id,
            ]))
            ->assertRedirect();

        $this->assertNotNull(
            CheckoutRequest::where('requestable_id', $asset->id)
                ->where('user_id', $victim->id)
                ->whereNotNull('canceled_at')
                ->first()
        );
    }
}
