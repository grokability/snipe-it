<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Importer;
use App\Models\User;
use Livewire\Livewire;
use Tests\TestCase;

class ImporterTest extends TestCase
{
    public function testRendersSuccessfully()
    {
        Livewire::actingAs(User::factory()->canImport()->create())
            ->test(Importer::class)
            ->assertStatus(200);
    }

    public function testRequiresPermission()
    {
        Livewire::actingAs(User::factory()->create())
            ->test(Importer::class)
            ->assertStatus(403);
    }
}
