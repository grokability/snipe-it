<?php

namespace Tests\Feature\Account\Api;

use App\Models\Asset;
use App\Models\User;
use Tests\TestCase;

class AssignedAssetsTest extends TestCase
{
    public function test_lists_own_assets_without_a_selected_user(): void
    {
        $user = User::factory()->create();
        $asset = Asset::factory()->assignedToUser($user)->create();
        Asset::factory()->assignedToUser()->create();

        $this->actingAsForApi($user)
            ->getJson(route('api.account.assets'))
            ->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonPath('rows.0.id', $asset->id);
    }

    public function test_manager_can_list_assets_of_a_selected_subordinate(): void
    {
        // Regression coverage for GH #20159: since v8.8.1 the Manager View
        // Assets table listed the signed-in user's assets instead of the
        // assets of the user picked in "View Items Assigned to User".
        $this->settings->set(['manager_view_enabled' => 1]);
        $manager = User::factory()->create();
        $subordinate = User::factory()->create(['manager_id' => $manager->id]);
        Asset::factory()->assignedToUser($manager)->create();
        $asset = Asset::factory()->assignedToUser($subordinate)->create();

        $this->actingAsForApi($manager)
            ->getJson(route('api.account.assets', ['user_id' => $subordinate->id]))
            ->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonPath('rows.0.id', $asset->id);
    }

    public function test_manager_cannot_list_assets_of_an_unrelated_user(): void
    {
        $this->settings->set(['manager_view_enabled' => 1]);
        $manager = User::factory()->create();
        User::factory()->create(['manager_id' => $manager->id]);
        $otherUser = User::factory()->create();

        $this->actingAsForApi($manager)
            ->getJson(route('api.account.assets', ['user_id' => $otherUser->id]))
            ->assertOk()
            ->assertStatusMessageIs('error');
    }

    public function test_manager_cannot_list_a_subordinates_assets_when_manager_view_is_disabled(): void
    {
        $this->settings->set(['manager_view_enabled' => 0]);
        $manager = User::factory()->create();
        $subordinate = User::factory()->create(['manager_id' => $manager->id]);

        $this->actingAsForApi($manager)
            ->getJson(route('api.account.assets', ['user_id' => $subordinate->id]))
            ->assertOk()
            ->assertStatusMessageIs('error');
    }

    public function test_superuser_can_list_assets_of_a_selected_active_user(): void
    {
        $this->settings->set(['manager_view_enabled' => 1]);
        $superuser = User::factory()->superuser()->create();
        $user = User::factory()->create();
        $asset = Asset::factory()->assignedToUser($user)->create();

        $this->actingAsForApi($superuser)
            ->getJson(route('api.account.assets', ['user_id' => $user->id]))
            ->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonPath('rows.0.id', $asset->id);
    }
}
