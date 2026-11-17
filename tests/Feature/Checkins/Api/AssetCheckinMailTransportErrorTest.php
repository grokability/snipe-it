<?php

namespace Tests\Feature\Checkins\Api;

use App\Models\Asset;
use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Regression for issue #20009.
 *
 * A broken mail transport (unresolved Docker-link `MAIL_PORT`, bad host,
 * stray TypeError from a strict-typed library) must not take down the
 * parent checkin / transfer request. CheckoutableListener wraps its
 * Mail::send calls in try/catch, but previously only caught Exception,
 * which left Error subtypes (TypeError in particular) bubbling up as a
 * 500. The listener is now Throwable-catching so transport-level
 * failures log and move on while the asset is still checked in.
 */
class AssetCheckinMailTransportErrorTest extends TestCase
{
    public function test_broken_mail_transport_does_not_500_the_checkin(): void
    {
        // Category that triggers the mail path on checkin so the
        // listener actually reaches Mail::send during the request.
        // Without a mail-triggering category, the listener early-exits
        // and the TypeError never has a chance to fire.
        $category = Category::factory()->forAssets()->sendsCheckinEmail()->create();
        $assignee = User::factory()->create(['email' => 'assignee@example.test']);
        $asset = Asset::factory()
            ->for(\App\Models\AssetModel::factory()->for($category, 'category'), 'model')
            ->assignedToUser($assignee)
            ->create();

        // Simulate the transport-level TypeError that unresolved Docker
        // link vars like MAIL_PORT=${MAIL_PORT_587_TCP_PORT} produce.
        // Any Throwable raised inside the listener's Mail::send block
        // should be swallowed by the try/catch.
        Mail::shouldReceive('to')->andThrow(new \TypeError('simulated Dsn port TypeError'));

        $this->actingAsForApi(User::factory()->checkinAssets()->create())
            ->postJson(route('api.asset.checkin', $asset))
            ->assertOk()
            ->assertStatusMessageIs('success');

        $this->assertNull($asset->refresh()->assignedTo, 'Asset should be checked in despite the mail transport error.');
        $this->assertNull($asset->assigned_type);
    }
}
