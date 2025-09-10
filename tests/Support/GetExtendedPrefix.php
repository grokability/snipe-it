<?php

namespace Tests\Support;

trait GetExtendedPrefix
{
    public static function getExtendedPrefix(string $stringA, string $stringB): string
    {
        $minLength = max(1, intdiv(strlen($stringA), 2));
        $common = '';

        $length = min(strlen($stringA), strlen($stringB));
        for ($i = 0; $i < $length; $i++) {
            if ($stringA[$i] === $stringB[$i]) {
                $common .= $stringA[$i];
            } else {
                break;
            }
        }

        // Add the next char from stringA if available
        if (strlen($common) < strlen($stringA)) {
            $common .= $stringA[strlen($common)];
        }

        // Ensure at least half of stringA is returned
        if (strlen($common) < $minLength) {
            $common = substr($stringA, 0, $minLength);
        }

        return $common;
    }

}
