<?php

namespace Tests\Feature\Categories\Ui;

use App\Models\User;
use Tests\TestCase;

class IndexCategoriesTest extends TestCase
{
    public function test_permission_required_to_view_category_list()
    {
        $this->actingAs(User::factory()->create())
            ->get(route('categories.index'))
            ->assertForbidden();
    }

    public function test_user_can_list_categories()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('categories.index'))
            ->assertOk();
    }
}
