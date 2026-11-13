<?php

namespace App\Mcp\Traits;

use Illuminate\Database\Eloquent\Model;
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
 *   - the sentinel ['_fk_ambiguous' => true, ...] or
 *     ['_fk_not_found' => true, ...] array for the caller to decode
 *     into an MCP error response
 *
 * Lookups run through the model's normal global scopes (including
 * CompanyableScope), so a non-superuser in one company gets a
 * not-found error for a name that only exists in a company they
 * cannot see. This matches the UI's "that record does not exist"
 * posture and prevents the name resolution from leaking other
 * tenants' data to non-superusers.
 *
 * Callers use fkErrorMessage() to convert those to a
 * localized error string.
 */
trait ResolvesForeignKeyByName
{
    /**
     * @param  class-string<Model>  $modelClass
     * @return int|array<string, mixed>|null
     */
    protected function resolveFkByName(
        Request $request,
        string $idField,
        string $nameField,
        string $modelClass,
        string $nameColumn = 'name',
    ): int|array|null {
        if ($request->filled($idField)) {
            return (int) $request->get($idField);
        }

        if (!$request->filled($nameField)) {
            return null;
        }

        $name = $request->get($nameField);

        $matches = $modelClass::query()
            ->whereRaw('LOWER(' . $nameColumn . ') = ?', [mb_strtolower($name)])
            ->limit(2)
            ->get(['id', $nameColumn]);

        if ($matches->isEmpty()) {
            return ['_fk_not_found' => true, 'field' => $nameField, 'value' => $name];
        }

        if ($matches->count() > 1) {
            return [
                '_fk_ambiguous' => true,
                'field' => $nameField,
                'value' => $name,
                'id_field' => $idField,
            ];
        }

        return (int) $matches->first()->getKey();
    }

    /**
     * Return true if the resolver value is one of the sentinel error
     * arrays (not found / ambiguous).
     *
     * @param  int|array<string, mixed>|null  $resolved
     */
    protected function fkResolutionFailed(int|array|null $resolved): bool
    {
        return is_array($resolved);
    }

    /**
     * Build the MCP error message for a failed resolution sentinel.
     *
     * @param  array<string, mixed>  $sentinel
     */
    protected function fkErrorMessage(array $sentinel): string
    {
        if (!empty($sentinel['_fk_ambiguous'])) {
            return trans('mcp.fk_ambiguous', [
                'field' => $sentinel['field'],
                'value' => $sentinel['value'],
                'id_field' => $sentinel['id_field'],
            ]);
        }

        return trans('mcp.fk_not_found', [
            'field' => $sentinel['field'],
            'value' => $sentinel['value'],
        ]);
    }
}
