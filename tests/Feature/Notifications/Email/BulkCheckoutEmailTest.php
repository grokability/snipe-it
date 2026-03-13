<?php

namespace Tests\Feature\Notifications\Email;

use App\Mail\BulkAssetCheckoutMail;
use App\Mail\CheckoutAssetMail;
use App\Models\Asset;
use App\Models\Category;
use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('notifications')]
class BulkCheckoutEmailTest extends TestCase
{
    private Collection $assets;
    private Model $assignee;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();

        $this->settings->disableAdminCC();
        $this->settings->disableAdminCCAlways();

        $this->assets = Asset::factory()->requiresAcceptance()->count(2)->create();
        $this->assignee = User::factory()->create();
    }

    public function test_sent_to_user()
    {
        $this->sendRequest();

        $this->assertSingularCheckoutEmailNotSent();

        Mail::assertSent(BulkAssetCheckoutMail::class, 1);
        Mail::assertSent(BulkAssetCheckoutMail::class, function (BulkAssetCheckoutMail $mail) {
            return $mail->hasTo($this->assignee->email);
        });
    }

    public function test_sent_to_location_manager()
    {
        $manager = User::factory()->create();

        $this->assignee = Location::factory()->for($manager, 'manager')->create();

        $this->sendRequest();

        $this->assertSingularCheckoutEmailNotSent();

        Mail::assertSent(BulkAssetCheckoutMail::class, 1);
        Mail::assertSent(BulkAssetCheckoutMail::class, function (BulkAssetCheckoutMail $mail) use ($manager) {
            return $mail->hasTo($manager->email);
        });
    }

    public function test_sent_to_user_asset_is_checked_out_to()
    {
        $user = User::factory()->create();

        $this->assignee = Asset::factory()->assignedToUser($user)->create();

        $this->sendRequest();

        $this->assertSingularCheckoutEmailNotSent();

        Mail::assertSent(BulkAssetCheckoutMail::class, 1);
        Mail::assertSent(BulkAssetCheckoutMail::class, function (BulkAssetCheckoutMail $mail) use ($user) {
            return $mail->hasTo($user->email);
        });
    }

    public function test_not_sent_to_user_when_user_does_not_have_email_address()
    {
        $this->assignee = User::factory()->create(['email' => null]);

        $this->sendRequest();

        $this->assertSingularCheckoutEmailNotSent();
        Mail::assertNotSent(BulkAssetCheckoutMail::class);
    }

    public function test_not_sent_to_user_if_assets_do_not_require_acceptance()
    {
        $this->assets = Asset::factory()->doesNotRequireAcceptance()->count(2)->create();

        $category = Category::factory()
            ->doesNotRequireAcceptance()
            ->doesNotSendCheckinEmail()
            ->withNoLocalOrGlobalEula()
            ->create();

        $this->assets->each(fn($asset) => $asset->model->category()->associate($category)->save());

        $this->sendRequest();

        $this->assertSingularCheckoutEmailNotSent();
        Mail::assertNotSent(BulkAssetCheckoutMail::class);
    }

    public function test_sent_when_assets_do_not_require_acceptance_but_have_a_eula()
    {
        $this->assets = Asset::factory()->count(2)->create();

        $category = Category::factory()
            ->doesNotRequireAcceptance()
            ->doesNotSendCheckinEmail()
            ->hasLocalEula()
            ->create();

        $this->assets->each(fn($asset) => $asset->model->category()->associate($category)->save());

        $this->sendRequest();

        $this->assertSingularCheckoutEmailNotSent();

        Mail::assertSent(BulkAssetCheckoutMail::class, 1);
        Mail::assertSent(BulkAssetCheckoutMail::class, function (BulkAssetCheckoutMail $mail) {
            return $mail->hasTo($this->assignee->email);
        });
    }

    public function test_sent_when_assets_do_not_require_acceptance_or_have_a_eula_but_category_is_set_to_send_email()
    {
        $this->assets = Asset::factory()->count(2)->create();

        $category = Category::factory()
            ->doesNotRequireAcceptance()
            ->withNoLocalOrGlobalEula()
            ->sendsCheckinEmail()
            ->create();

        $this->assets->each(fn($asset) => $asset->model->category()->associate($category)->save());

        $this->sendRequest();

        $this->assertSingularCheckoutEmailNotSent();

        Mail::assertSent(BulkAssetCheckoutMail::class, 1);
        Mail::assertSent(BulkAssetCheckoutMail::class, function (BulkAssetCheckoutMail $mail) {
            return $mail->hasTo($this->assignee->email);
        });
    }

    public function test_sent_to_cc_address_when_assets_require_acceptance()
    {
        $this->assets = Asset::factory()->requiresAcceptance()->count(2)->create();

        $this->settings->enableAdminCC('cc@example.com')->disableAdminCCAlways();

        $this->sendRequest();

        $this->assertSingularCheckoutEmailNotSent();

        Mail::assertSent(BulkAssetCheckoutMail::class, 2);

        Mail::assertSent(BulkAssetCheckoutMail::class, function (BulkAssetCheckoutMail $mail) {
            return $mail->hasTo($this->assignee->email);
        });

        Mail::assertSent(BulkAssetCheckoutMail::class, function (BulkAssetCheckoutMail $mail) {
            return $mail->hasTo('cc@example.com');
        });
    }

    public function test_sent_to_cc_address_when_assets_do_not_require_acceptance_or_have_eula_but_admin_cc_always_enabled()
    {
        $this->settings->enableAdminCC('cc@example.com')->enableAdminCCAlways();

        $this->assets = Asset::factory()->doesNotRequireAcceptance()->count(2)->create();

        $category = Category::factory()
            ->doesNotRequireAcceptance()
            ->doesNotSendCheckinEmail()
            ->withNoLocalOrGlobalEula()
            ->create();

        $this->assets->each(fn($asset) => $asset->model->category()->associate($category)->save());

        $this->sendRequest();

        $this->assertSingularCheckoutEmailNotSent();

        Mail::assertSent(BulkAssetCheckoutMail::class, function (BulkAssetCheckoutMail $mail) {
            return $mail->hasTo('cc@example.com');
        });
    }

    public function test_not_sent_to_cc_address_if_assets_do_not_require_acceptance()
    {
        $this->settings->enableAdminCC('cc@example.com')->disableAdminCCAlways();

        $this->assets = Asset::factory()->doesNotRequireAcceptance()->count(2)->create();

        $category = Category::factory()
            ->doesNotRequireAcceptance()
            ->doesNotSendCheckinEmail()
            ->withNoLocalOrGlobalEula()
            ->create();

        $this->assets->each(fn($asset) => $asset->model->category()->associate($category)->save());

        $this->sendRequest();

        $this->assertSingularCheckoutEmailNotSent();
        Mail::assertNotSent(BulkAssetCheckoutMail::class);
    }

    private function sendRequest()
    {
        $assigned = match (get_class($this->assignee)) {
            User::class => [
                'checkout_to_type' => 'user',
                'assigned_user' => $this->assignee->id,
            ],
            Location::class => [
                'checkout_to_type' => 'location',
                'assigned_location' => $this->assignee->id,
            ],
            Asset::class => [
                'checkout_to_type' => 'asset',
                'assigned_asset' => $this->assignee->id,
            ],
            // we shouldn't get here...
            default => [],
        };

        $this->actingAs(User::factory()->checkoutAssets()->viewAssets()->create())
            ->followingRedirects()
            ->post(route('hardware.bulkcheckout.store'), [
                    'selected_assets' => $this->assets->pluck('id')->toArray(),
                    'checkout_at' => now()->subWeek()->format('Y-m-d'),
                    'expected_checkin' => now()->addWeek()->format('Y-m-d'),
                    'note' => null,
                ] + $assigned)
            ->assertOk();
    }

    private function assertSingularCheckoutEmailNotSent(): static
    {
        Mail::assertNotSent(CheckoutAssetMail::class);

        return $this;
    }
}
