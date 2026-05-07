<?php

namespace Tests\Feature\Suppliers\Ui;

use App\Models\User;
use Tests\TestCase;

class IndexSuppliersTest extends TestCase
{
    public function test_page_renders()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('suppliers.index'))
            ->assertOk();
    }
}
