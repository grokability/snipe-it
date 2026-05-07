<?php

namespace Tests\Feature\Assets\Ui;

use App\Models\User;
use Tests\TestCase;

class AssetIndexTest extends TestCase
{
    public function test_page_renders()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('hardware.index'))
            ->assertOk();
    }

    public function test_page_renders_with_array_query_inputs()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('hardware.index', [
                'status_type' => ['Deleted'],
                'order_number' => [123],
                'company_id' => [1],
                'status_id' => [1],
            ]))
            ->assertOk();
    }
}
