<?php

namespace App\Mcp\Prompts;

use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Prompt;
use Laravel\Mcp\Server\Prompts\Argument;

#[Name('inventory_summary')]
#[Title('Inventory Summary')]
#[Description('Produce a high-level inventory count by category, broken down by deployment status')]
class InventorySummaryPrompt extends Prompt
{
    public function handle(Request $request): Response
    {
        $location = $request->get('location');
        $department = $request->get('department');

        $scope = collect([
            $location ? "location: {$location}" : null,
            $department ? "department: {$department}" : null,
        ])->filter()->implode(' and ');

        $scopeLine = $scope
            ? "Scope the report to {$scope}."
            : 'Report across the entire organisation.';

        $prompt = <<<TEXT
        You are generating an inventory summary report. {$scopeLine}

        Please complete the following steps using the available tools:

        1. List assets (filtered by the scope above if provided) and tally counts by status: Deployed, Ready to Deploy, Archived, Pending, Out for Repair.
        2. Break the deployed count down by asset category (laptops, monitors, phones, etc.).
        3. List the top 5 models by total quantity.
        4. Show total purchase value of the inventory if cost data is available.
        5. Highlight any categories with zero available (Ready to Deploy) assets — potential stock-out risk.
        6. Present the results as a concise executive summary with a supporting breakdown table.
        TEXT;

        return Response::text(trim($prompt));
    }

    public function arguments(): array
    {
        return [
            new Argument('location', 'Limit report to a specific location', required: false),
            new Argument('department', 'Limit report to a specific department', required: false),
        ];
    }
}
