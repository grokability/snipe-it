<?php

namespace Tests\Feature\Companies\Ui;

use App\Models\User;
use Tests\TestCase;

class CompanyIndexTest extends TestCase
{
    public function testPageRenders()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('companies.index'))
            ->assertOk();
    }
}
