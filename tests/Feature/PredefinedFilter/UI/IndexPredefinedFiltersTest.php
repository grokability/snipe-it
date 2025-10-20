<?php

namespace Tests\Feature\PredefinedFilter\Ui;

use App\Models\User;
use Tests\TestCase;

class IndexPredefinedFiltersTest extends TestCase
{
    public function testPageRenders()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('predefined-filters.index'))
            ->assertOk();
    }

    public function test_predefined_filters_page_returns_403_for_unauthorized_user()
    {
        $user = User::factory()->create(); // No permissions

        $this->actingAs($user)
            ->get(route('predefined-filters.index'))
            ->assertForbidden();
    }
}
