<?php

namespace App\Actions;

class BaseAction
{
    public static function make()
    {
        return app(static::class);
    }

    public static function run(mixed ...$arguments): mixed
    {
        return static::make()->handle(...$arguments);
    }
}
