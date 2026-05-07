<?php

namespace Tests\Unit\BladeComponents;

use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Tests\TestCase;

class IconComponentTest extends TestCase
{
    public function test_icon_component_does_not_end_in_newline()
    {
        $renderedTemplateString = View::make('blade.icon', ['type' => 'checkout'])->render();

        $this->assertFalse(
            Str::endsWith($renderedTemplateString, PHP_EOL),
            'Newline found at end of icon component. Bootstrap tables will not render if there is a newline at the end of the file.'
        );
    }
}
