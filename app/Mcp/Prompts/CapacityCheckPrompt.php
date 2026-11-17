<?php

namespace App\Mcp\Prompts;

use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Prompts\Argument;

#[Name('capacity_check')]
#[Title('Capacity Check')]
#[Description('Check whether current Ready-to-Deploy inventory can cover a projected demand (e.g. equipping N new hires in a category)')]
class CapacityCheckPrompt extends SnipePrompt
{
    public function handle(Request $request): Response
    {
        $count = $request->get('count');
        $category = $request->get('category');
        $model = $request->get('model');

        $scopeLines = collect([
            $category ? "Category: {$category}" : null,
            $model ? "Model: {$model}" : null,
        ])->filter()->implode("\n");

        if ($scopeLines === '') {
            $scopeLines = 'No specific category or model specified. Ask the admin what kind of asset they need (e.g. "laptop", "monitor") before proceeding.';
        }

        $prompt = <<<TEXT
        You are helping check whether current inventory can cover a projected demand.

        Projected demand: {$count} units
        {$scopeLines}

        Steps:

        1. Resolve the requested category and model if provided. Confirm both exist. If only a category is given, list the models in that category that have Ready to Deploy stock.
        2. Count Ready to Deploy assets in the requested scope. Also count assets with other deployable statuses (if the install uses more than Ready to Deploy for available-but-not-RTD).
        3. Compare the available count to the projected demand ({$count}). Report the gap: how many more units are needed, or how many surplus units exist if the inventory exceeds demand.
        4. If there is a gap, identify the top suppliers for the matching models based on history (ListSuppliers plus asset counts per supplier). Suggest reorder candidates.
        5. If there is a surplus, note the excess and suggest whether any of that stock could be reallocated (e.g. old models approaching EOL could be reserved for short-term use before new stock arrives).
        6. Produce a short assessment: can we meet the demand with current stock, how many more we need to order, and a recommended action.

        Do not place any orders or update any records. This is a planning report only.
        TEXT;

        return Response::text(trim($prompt).$this->localeInstruction());
    }

    public function arguments(): array
    {
        return [
            new Argument('count', 'Number of units needed', required: true),
            new Argument('category', 'Asset category to check capacity in (e.g. "Laptop")', required: false),
            new Argument('model', 'Specific asset model to check capacity for', required: false),
        ];
    }
}
