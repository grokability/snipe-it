<?php

namespace App\Mcp\Prompts;

use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Prompts\Argument;

#[Name('bulk_asset_replacement')]
#[Title('Bulk Asset Replacement')]
#[Description('Find every current holder of a given asset model and propose a per-user replacement plan using available inventory')]
class BulkAssetReplacementPrompt extends SnipePrompt
{
    public function handle(Request $request): Response
    {
        $oldModel = $request->get('old_model');
        $newModel = $request->get('new_model');

        $newModelLine = $newModel
            ? "Replacement target model: {$newModel}"
            : 'Replacement target: not specified, use the most similar available model.';

        $prompt = <<<TEXT
        You are helping plan a bulk hardware replacement.

        Model being replaced: {$oldModel}
        {$newModelLine}

        This prompt builds a plan. Do not perform any check-ins, checkouts, or status changes yourself. The admin will act on the plan after reviewing it.

        Steps:

        1. Look up the old model {$oldModel} and confirm it exists. Show its category and manufacturer so the admin can confirm you resolved it correctly.
        2. List every asset of that model that is currently deployed (checked out). For each, show: asset tag, serial, current assignee, location, warranty end date, EOL date, age.
        3. List every asset of that model that is currently in stock (Ready to Deploy or similar). These could be redeployed as replacements before new inventory is needed.
        4. If a replacement target model was specified, verify it exists and show how many Ready to Deploy units are available in that model. If no replacement was specified, suggest the top 2-3 most similar available models based on category, manufacturer, and specs.
        5. Compare the number of holders needing replacement against the number of available replacement units. Report the gap (how many new units need to be purchased).
        6. For each current holder, propose a specific replacement unit (by asset tag or "to be purchased"). Prioritize replacing assets closest to EOL or out of warranty first.
        7. Return a structured plan with three sections: per-user replacement proposals, inventory gap (how many more need to be ordered), and old-asset disposition suggestion (retire, redeploy as spare, etc).

        Do not execute the plan. Flag any ambiguity (unclear replacement match, assignee at a closed location, etc) for the admin to decide.
        TEXT;

        return Response::text(trim($prompt).$this->localeInstruction());
    }

    public function arguments(): array
    {
        return [
            new Argument('old_model', 'Name or ID of the asset model being replaced', required: true),
            new Argument('new_model', 'Name or ID of the replacement model, if a specific one is targeted', required: false),
        ];
    }
}
