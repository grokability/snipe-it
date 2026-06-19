<?php

namespace Tests\Unit\Notifications;

use App\Models\Asset;
use App\Models\User;
use App\Notifications\RequestAssetCancelation;
use Tests\TestCase;

/**
 * Cubre RequestAssetCancelation (antes 0%): construccion, canales via(),
 * y las representaciones toMail()/toSlack().
 */
class RequestAssetCancelationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->superuser()->create());
    }

    private function params(): array
    {
        return [
            'target' => User::factory()->create(),
            'item' => Asset::factory()->create([
                'last_checkout' => now()->subDays(3),
                'expected_checkin' => now()->addDays(3),
            ]),
            'item_quantity' => 1,
            'requested_date' => now()->subDay(),
            'note' => 'cancelacion de prueba',
        ];
    }

    public function test_via_includes_mail_by_default(): void
    {
        $this->settings->set(['webhook_endpoint' => '']);
        $notification = new RequestAssetCancelation($this->params());

        $this->assertContains('mail', $notification->via());
        $this->assertNotContains('slack', $notification->via());
    }

    public function test_via_includes_slack_when_webhook_set(): void
    {
        $this->settings->set(['webhook_endpoint' => 'https://hooks.slack.com/services/xxx']);
        $notification = new RequestAssetCancelation($this->params());

        $this->assertContains('slack', $notification->via());
    }

    public function test_to_mail_builds_message(): void
    {
        $notification = new RequestAssetCancelation($this->params());

        $mail = $notification->toMail();

        $this->assertInstanceOf(\Illuminate\Notifications\Messages\MailMessage::class, $mail);
    }

    public function test_to_slack_builds_message(): void
    {
        $this->settings->set([
            'webhook_endpoint' => 'https://hooks.slack.com/services/xxx',
            'webhook_channel' => '#general',
            'webhook_botname' => 'Snipe-Bot',
        ]);
        $notification = new RequestAssetCancelation($this->params());

        $slack = $notification->toSlack();

        $this->assertInstanceOf(\Illuminate\Notifications\Messages\SlackMessage::class, $slack);
    }
}
