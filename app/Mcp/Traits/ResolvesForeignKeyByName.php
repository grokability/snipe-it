<?php

namespace App\Mcp\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;
use Laravel\Mcp\Request;

/**
 * Lets MCP tool schemas accept either `thing_id` (number) or
 * `thing_name` (string) for every foreign-key field, so an AI caller
 * doesn't have to resolve IDs via a separate list_* round trip before
 * every create / update / list call.
 *
 * resolveFkByName() returns:
 *   - the int id when `*_id` is present
 *   - the int id resolved from `*_name` via a case-insensitive exact
 *     match on the model's name column when only `*_name` is present
 *   - null when neither key is present
 *
 * Missing or ambiguous names throw a ValidationException, which Laravel
 * MCP converts into a localized tool error response.
 *
 * Lookups run through the model's normal global scopes (including
 * CompanyableScope), so a non-superuser in one company gets a
 * not-found error for a name that only exists in a company they
 * cannot see. This matches the UI's "that record does not exist"
 * posture and prevents the name resolution from leaking other
 * tenants' data to non-superusers.
 */
trait ResolvesForeignKeyByName
{
    /**
     * @return array<string, int>
     *
     * @throws ValidationException
     */
    protected function resolveForeignKeys(Request $request): array
    {
        $resolvedFks = [];

        foreach ($this->foreignKeys as [$idField, $nameField, $modelClass]) {
            $resolved = $this->resolveFkByName($request, $idField, $nameField, $modelClass);

            if ($resolved !== null) {
                $resolvedFks[$idField] = $resolved;
            }
        }

        return $resolvedFks;
    }

    /**
     * @param  class-string<Model>  $modelClass
     *
     * @throws ValidationException
     */
    protected function resolveFkByName(
        Request $request,
        string $idField,
        string $nameField,
        string $modelClass,
        string $nameColumn = 'name',
    ): ?int {
        if ($request->filled($idField)) {
            return (int) $request->get($idField);
        }

        if (! $request->filled($nameField)) {
            return null;
        }

        $name = $request->get($nameField);

        $matches = $modelClass::query()
            ->whereRaw('LOWER('.$nameColumn.') = ?', [mb_strtolower($name)])
            ->limit(2)
            ->get(['id', $nameColumn]);

        if ($matches->isEmpty()) {
            throw ValidationException::withMessages([
                $nameField => trans('mcp.fk_not_found', [
                    'field' => $nameField,
                    'value' => $name,
                ]),
            ]);
        }

        if ($matches->count() > 1) {
            throw ValidationException::withMessages([
                $nameField => trans('mcp.fk_ambiguous', [
                    'field' => $nameField,
                    'value' => $name,
                    'id_field' => $idField,
                ]),
            ]);
        }

        return (int) $matches->first()->getKey();
    }
}
