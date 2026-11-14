<?php

namespace Tests\Feature\SyncAdapters;

use App\Events\CheckoutableCheckedOut;
use App\Mail\CheckoutAssetMail;
use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\Setting;
use App\Models\Statuslabel;
use App\Models\SyncAdapterConfig;
use App\Models\SyncAdapterInstance;
use App\Models\User;
use App\Notifications\CheckoutAssetNotification;
use App\SyncAdapters\HostInventoryRecord;
use App\SyncAdapters\SyncAdapter;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Coverage for the sync-driven user assignment feature. Adapters
 * with a user_match_strategy configured look up Snipe-IT users from
 * the vendor's assigned-user field and check the asset out to the
 * match. Missing or unresolved users get skipped and logged.
 * Existing assignments are never cleared by a payload that omits the
 * user field.
 */
class UserAssignmentTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Statuslabel::factory()->rtd()->create();
    }

    public function test_email_strategy_assigns_asset_to_matched_user()
    {
        $fleet = $this->configuredFleet();
        SyncAdapterConfig::put($fleet->id, 'user_match_strategy', 'email');

        $user = User::factory()->create(['email' => 'alice@example.test']);

        SyncAdapter::syncFromRecord(new HostInventoryRecord(
            sourceKey: 'fleet',
            sourceId: 'ua-1',
            hostname: 'alices-laptop',
            hardwareSerial: 'SN-A',
            hardwareModel: 'MacBook Pro',
            assignedUserEmail: 'alice@example.test',
        ));

        $asset = Asset::where('name', 'alices-laptop')->firstOrFail();
        $this->assertSame($user->id, (int) $asset->assigned_to);
        $this->assertSame(User::class, $asset->assigned_type);
    }

    public function test_username_strategy_assigns_asset_to_matched_user()
    {
        $fleet = $this->configuredFleet();
        SyncAdapterConfig::put($fleet->id, 'user_match_strategy', 'username');

        $user = User::factory()->create(['username' => 'alice.smith']);

        SyncAdapter::syncFromRecord(new HostInventoryRecord(
            sourceKey: 'fleet',
            sourceId: 'ua-2',
            hostname: 'alices-laptop',
            hardwareSerial: 'SN-A',
            hardwareModel: 'MacBook Pro',
            assignedUserName: 'alice.smith',
        ));

        $asset = Asset::where('name', 'alices-laptop')->firstOrFail();
        $this->assertSame($user->id, (int) $asset->assigned_to);
    }

    public function test_none_strategy_leaves_asset_unassigned_even_when_user_field_populated()
    {
        $this->configuredFleet();
        // Default strategy is 'none' when nothing is stored.
        User::factory()->create(['email' => 'alice@example.test']);

        SyncAdapter::syncFromRecord(new HostInventoryRecord(
            sourceKey: 'fleet',
            sourceId: 'ua-3',
            hostname: 'no-assignment',
            hardwareSerial: 'SN-A',
            hardwareModel: 'MacBook Pro',
            assignedUserEmail: 'alice@example.test',
        ));

        $asset = Asset::where('name', 'no-assignment')->firstOrFail();
        $this->assertNull($asset->assigned_to);
    }

    public function test_unresolved_email_skips_assignment_and_logs_warning()
    {
        $fleet = $this->configuredFleet();
        SyncAdapterConfig::put($fleet->id, 'user_match_strategy', 'email');

        SyncAdapter::syncFromRecord(new HostInventoryRecord(
            sourceKey: 'fleet',
            sourceId: 'ua-4',
            hostname: 'unmatched-host',
            hardwareSerial: 'SN-A',
            hardwareModel: 'MacBook Pro',
            assignedUserEmail: 'nobody-here@example.test',
        ));

        $asset = Asset::where('name', 'unmatched-host')->firstOrFail();
        $this->assertNull($asset->assigned_to);
    }

    public function test_missing_user_field_does_not_clear_existing_assignment()
    {
        $fleet = $this->configuredFleet();
        SyncAdapterConfig::put($fleet->id, 'user_match_strategy', 'email');

        $user = User::factory()->create(['email' => 'alice@example.test']);

        // First sync: assigns to alice.
        SyncAdapter::syncFromRecord(new HostInventoryRecord(
            sourceKey: 'fleet',
            sourceId: 'ua-5',
            hostname: 'alices-laptop',
            hardwareSerial: 'SN-A',
            hardwareModel: 'MacBook Pro',
            assignedUserEmail: 'alice@example.test',
        ));

        // Second sync of the same asset with no user email.
        SyncAdapter::syncFromRecord(new HostInventoryRecord(
            sourceKey: 'fleet',
            sourceId: 'ua-5',
            hostname: 'alices-laptop',
            hardwareSerial: 'SN-A',
            hardwareModel: 'MacBook Pro',
            assignedUserEmail: null,
        ));

        $asset = Asset::where('name', 'alices-laptop')->firstOrFail();
        $this->assertSame($user->id, (int) $asset->assigned_to);
    }

    public function test_suppress_notifications_bypasses_event_dispatch()
    {
        Event::fake([CheckoutableCheckedOut::class]);

        $fleet = $this->configuredFleet();
        SyncAdapterConfig::put($fleet->id, 'user_match_strategy', 'email');
        // Default is suppress = true, verify explicitly.
        SyncAdapterConfig::put($fleet->id, 'suppress_notifications', '1');

        User::factory()->create(['email' => 'alice@example.test']);

        SyncAdapter::syncFromRecord(new HostInventoryRecord(
            sourceKey: 'fleet',
            sourceId: 'ua-6',
            hostname: 'silent-assign',
            hardwareSerial: 'SN-A',
            hardwareModel: 'MacBook Pro',
            assignedUserEmail: 'alice@example.test',
        ));

        // Direct assignment path skips the CheckoutableCheckedOut
        // event entirely, so no listener (email or otherwise) runs.
        Event::assertNotDispatched(CheckoutableCheckedOut::class);

        // But the assignment happened and a checkout actionlog was
        // written manually so the asset history stays accurate.
        $asset = Asset::where('name', 'silent-assign')->firstOrFail();
        $this->assertNotNull($asset->assigned_to);
        $this->assertDatabaseHas('action_logs', [
            'item_id' => $asset->id,
            'item_type' => Asset::class,
            'action_type' => 'checkout',
            'created_by' => null,
        ]);
    }

    public function test_suppress_off_runs_full_checkout_without_authenticated_admin()
    {
        Mail::fake();

        $fleet = $this->configuredFleet();
        SyncAdapterConfig::put($fleet->id, 'user_match_strategy', 'email');
        SyncAdapterConfig::put($fleet->id, 'suppress_notifications', '0');

        $user = User::factory()->create(['email' => 'alice@example.test']);

        // First sync reports no user, so the asset is created unassigned
        // and we can make its category require acceptance.
        $record = fn (?string $email) => new HostInventoryRecord(
            sourceKey: 'fleet',
            sourceId: 'ua-8',
            hostname: 'cli-assign',
            hardwareSerial: 'SN-A',
            hardwareModel: 'MacBook Pro',
            assignedUserEmail: $email,
        );
        SyncAdapter::syncFromRecord($record(null));
        $asset = Asset::where('name', 'cli-assign')->firstOrFail();
        $asset->model->category->update(['require_acceptance' => true]);

        // No actingAs(): simulates a scheduled/CLI sync with no auth() user.
        SyncAdapter::syncFromRecord($record('alice@example.test'));

        $this->assertSame($user->id, (int) $asset->fresh()->assigned_to);
        $this->assertDatabaseHas('checkout_acceptances', [
            'checkoutable_type' => Asset::class,
            'checkoutable_id' => $asset->id,
            'assigned_to_id' => $user->id,
            'accepted_at' => null,
            'declined_at' => null,
        ]);
        Mail::assertSent(CheckoutAssetMail::class, fn ($mail) => $mail->hasTo('alice@example.test'));
    }

    public function test_slack_notification_omits_admin_when_checkout_has_none()
    {
        $asset = Asset::factory()->assignedToUser()->create();

        $notification = new CheckoutAssetNotification($asset, $asset->assignedTo, null, null, null, Setting::getSettings());
        $fields = $notification->toSlack()->attachments[0]->fields;

        $this->assertArrayNotHasKey(trans('general.by_user'), $fields);
    }

    private function configuredFleet(): SyncAdapterInstance
    {
        $instance = SyncAdapterInstance::where('slug', 'fleet')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'url', 'https://example.com/fleet');
        SyncAdapterConfig::put($instance->id, 'token', Crypt::encrypt('fake-token'));

        return $instance->fresh();
    }
}
