<?php

namespace Tests\Feature\Depreciations\Api;

use App\Models\User;
use Tests\TestCase;

class DepreciationsIndexTest extends TestCase
{
    public function test_viewing_depreciation_index_requires_permission()
    {
        $this->actingAsForApi(User::factory()->create())
            ->getJson(route('api.depreciations.index'))
            ->assertForbidden();
    }
}
