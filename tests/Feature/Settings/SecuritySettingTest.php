<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use Tests\TestCase;

class SecuritySettingTest extends TestCase
{
    public function test_permission_required_to_view_security_settings()
    {
        $this->actingAs(User::factory()->create())
            ->get(route('settings.security.index'))
            ->assertForbidden();
    }
}
