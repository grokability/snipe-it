<?php

namespace App\Traits;

trait DisablesDebugbar
{
    public function disableDebugbar()
    {
        if (class_exists(\Fruitcake\LaravelDebugbar\Facades\Debugbar::class)) {
            \Fruitcake\LaravelDebugbar\Facades\Debugbar::disable();
        }
    }
}
