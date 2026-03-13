<?php

namespace Tests\Feature\Suppliers\Ui;

use App\Models\Supplier;
use App\Models\User;
use Tests\TestCase;

class ShowSupplierTest extends TestCase
{
    public function testPageRenders()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('suppliers.show', Supplier::factory()->create()->id))
            ->assertOk();
    }
}
