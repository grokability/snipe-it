<?php

namespace Tests\Feature\Livewire;

use App\Livewire\PersonalAccessTokens;
use App\Models\User;
use Livewire\Livewire;
use Tests\TestCase;

class PersonalAccessTokensTest extends TestCase
{
    public function test_the_component_can_render()
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(PersonalAccessTokens::class)
            ->assertStatus(200);
    }

    public function test_create_token_validation_fails_without_name()
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(PersonalAccessTokens::class)
            ->set('name', '')
            ->call('createToken')
            ->assertHasErrors(['name' => 'required']);
    }
}
