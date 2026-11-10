<?php

namespace App\Mcp\Prompts;

use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Prompt;
use Laravel\Mcp\Server\Prompts\Argument;

#[Name('find_available_asset')]
#[Title('Find Available Asset')]
#[Description('Find an undeployed asset by category or model and optionally check it out to a user')]
class FindAvailableAssetPrompt extends Prompt
{
    public function handle(Request $request): Response
    {
        $category = $request->get('category');
        $model = $request->get('model');
        $assignTo = $request->get('assign_to');

        $assetDescription = collect([
            $category ? "category: {$category}" : null,
            $model ? "model: {$model}" : null,
        ])->filter()->implode(' / ');

        $assignLine = $assignTo
            ? "If a suitable asset is found, check it out to the user: {$assignTo}."
            : 'Ask whether the found asset should be checked out to a specific user before proceeding.';

        $prompt = <<<TEXT
        You need to find an available (undeployed) asset matching {$assetDescription}.

        Please complete the following steps using the available tools:

        1. Search for assets with a Ready-to-Deploy status that match the requested {$assetDescription}.
        2. If multiple options are available, list them with their asset tags, serial numbers, and any relevant details so the best one can be selected.
        3. {$assignLine}
        4. Confirm the final asset tag, serial number, and checkout status once complete.

        If no available assets match, report what was found and suggest alternatives (different models in the same category, or assets currently out for repair that may return soon).
        TEXT;

        return Response::text(trim($prompt));
    }

    public function arguments(): array
    {
        return [
            new Argument('category', 'Asset category to search (e.g. Laptop, Monitor)', required: false),
            new Argument('model', 'Specific model name to search for', required: false),
            new Argument('assign_to', 'Username to check the asset out to once found', required: false),
        ];
    }
}
