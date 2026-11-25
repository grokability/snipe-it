<?php

namespace Tests\Feature\Account\Ui;

use App\Models\User;
use Tests\TestCase;

class AssignedAssetsTest extends TestCase
{
    public function test_manager_view_passes_the_selected_user_to_the_assets_table(): void
    {
        $this->settings->set(['manager_view_enabled' => 1]);
        $manager = User::factory()->create();
        $subordinate = User::factory()->create(['manager_id' => $manager->id]);

        $this->actingAs($manager)
            ->get(route('view-assets', ['user_id' => $subordinate->id]))
            ->assertOk()
            ->assertSee('data-url="'.route('api.account.assets', ['user_id' => $subordinate->id]).'"', false);
    }
}
