<?php

namespace Tests\Feature\Suppliers\Ui;

use App\Models\User;
use Tests\TestCase;

class CreateSupplierTest extends TestCase
{
    public function testPageRenders()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('suppliers.create'))
            ->assertOk();
    }
}
