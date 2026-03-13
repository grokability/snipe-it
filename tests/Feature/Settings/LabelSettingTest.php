<?php

namespace Tests\Feature\Settings;

use Tests\TestCase;
use App\Models\User;


class LabelSettingTest extends TestCase
{
    public function testPermissionRequiredToViewLabelSettings()
    {
        $this->actingAs(User::factory()->create())
            ->get(route('settings.labels.index'))
            ->assertForbidden();
    }

}
