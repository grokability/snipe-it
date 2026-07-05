<?php

namespace Tests\Feature\Settings;

use App\Models\Setting;
use App\Models\User;
use Tests\TestCase;

class ApiRequestFiltersTest extends TestCase
{
    public function test_api_settings_page_requires_superuser()
    {
        $this->actingAs(User::factory()->create())
            ->get(route('settings.oauth.index'))
            ->assertForbidden();
    }

    public function test_api_request_filters_tab_renders_for_superuser()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('settings.oauth.index'))
            ->assertOk()
            ->assertSee('api-request-filters', false);
    }

    public function test_textarea_is_pre_filled_with_defaults_when_setting_is_null()
    {
        // Ensure the column starts unset.
        Setting::getSettings()->forceFill(['blocked_api_user_agents' => null])->save();

        $response = $this->actingAs(User::factory()->superuser()->create())
            ->get(route('settings.oauth.index'))
            ->assertOk();

        $response->assertSee('curl/', false);
        $response->assertSee('PostmanRuntime/', false);
    }

    public function test_post_persists_master_toggle_patterns_and_blank_flag()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('settings.oauth.request_filters.save'), [
                'block_api_user_agents' => '1',
                'blocked_api_user_agents' => "curl/\nfoo-client/",
                'block_blank_api_user_agents' => '1',
            ])
            ->assertRedirect();

        $fresh = Setting::getSettings()->fresh();
        $this->assertTrue($fresh->block_api_user_agents);
        $this->assertSame("curl/\nfoo-client/", $fresh->blocked_api_user_agents);
        $this->assertTrue($fresh->block_blank_api_user_agents);
    }

    public function test_post_without_blank_flag_treats_it_as_false()
    {
        Setting::getSettings()->forceFill(['block_blank_api_user_agents' => true])->save();

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('settings.oauth.request_filters.save'), [
                'block_api_user_agents' => '1',
                'blocked_api_user_agents' => 'curl/',
                // block_blank_api_user_agents intentionally absent — hidden=0 sentinel covers it
            ])
            ->assertRedirect();

        $this->assertFalse(Setting::getSettings()->fresh()->block_blank_api_user_agents);
    }

    public function test_post_with_empty_textarea_clears_patterns_to_null()
    {
        Setting::getSettings()->forceFill(['blocked_api_user_agents' => 'curl/'])->save();

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('settings.oauth.request_filters.save'), [
                'block_api_user_agents' => '1',
                'blocked_api_user_agents' => '',
            ])
            ->assertRedirect();

        $this->assertNull(Setting::getSettings()->fresh()->blocked_api_user_agents);
    }

    public function test_post_with_unchecked_master_persists_false_and_preserves_textarea()
    {
        // Saving with the master unchecked should turn blocking off but keep the textarea
        // contents so the admin doesn't have to re-type the list to re-enable it later.
        Setting::getSettings()->forceFill([
            'block_api_user_agents' => true,
            'blocked_api_user_agents' => "curl/\nfoo-client/",
        ])->save();

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('settings.oauth.request_filters.save'), [
                'block_api_user_agents' => '0',
                'blocked_api_user_agents' => "curl/\nfoo-client/",
            ])
            ->assertRedirect();

        $fresh = Setting::getSettings()->fresh();
        $this->assertFalse($fresh->block_api_user_agents);
        $this->assertSame("curl/\nfoo-client/", $fresh->blocked_api_user_agents);
    }

    public function test_post_is_blocked_when_app_is_in_demo_mode()
    {
        config(['app.lock_passwords' => true]);

        Setting::getSettings()->forceFill([
            'block_api_user_agents' => false,
            'blocked_api_user_agents' => null,
            'block_blank_api_user_agents' => false,
        ])->save();

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('settings.oauth.request_filters.save'), [
                'block_api_user_agents' => '1',
                'blocked_api_user_agents' => "curl/\nfoo-client/",
                'block_blank_api_user_agents' => '1',
            ])
            ->assertRedirect()
            ->assertSessionHas('error');

        // Nothing should have changed.
        $fresh = Setting::getSettings()->fresh();
        $this->assertFalse($fresh->block_api_user_agents);
        $this->assertNull($fresh->blocked_api_user_agents);
        $this->assertFalse($fresh->block_blank_api_user_agents);
    }

    public function test_post_without_master_flag_treats_it_as_false()
    {
        Setting::getSettings()->forceFill(['block_api_user_agents' => true])->save();

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('settings.oauth.request_filters.save'), [
                // block_api_user_agents intentionally absent — hidden=0 sentinel covers it
                'blocked_api_user_agents' => '',
            ])
            ->assertRedirect();

        $this->assertFalse(Setting::getSettings()->fresh()->block_api_user_agents);
    }
}
