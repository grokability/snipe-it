<?php

namespace Tests\Unit\Notifications;

use App\Models\Asset;
use App\Models\User;
use App\Notifications\AuditNotification;
use Illuminate\Notifications\Channels\SlackWebhookChannel;
use NotificationChannels\GoogleChat\GoogleChatChannel;
use NotificationChannels\GoogleChat\GoogleChatMessage;
use NotificationChannels\MicrosoftTeams\MicrosoftTeamsChannel;
use Tests\TestCase;

/**
 * Cubre AuditNotification (antes 34%): canales via(), toSlack, toMicrosoftTeams
 * (ramas workflows / no-workflows) y toGoogleChat.
 */
class AuditNotificationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->superuser()->create());
    }

    private function params(): array
    {
        return [
            'item' => Asset::factory()->create(),
            'admin' => User::factory()->create(),
            'note' => 'auditoria de prueba',
            'location' => 'Bodega',
        ];
    }

    public function test_constructor_throws_without_valid_item(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new AuditNotification(['item' => null, 'admin' => User::factory()->create()]);
    }

    public function test_via_slack(): void
    {
        $this->settings->set(['webhook_selected' => 'slack']);
        $this->assertContains(SlackWebhookChannel::class, (new AuditNotification($this->params()))->via());
    }

    public function test_via_microsoft(): void
    {
        $this->settings->set([
            'webhook_selected' => 'microsoft',
            'webhook_endpoint' => 'https://outlook.office.com/webhook/xxx',
        ]);
        $this->assertContains(MicrosoftTeamsChannel::class, (new AuditNotification($this->params()))->via());
    }

    public function test_via_google(): void
    {
        $this->settings->set([
            'webhook_selected' => 'google',
            'webhook_endpoint' => 'https://chat.googleapis.com/v1/spaces/xxx',
        ]);
        $this->assertContains(GoogleChatChannel::class, (new AuditNotification($this->params()))->via());
    }

    public function test_to_slack_builds_message(): void
    {
        $this->settings->set(['webhook_selected' => 'slack', 'webhook_channel' => '#general']);
        $slack = (new AuditNotification($this->params()))->toSlack();

        $this->assertInstanceOf(\Illuminate\Notifications\Messages\SlackMessage::class, $slack);
    }

    public function test_to_microsoft_teams_non_workflows(): void
    {
        $this->settings->set(['webhook_endpoint' => 'https://outlook.office.com/webhook/abc']);

        $result = AuditNotification::toMicrosoftTeams($this->params());

        $this->assertNotNull($result);
    }

    public function test_to_microsoft_teams_workflows_returns_array(): void
    {
        $this->settings->set(['webhook_endpoint' => 'https://prod.workflows.azure.com/xyz']);

        $result = AuditNotification::toMicrosoftTeams($this->params());

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
    }

    public function test_to_microsoft_teams_returns_null_without_item(): void
    {
        $this->assertNull(AuditNotification::toMicrosoftTeams(['item' => null]));
    }

    public function test_to_google_chat_builds_message(): void
    {
        $this->settings->set(['webhook_endpoint' => 'https://chat.googleapis.com/v1/spaces/abc']);

        $message = (new AuditNotification($this->params()))->toGoogleChat();

        $this->assertInstanceOf(GoogleChatMessage::class, $message);
    }
}
