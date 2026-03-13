<?php

namespace Tests\Feature\Depreciations\Api;

use App\Models\User;
use Tests\TestCase;

class DepreciationsIndexTest extends TestCase
{
    public function testViewingDepreciationIndexRequiresPermission()
    {
        $this->actingAsForApi(User::factory()->create())
            ->getJson(route('api.departments.index'))
            ->assertForbidden();
    }

}
