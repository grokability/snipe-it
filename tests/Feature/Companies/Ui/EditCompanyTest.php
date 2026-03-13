<?php

namespace Tests\Feature\Companies\Ui;

use App\Models\Company;
use App\Models\User;
use Tests\TestCase;

class EditCompanyTest extends TestCase
{
    public function testPageRenders()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('companies.edit', Company::factory()->create()))
            ->assertOk();
    }
}
