<?php

namespace Tests\Feature\Reporting;

use App\Models\Asset;
use App\Models\CheckoutAcceptance;
use App\Models\Company;
use App\Models\User;
use App\Notifications\UnacceptedAssetReminderNotification;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Auth + FMCS pins for the per-row reminder-send endpoint on the moved
 * /unaccepted-acceptances surface. Gate shifted from reports.view to
 * canCheckoutAtLeastOneItemType in v8.9 so the mutating path isn't
 * reachable by a read-only Reports grant.
 */
class SentReminderAuthorizationTest extends TestCase
{
    public function test_user_without_checkout_grant_cannot_send_reminder()
    {
        Notification::fake();

        $assignee = User::factory()->create(['email' => 'assignee@example.test']);
        $asset = Asset::factory()->create();
        $acceptance = CheckoutAcceptance::factory()
            ->pending()
            ->for($asset, 'checkoutable')
            ->for($assignee, 'assignedTo')
            ->create();

        $this->actingAs(User::factory()->create())
            ->post(route('unaccepted-acceptances.send-reminder'), ['acceptance_id' => $acceptance->id])
            ->assertForbidden();

        Notification::assertNothingSent();
    }

    public function test_user_with_reports_view_only_cannot_send_reminder()
    {
        // Regression pin for the v8.9 move. reports.view alone no
        // longer unlocks a reminder send. The actor must hold a
        // checkout grant on at least one type.
        Notification::fake();

        $assignee = User::factory()->create(['email' => 'assignee@example.test']);
        $asset = Asset::factory()->create();
        $acceptance = CheckoutAcceptance::factory()
            ->pending()
            ->for($asset, 'checkoutable')
            ->for($assignee, 'assignedTo')
            ->create();

        $this->actingAs(User::factory()->canViewReports()->create())
            ->post(route('unaccepted-acceptances.send-reminder'), ['acceptance_id' => $acceptance->id])
            ->assertForbidden();

        Notification::assertNothingSent();
    }

    public function test_checkout_user_can_send_reminder_for_their_own_company()
    {
        Notification::fake();
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA] = Company::factory()->count(2)->create();

        $assignee = User::factory()->forCompany($companyA)->create(['email' => 'assignee@example.test']);
        $asset = Asset::factory()->create(['company_id' => $companyA->id]);
        $reporter = User::factory()->checkoutAccessories()->forCompany($companyA)->create();
        $acceptance = CheckoutAcceptance::factory()
            ->pending()
            ->for($asset, 'checkoutable')
            ->for($assignee, 'assignedTo')
            ->create();

        $this->actingAs($reporter)
            ->post(route('unaccepted-acceptances.send-reminder'), ['acceptance_id' => $acceptance->id])
            ->assertRedirectToRoute('unaccepted-acceptances.index')
            ->assertSessionHas('success');

        Notification::assertSentTo($assignee, UnacceptedAssetReminderNotification::class);
    }

    public function test_checkout_user_cannot_send_reminder_for_another_company()
    {
        Notification::fake();
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $assignee = User::factory()->create(['email' => 'assignee@example.test']);
        $assetB = Asset::factory()->create(['company_id' => $companyB->id]);
        $reporter = User::factory()->checkoutAccessories()->forCompany($companyA)->create();
        $acceptance = CheckoutAcceptance::factory()
            ->pending()
            ->for($assetB, 'checkoutable')
            ->for($assignee, 'assignedTo')
            ->create();

        $this->actingAs($reporter)
            ->post(route('unaccepted-acceptances.send-reminder'), ['acceptance_id' => $acceptance->id])
            ->assertRedirectToRoute('unaccepted-acceptances.index')
            ->assertSessionHas('error');

        Notification::assertNothingSent();
    }

    public function test_pivot_only_user_cannot_send_reminder_for_another_company()
    {
        Notification::fake();
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $assignee = User::factory()->create(['email' => 'assignee@example.test']);
        $assetB = Asset::factory()->create(['company_id' => $companyB->id]);
        $reporter = User::factory()->checkoutAccessories()->withoutCompany()->create();
        $reporter->companies()->sync([$companyA->id]);

        $acceptance = CheckoutAcceptance::factory()
            ->pending()
            ->for($assetB, 'checkoutable')
            ->for($assignee, 'assignedTo')
            ->create();

        $this->actingAs($reporter)
            ->post(route('unaccepted-acceptances.send-reminder'), ['acceptance_id' => $acceptance->id])
            ->assertRedirectToRoute('unaccepted-acceptances.index')
            ->assertSessionHas('error');

        Notification::assertNothingSent();
    }

    public function test_superuser_can_send_reminder_for_any_company()
    {
        Notification::fake();
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $assignee = User::factory()->create(['email' => 'assignee@example.test']);
        $assetB = Asset::factory()->create(['company_id' => $companyB->id]);
        $superuser = User::factory()->superuser()->forCompany($companyA)->create();
        $acceptance = CheckoutAcceptance::factory()
            ->pending()
            ->for($assetB, 'checkoutable')
            ->for($assignee, 'assignedTo')
            ->create();

        $this->actingAs($superuser)
            ->post(route('unaccepted-acceptances.send-reminder'), ['acceptance_id' => $acceptance->id])
            ->assertRedirectToRoute('unaccepted-acceptances.index')
            ->assertSessionHas('success');

        Notification::assertSentTo($assignee, UnacceptedAssetReminderNotification::class);
    }
}
