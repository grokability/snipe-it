<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Importer;
use App\Models\User;
use Livewire\Livewire;
use Tests\TestCase;

class ImporterTest extends TestCase
{
    public function test_renders_successfully()
    {
        Livewire::actingAs(User::factory()->canImport()->create())
            ->test(Importer::class)
            ->assertStatus(200);
    }

    public function test_requires_permission()
    {
        Livewire::actingAs(User::factory()->create())
            ->test(Importer::class)
            ->assertStatus(403);
    }
}
