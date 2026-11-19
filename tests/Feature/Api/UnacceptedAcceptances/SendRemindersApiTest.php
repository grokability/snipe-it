<?php

use App\Models\Asset;
use App\Models\CheckoutAcceptance;
use App\Models\Company;
use App\Models\User;
use App\Notifications\UnacceptedAssetReminderNotification;
use Illuminate\Support\Facades\Notification;

/**
 * API coverage for POST /api/v1/unaccepted-acceptances/send-reminders,
 * the scheduler-friendly surface added for #19811.
 *
 * Shape:
 *   - no ids in body: delegates to the snipeit:acceptance-reminder
 *     console command (one grouped email per assignee).
 *   - ids in body: iterates each, applies FMCS, dispatches per-row.
 */
test('user without checkout grant is rejected', function () {
    $this->actingAsForApi(User::factory()->create())
        ->postJson(route('api.unaccepted-acceptances.send-reminders'))
        ->assertForbidden();
});

test('missing ids sends to every pending assignee', function () {
    Notification::fake();

    $userA = User::factory()->create(['email' => 'a@example.test']);
    $userB = User::factory()->create(['email' => 'b@example.test']);
    $userNoEmail = User::factory()->create(['email' => '']);

    CheckoutAcceptance::factory()->pending()->count(2)->create(['assigned_to_id' => $userA->id]);
    CheckoutAcceptance::factory()->pending()->create(['assigned_to_id' => $userB->id]);
    CheckoutAcceptance::factory()->pending()->create(['assigned_to_id' => $userNoEmail->id]);

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->postJson(route('api.unaccepted-acceptances.send-reminders'))
        ->assertOk()
        ->assertJsonPath('status', 'success')
        ->assertJsonPath('payload.mode', 'all')
        ->assertJsonPath('payload.sent', 2)
        ->assertJsonPath('payload.skipped.no_email', 1);

    Notification::assertSentTo($userA, UnacceptedAssetReminderNotification::class);
    Notification::assertSentTo($userB, UnacceptedAssetReminderNotification::class);
    Notification::assertNotSentTo($userNoEmail, UnacceptedAssetReminderNotification::class);
});

test('empty ids array also sends to every pending assignee', function () {
    // Explicitly-empty array arrives from well-formed clients
    // that always include the key. Should behave the same as a
    // missing one.
    Notification::fake();

    $user = User::factory()->create(['email' => 'target@example.test']);
    CheckoutAcceptance::factory()->pending()->create(['assigned_to_id' => $user->id]);

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->postJson(route('api.unaccepted-acceptances.send-reminders'), ['ids' => []])
        ->assertOk()
        ->assertJsonPath('payload.mode', 'all')
        ->assertJsonPath('payload.sent', 1);

    Notification::assertSentTo($user, UnacceptedAssetReminderNotification::class);
});

test('ids in body narrows to only the targeted assignees', function () {
    // Verifies ids-mode doesn't accidentally mail every pending
    // assignee the way the no-ids send-all path does.
    Notification::fake();

    $targeted = User::factory()->create(['email' => 'target@example.test']);
    $unrelated = User::factory()->create(['email' => 'unrelated@example.test']);

    $targetedAcceptance = CheckoutAcceptance::factory()
        ->pending()
        ->for(Asset::factory(), 'checkoutable')
        ->for($targeted, 'assignedTo')
        ->create();

    // Unrelated user has a pending acceptance that's NOT in the ids list.
    CheckoutAcceptance::factory()
        ->pending()
        ->for(Asset::factory(), 'checkoutable')
        ->for($unrelated, 'assignedTo')
        ->create();

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->postJson(route('api.unaccepted-acceptances.send-reminders'), ['ids' => [$targetedAcceptance->id]])
        ->assertOk()
        ->assertJsonPath('payload.mode', 'ids')
        ->assertJsonPath('payload.sent', 1);

    Notification::assertSentTo($targeted, UnacceptedAssetReminderNotification::class);
    Notification::assertNotSentTo($unrelated, UnacceptedAssetReminderNotification::class);
});

