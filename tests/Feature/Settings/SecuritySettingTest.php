<?php

namespace Tests\Feature\Settings;

use Tests\TestCase;
use App\Models\User;


class SecuritySettingTest extends TestCase
{
    public function testPermissionRequiredToViewSecuritySettings()
    {
        $this->actingAs(User::factory()->create())
            ->get(route('settings.security.index'))
            ->assertForbidden();
    }

}
