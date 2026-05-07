<?php

namespace App\Actions\Permissions;

final class NormalizePermissionsPayloadAction
{
    /**
     * Normalize permissions payloads from request/model to a consistent associative array.
     *
     * @return array<string, mixed>
     */
    public static function run(mixed $permissions): array
    {
        if (is_string($permissions)) {
            $decoded = json_decode($permissions, true);

            return is_array($decoded) ? $decoded : [];
        }

        if (is_array($permissions)) {
            return $permissions;
        }

        if ($permissions instanceof \stdClass) {
            return (array) $permissions;
        }

        return [];
    }
}
