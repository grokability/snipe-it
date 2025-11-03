<?php

namespace Tests\Feature\Livewire;

use App\Livewire\CategoryEditForm;
use Livewire\Livewire;
use Tests\TestCase;

class CategoryEditFormTest extends TestCase
{
    public function testTheComponentCanRender()
    {
        Livewire::test(CategoryEditForm::class, [
            'sendCheckInEmail' => true,
            'useDefaultEula' => true,
        ])->assertStatus(200);
    }

    public function testEulaFieldEnabledOnLoadWhenNotUsingDefaultEula()
    {
        Livewire::test(CategoryEditForm::class, [
            'sendCheckInEmail' => false,
            'eulaText' => '',
            'useDefaultEula' => false,
        ])->assertSet('eulaTextDisabled', false);
    }
}
