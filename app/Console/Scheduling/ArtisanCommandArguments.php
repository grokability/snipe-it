<?php

namespace App\Console\Scheduling;

use Symfony\Component\Console\Input\StringInput;

use function Illuminate\Support\artisan_binary;
use function Illuminate\Support\php_binary;

/** Convert console input to argv data, without interpreting shell syntax. */
class ArtisanCommandArguments
{
    public static function compile(string $command, array $parameters): array
    {
        $arguments = array_merge([php_binary(), artisan_binary()], (new StringInput($command))->getRawTokens());

        foreach ($parameters as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $item) {
                    if (is_string($key) && str_starts_with($key, '--')) {
                        $arguments[] = $key.'='.(string) $item;
                    } elseif (is_string($key) && str_starts_with($key, '-')) {
                        $arguments[] = $key;
                        $arguments[] = (string) $item;
                    } else {
                        $arguments[] = (string) $item;
                    }
                }
            } else {
                $arguments[] = is_numeric($key) ? (string) $value : $key.'='.(string) $value;
            }
        }

        return $arguments;
    }
}
