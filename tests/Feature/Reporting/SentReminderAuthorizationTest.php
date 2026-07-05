<?php

namespace Tests\Feature\Reporting;

use App\Mail\CheckoutAssetMail;
use App\Models\Asset;
use App\Models\CheckoutAcceptance;
use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SentReminderAuthorizationTest extends TestCase
{
    public function test_user_without_reports_view_cannot_send_reminder()
    {
        Mail::fake();

        $assignee = User::factory()->create(['email' => 'assignee@example.test']);
        $asset = Asset::factory()->create();
        $acceptance = CheckoutAcceptance::factory()
            ->pending()
            ->for($asset, 'checkoutable')
            ->for($assignee, 'assignedTo')
            ->create();

        $this->actingAs(User::factory()->create())
            ->post(route('reports/unaccepted_assets_sent_reminder'), ['acceptance_id' => $acceptance->id])
            ->assertForbidden();

        Mail::assertNothingSent();
    }

    public function test_reports_user_can_send_reminder_for_their_own_company()
    {
        Mail::fake();
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA] = Company::factory()->count(2)->create();

        $assignee = User::factory()->create(['email' => 'assignee@example.test', 'company_id' => $companyA->id]);
        $asset = Asset::factory()->create(['company_id' => $companyA->id]);
        $reporter = User::factory()->canViewReports()->create(['company_id' => $companyA->id]);
        $acceptance = CheckoutAcceptance::factory()
            ->pending()
            ->for($asset, 'checkoutable')
            ->for($assignee, 'assignedTo')
            ->create();

        $this->actingAs($reporter)
            ->post(route('reports/unaccepted_assets_sent_reminder'), ['acceptance_id' => $acceptance->id])
            ->assertRedirectToRoute('reports/unaccepted_assets')
            ->assertSessionHas('success');

        Mail::assertSent(CheckoutAssetMail::class);
    }

    public function test_reports_user_cannot_send_reminder_for_another_company()
    {
        Mail::fake();
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $assignee = User::factory()->create(['email' => 'assignee@example.test']);
        $assetB = Asset::factory()->create(['company_id' => $companyB->id]);
        $reporter = User::factory()->canViewReports()->create(['company_id' => $companyA->id]);
        $acceptance = CheckoutAcceptance::factory()
            ->pending()
            ->for($assetB, 'checkoutable')
            ->for($assignee, 'assignedTo')
            ->create();

        $this->actingAs($reporter)
            ->post(route('reports/unaccepted_assets_sent_reminder'), ['acceptance_id' => $acceptance->id])
            ->assertRedirectToRoute('reports/unaccepted_assets')
            ->assertSessionHas('error');

        Mail::assertNothingSent();
    }

    public function test_pivot_only_user_cannot_send_reminder_for_another_company()
    {
        Mail::fake();
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $assignee = User::factory()->create(['email' => 'assignee@example.test']);
        $assetB = Asset::factory()->create(['company_id' => $companyB->id]);
        $reporter = User::factory()->canViewReports()->create(['company_id' => null]);
        $reporter->companies()->sync([$companyA->id]);

        $acceptance = CheckoutAcceptance::factory()
            ->pending()
            ->for($assetB, 'checkoutable')
            ->for($assignee, 'assignedTo')
            ->create();

        $this->actingAs($reporter)
            ->post(route('reports/unaccepted_assets_sent_reminder'), ['acceptance_id' => $acceptance->id])
            ->assertRedirectToRoute('reports/unaccepted_assets')
            ->assertSessionHas('error');

        Mail::assertNothingSent();
    }

    public function test_superuser_can_send_reminder_for_any_company()
    {
        Mail::fake();
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $assignee = User::factory()->create(['email' => 'assignee@example.test']);
        $assetB = Asset::factory()->create(['company_id' => $companyB->id]);
        $superuser = User::factory()->superuser()->create(['company_id' => $companyA->id]);
        $acceptance = CheckoutAcceptance::factory()
            ->pending()
            ->for($assetB, 'checkoutable')
            ->for($assignee, 'assignedTo')
            ->create();

        $this->actingAs($superuser)
            ->post(route('reports/unaccepted_assets_sent_reminder'), ['acceptance_id' => $acceptance->id])
            ->assertRedirectToRoute('reports/unaccepted_assets')
            ->assertSessionHas('success');

        Mail::assertSent(CheckoutAssetMail::class);
    }
}
