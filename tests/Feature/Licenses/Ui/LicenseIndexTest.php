<?php

namespace Tests\Feature\Licenses\Ui;

use App\Models\User;
use Tests\TestCase;

class LicenseIndexTest extends TestCase
{
    public function test_permission_required_to_view_license_list()
    {
        $this->actingAs(User::factory()->create())
            ->get(route('licenses.index'))
            ->assertForbidden();
    }

    public function test_user_can_list_licenses()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('licenses.index'))
            ->assertOk();
    }
}
