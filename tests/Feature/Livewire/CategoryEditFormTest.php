<?php

namespace Tests\Feature\Livewire;

use App\Livewire\CategoryEditForm;
use Livewire\Livewire;
use Tests\TestCase;

class CategoryEditFormTest extends TestCase
{
    public function test_the_component_can_render()
    {
        Livewire::test(CategoryEditForm::class, [
            'sendCheckInEmail' => true,
            'useDefaultEula' => true,
        ])->assertStatus(200);
    }

    public function test_eula_field_enabled_on_load_when_not_using_default_eula()
    {
        Livewire::test(CategoryEditForm::class, [
            'sendCheckInEmail' => false,
            'eulaText' => '',
            'useDefaultEula' => false,
        ])->assertSet('eulaTextDisabled', false);
    }
}
