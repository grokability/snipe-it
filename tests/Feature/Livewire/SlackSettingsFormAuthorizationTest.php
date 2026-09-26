<?php

namespace Tests\Feature\Livewire;

use App\Livewire\IntegrationSettingsForm;
use App\Models\User;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Regression coverage for the Livewire snapshot-replay authorization bypass
 * reported by PizzaStev3 (2026-07-31). SlackSettingsForm had no
 * per-request authorization and exposed webhook mutation methods
 * (testWebhook, clearSettings, submit) plus render()-time disclosure of
 * the configured webhook_endpoint/channel. boot() gate now blocks both
 * mount and any replay under a non-superuser session.
 */
class SlackSettingsFormAuthorizationTest extends TestCase
{
    public function test_superuser_can_mount()
    {
        $this->actingAs(User::factory()->superuser()->create());

        $initialObLevel = ob_get_level();

        Livewire::test(IntegrationSettingsForm::class)
            ->assertStatus(200);

        // The slack-settings-form view opens @section('content') at line 13
        // but never closes it in-file. In production, the parent
        // settings/slack.blade.php's trailing @stop closes the section.
        // Livewire::test() renders the component in isolation, so the
        // section's output buffer stays open and PHPUnit flags the test
        // as risky. Drain any buffers opened during render.
        while (ob_get_level() > $initialObLevel) {
            ob_end_clean();
        }
    }

    public function test_non_superuser_cannot_mount_or_replay()
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(IntegrationSettingsForm::class)
            ->assertStatus(403);
    }
}
