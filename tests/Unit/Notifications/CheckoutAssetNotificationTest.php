<?php

namespace Tests\Unit\Notifications;

use App\Models\Asset;
use App\Models\Company;
use App\Models\Location;
use App\Models\User;
use App\Notifications\CheckoutAssetNotification;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CheckoutAssetNotificationTest extends TestCase
{
    private function makeNotification(): CheckoutAssetNotification
    {
        $asset = Asset::factory()->create([
            'location_id' => Location::factory()->create()->id,
            'company_id' => Company::factory()->create()->id,
            'expected_checkin' => '2030-01-01',
            'last_checkout' => '2025-01-01 00:00:00',
        ]);
        $target = User::factory()->create();
        $admin = User::factory()->create();

        return new CheckoutAssetNotification($asset, $target, $admin, null, 'a note');
    }

    public static function webhookProvider(): array
    {
        return [
            'slack'     => ['slack', 1],
            'general'   => ['general', 1],
            'google'    => ['google', 1],
            'microsoft' => ['microsoft', 1],
            'none'      => ['', 0],
        ];
    }

    #[DataProvider('webhookProvider')]
    public function test_via_returns_channels_by_webhook_setting(string $selected, int $expectedCount): void
    {
        $this->settings->set([
            'webhook_selected' => $selected,
            'webhook_endpoint' => 'https://example.com/hook',
        ]);

        $channels = $this->makeNotification()->via();

        $this->assertCount($expectedCount, $channels);
    }

    public function test_to_slack_builds_message(): void
    {
        $this->assertNotNull($this->makeNotification()->toSlack());
    }

    public function test_to_microsoft_teams_builds_message(): void
    {
        $this->settings->set(['webhook_endpoint' => 'https://example.webhook.office.com/hook']);

        $this->assertNotNull($this->makeNotification()->toMicrosoftTeams());
    }

    public function test_to_google_chat_builds_message(): void
    {
        $this->settings->set(['webhook_endpoint' => 'https://chat.googleapis.com/v1/spaces/AAA/messages']);

        $this->assertNotNull($this->makeNotification()->toGoogleChat());
    }
}
