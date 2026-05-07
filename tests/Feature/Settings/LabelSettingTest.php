<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use Tests\TestCase;

class LabelSettingTest extends TestCase
{
    public function test_permission_required_to_view_label_settings()
    {
        $this->actingAs(User::factory()->create())
            ->get(route('settings.labels.index'))
            ->assertForbidden();
    }
}
