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

    public function testPredefinedFiltersPageReturns403ForUnauthorizedUser()
    {
        $user = User::factory()->create(); // No permissions

        $this->actingAs($user)
            ->get(route('predefined-filters.index'))
            ->assertForbidden();
    }
}