test('id pointing at a non pending acceptance sends nothing', function () {
    Notification::fake();

    $assignee = User::factory()->create(['email' => 'target@example.test']);
    $asset = Asset::factory()->create();
    $acceptance = CheckoutAcceptance::factory()
        ->for($asset, 'checkoutable')
        ->for($assignee, 'assignedTo')
        ->create(['accepted_at' => now()]);

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->postJson(route('api.unaccepted-acceptances.send-reminders'), ['ids' => [$acceptance->id]])
        ->assertOk()
        ->assertJsonPath('payload.sent', 0);

    Notification::assertNothingSent();
});

test('acceptance without assignee email sends nothing', function () {
    Notification::fake();

    $assignee = User::factory()->create(['email' => '']);
    $asset = Asset::factory()->create();
    $acceptance = CheckoutAcceptance::factory()
        ->pending()
        ->for($asset, 'checkoutable')
        ->for($assignee, 'assignedTo')
        ->create();

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->postJson(route('api.unaccepted-acceptances.send-reminders'), ['ids' => [$acceptance->id]])
        ->assertOk()
        ->assertJsonPath('payload.sent', 0);

    Notification::assertNothingSent();
});

test('fmcs blocks cross company ids without leaking to the mail queue', function () {
    Notification::fake();
    $this->settings->enableMultipleFullCompanySupport();

    [$companyA, $companyB] = Company::factory()->count(2)->create();

    $assignee = User::factory()->create(['email' => 'target@example.test']);
    $assetInB = Asset::factory()->create(['company_id' => $companyB->id]);
    $acceptance = CheckoutAcceptance::factory()
        ->pending()
        ->for($assetInB, 'checkoutable')
        ->for($assignee, 'assignedTo')
        ->create();

    $callerInA = User::factory()
        ->checkoutAccessories()
        ->forCompany($companyA)
        ->create();

    $this->actingAsForApi($callerInA)
        ->postJson(route('api.unaccepted-acceptances.send-reminders'), ['ids' => [$acceptance->id]])
        ->assertOk()
        ->assertJsonPath('payload.sent', 0);

    Notification::assertNothingSent();
});

test('mixed batch sends only valid rows', function () {
    Notification::fake();

    $goodAssignee = User::factory()->create(['email' => 'good@example.test']);
    $noEmailAssignee = User::factory()->create(['email' => '']);

    $goodAcceptance = CheckoutAcceptance::factory()
        ->pending()
        ->for(Asset::factory(), 'checkoutable')
        ->for($goodAssignee, 'assignedTo')
        ->create();

    $noEmailAcceptance = CheckoutAcceptance::factory()
        ->pending()
        ->for(Asset::factory(), 'checkoutable')
        ->for($noEmailAssignee, 'assignedTo')
        ->create();

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->postJson(route('api.unaccepted-acceptances.send-reminders'), [
            'ids' => [$goodAcceptance->id, $noEmailAcceptance->id, 999999],
        ])
        ->assertOk()
        ->assertJsonPath('payload.sent', 1);

    Notification::assertSentTimes(UnacceptedAssetReminderNotification::class, 1);
});

test('selecting multiple acceptances for one user dedupes to one email', function () {
    // Regression pin for the dedupe behavior. A user with three
    // pending acceptances who gets all three selected in the
    // bulk UI should receive one UnacceptedAssetReminderNotification
    // with count=3, not three separate emails.
    Notification::fake();

    $assignee = User::factory()->create(['email' => 'target@example.test']);
    $acceptances = CheckoutAcceptance::factory()->count(3)
        ->pending()
        ->for(Asset::factory(), 'checkoutable')
        ->for($assignee, 'assignedTo')
        ->create();

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->postJson(route('api.unaccepted-acceptances.send-reminders'), [
            'ids' => $acceptances->pluck('id')->all(),
        ])
        ->assertOk()
        ->assertJsonPath('payload.sent', 1);

    Notification::assertSentTimes(UnacceptedAssetReminderNotification::class, 1);
});
