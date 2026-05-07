<?php

namespace Tests\Feature\Categories\Ui;

use App\Models\Category;
use App\Models\User;
use Tests\TestCase;

class ShowCategoryTest extends TestCase
{
    public function test_page_renders()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('categories.show', Category::factory()->create()))
            ->assertOk();
    }
}
