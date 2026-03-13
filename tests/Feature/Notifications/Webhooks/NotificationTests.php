<?php
namespace Tests\Feature\Notifications\Webhooks;

use App\Models\Asset;
use App\Models\User;
use App\Notifications\AuditNotification;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;
use Tests\Support\Settings;

#[Group('notifications')]
class NotificationTests extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
    }
    public function testAuditNotificationThrowsWhenItemIsNull()
    {
        try {
            new AuditNotification([
                'item' => null,
            ]);
            $this->fail('Expected Error was not thrown');
        } catch (\Throwable $e) {
            $this->assertInstanceOf(\InvalidArgumentException::class, $e);
            $this->assertSame('Notification requires a valid item.', $e->getMessage());
        }
    }

    public function testAuditNotificationFires()
    {
        $webhook_options = [
            'enableSlackWebhook',
            'enableMicrosoftTeamsWebhook',
            'enableGoogleChatWebhook'
        ];

        Notification::fake();
        //tests every webhook option
        foreach($webhook_options as $option) {

            $this->settings->{$option}();

            $user = User::factory()->create();
            $item = Asset::factory()->create();

            try {
                $user->notify(new \App\Notifications\AuditNotification([
                    'item'  => $item,
                ]));
            } catch (\InvalidArgumentException $e) {
                $this->fail("AuditNotification threw for [{$option}]: {$e->getMessage()}");
            }
        }
            Notification::assertSentTimes(AuditNotification::class, count($webhook_options));
    }
}
