<?php

namespace Tests\Feature\Suppliers\Ui;

use App\Models\User;
use Tests\TestCase;

class IndexSuppliersTest extends TestCase
{
    public function testPageRenders()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('suppliers.index'))
            ->assertOk();
    }
}
