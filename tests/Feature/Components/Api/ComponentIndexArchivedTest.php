<?php

namespace Tests\Feature\Components\Api;

use App\Models\Component;
use App\Models\User;
use Tests\TestCase;

class ComponentIndexArchivedTest extends TestCase
{
    public function test_default_list_hides_archived_components_when_setting_off(): void
    {
        $this->settings->set(['show_archived_in_list' => 0]);

        $live = Component::factory()->create();
        $archived = Component::factory()->archived()->create();

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(route('api.components.index'))
            ->assertOk()
            ->assertResponseContainsInRows($live)
            ->assertResponseDoesNotContainInRows($archived);
    }

    public function test_default_list_shows_archived_when_setting_on(): void
    {
        $this->settings->set(['show_archived_in_list' => 1]);

        $live = Component::factory()->create();
        $archived = Component::factory()->archived()->create();

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(route('api.components.index'))
            ->assertOk()
            ->assertResponseContainsInRows($live)
            ->assertResponseContainsInRows($archived);
    }

    public function test_status_archived_returns_only_archived_components(): void
    {
        $live = Component::factory()->create();
        $archived = Component::factory()->archived()->create();

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(route('api.components.index', ['status' => 'Archived']))
            ->assertOk()
            ->assertResponseContainsInRows($archived)
            ->assertResponseDoesNotContainInRows($live);
    }

    public function test_status_deleted_returns_only_soft_deleted_components(): void
    {
        $live = Component::factory()->create();
        $deleted = Component::factory()->create();
        $deleted->delete();

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(route('api.components.index', ['status' => 'Deleted']))
            ->assertOk()
            ->assertResponseContainsInRows($deleted)
            ->assertResponseDoesNotContainInRows($live);
    }

    public function test_transformer_exposes_archived_flag(): void
    {
        $archived = Component::factory()->archived()->create();

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(route('api.components.index', ['status' => 'Archived']))
            ->assertOk()
            ->assertJsonFragment(['id' => $archived->id, 'archived' => true]);
    }
}
