<?php

namespace Tests\Feature\Console;

use App\Models\Consumable;
use Tests\TestCase;

/**
 * The snipeit:inventory-alerts artisan command is the daily cron consumer of
 * Helper::checkLowInventory(). It had no test coverage before this file was
 * added — meaning a refactor to the helper (like the havingRaw / SQL-side
 * filter change) could silently break the daily email without failing a
 * test. This file locks in the command's end-to-end behavior: sends when
 * items are low, skips when they aren't, no-ops when disabled.
 */
class SendInventoryAlertsTest extends TestCase
{
    // Assertions target artisan output rather than Mail::/Notification::fake,
    // because InventoryAlert routes through Notification::send() with an
    // AlertRecipient (Notifiable trait, no Eloquent parent), and the fake
    // helpers call ->getKey() on the notifiable during send-time tracking.
    // Output-level assertions still catch what matters: the command
    // completes without exception, took the correct branch, and reported
    // the right thing to cron.
    public function test_reports_low_inventory_count_when_items_are_low(): void
    {
        $this->settings->set([
            'alerts_enabled' => 1,
            'alert_email' => 'ops@example.test',
            'alert_threshold' => 0,
        ]);

        Consumable::factory()->create(['qty' => 0, 'min_amt' => 1]);

        $this->artisan('snipeit:inventory-alerts')
            ->expectsOutputToContain('below minimum inventory')
            ->assertExitCode(0);
    }

    public function test_reports_nothing_to_send_when_no_low_inventory(): void
    {
        $this->settings->set([
            'alerts_enabled' => 1,
            'alert_email' => 'ops@example.test',
            'alert_threshold' => 0,
        ]);

        Consumable::factory()->create(['qty' => 10, 'min_amt' => 1]);

        $this->artisan('snipeit:inventory-alerts')
            ->expectsOutputToContain('No low inventory items found')
            ->assertExitCode(0);
    }

    public function test_no_op_when_alerts_disabled_in_settings(): void
    {
        $this->settings->set([
            'alerts_enabled' => 0,
            'alert_email' => 'ops@example.test',
        ]);

        Consumable::factory()->create(['qty' => 0, 'min_amt' => 1]);

        $this->artisan('snipeit:inventory-alerts')
            ->expectsOutputToContain('Alerts are disabled')
            ->assertExitCode(0);
    }

    public function test_no_op_when_alert_email_is_blank(): void
    {
        $this->settings->set([
            'alerts_enabled' => 1,
            'alert_email' => '',
        ]);

        Consumable::factory()->create(['qty' => 0, 'min_amt' => 1]);

        $this->artisan('snipeit:inventory-alerts')
            ->expectsOutputToContain('No alert email configured')
            ->assertExitCode(0);
    }
}
