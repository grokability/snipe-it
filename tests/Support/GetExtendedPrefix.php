<?php

namespace Tests\Support;

trait GetExtendedPrefix
{
    public static function getExtendedPrefix(string $valueA, string $valueB): string
    {
        $lengthA = strlen($valueA);
        $lengthB = strlen($valueB);
        $minLength = max(1, intdiv($lengthA, 2));

        $identical = '';
        $maxIndex = min($lengthA, $lengthB);

        // Build identical prefix
        for ($i = 0; $i < $maxIndex; $i++) {
            if ($valueA[$i] === $valueB[$i]) {
                $identical .= $valueA[$i];
            } else {
                break;
            }
        }

        // Add one more char from valueA if possible
        if (strlen($identical) < $lengthA) {
            $identical .= $valueA[strlen($identical)];
        }

        // Ensure the result is at least half of valueA
        if (strlen($identical) < $minLength) {
            $identical = substr($valueA, 0, $minLength);
        }

        return $identical;
    }

}
