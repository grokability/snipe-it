<?php

namespace App\Mcp\Prompts;

use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Prompts\Argument;

#[Name('maintenance_summary')]
#[Title('Maintenance Summary')]
#[Description('Summarize maintenance activity for a user, location, asset model, manufacturer, or supplier. For a single asset, call show_asset directly - the response includes maintenance aggregates.')]
class MaintenanceSummaryPrompt extends SnipePrompt
{
    public function handle(Request $request): Response
    {
        $entityType = strtolower((string) $request->get('entity_type'));
        $identifier = (string) $request->get('identifier');
        $scope = strtolower((string) $request->get('scope', 'current'));

        $prompt = <<<TEXT
        Summarize maintenance activity for the {$entityType} "{$identifier}". Scope: {$scope}.

        Current scope matches assets tied to this {$entityType} right now. Historical scope also matches assets that were tied at any prior point (walk action_logs for checkout/checkin or location changes). Historical is expensive on large installs; if scope is missing or "current" and the user implies they want history, confirm before switching.

        Supplier is a special case: match both assets.supplier_id and maintenances.supplier_id, since the FK appears on both tables.

        Steps:
        1. Resolve "{$identifier}" to a record via the matching show_/list_ tool. Ask the user to disambiguate if several match.
        2. Collect linked assets under the chosen scope.
        3. Pull maintenances for each asset via list_maintenances.
        4. Report:
           - Total count, open vs completed
           - Total cost and mean (null cost = 0)
           - Breakdown by maintenance type (resolve names via list_maintenance_types if needed)
           - Warranty split
           - Top 5 assets by count and by cost
        5. Historical scope: surface the time window and call out assets that moved in/out during it.
        TEXT;

        return Response::text(trim($prompt).$this->localeInstruction());
    }

    public function arguments(): array
    {
        return [
            new Argument('entity_type', 'One of: user, location, asset_model, manufacturer, supplier. For a single asset, call show_asset directly.', required: true),
            new Argument('identifier', 'Numeric id, username, name, or other lookup token for the entity. Ambiguity is resolved via the matching list_/show_ tool.', required: true),
            new Argument('scope', 'Either "current" (default - who/what the entity is tied to RIGHT NOW) or "historical" (walk action_logs for anyone / anything that was tied to the entity at any point).', required: false),
        ];
    }
}
