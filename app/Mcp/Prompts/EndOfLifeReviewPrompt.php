<?php

namespace App\Mcp\Prompts;

use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Prompts\Argument;

#[Name('end_of_life_review')]
#[Title('End of Life Review')]
#[Description('Identify assets that have passed their EOL date or are fully depreciated, and recommend disposition actions')]
class EndOfLifeReviewPrompt extends SnipePrompt
{
    public function handle(Request $request): Response
    {
        $department = $request->get('department');
        $category = $request->get('category');

        $scope = collect([
            $department ? "department: {$department}" : null,
            $category ? "category: {$category}" : null,
        ])->filter()->implode(' and ');

        $scopeLine = $scope
            ? "Limit the review to assets in {$scope}."
            : 'Review assets across the entire organisation.';

        $prompt = <<<TEXT
        You are conducting an end-of-life and depreciation review. {$scopeLine}

        Please complete the following steps using the available tools:

        1. List assets that have passed their asset_eol_date (end-of-life date is in the past).
        2. List assets that are fully depreciated based on their depreciation schedule and purchase date.
        3. For each identified asset, show: asset tag, name, model, assigned user or location, EOL date, purchase date, and current status.
        4. Group findings by category for easier review.
        5. Recommend disposition for each group: retire and replace, redeploy to a lower-demand role, send for repair, or archive.
        6. Provide a cost summary if purchase cost data is available — total value of end-of-life assets.
        TEXT;

        return Response::text(trim($prompt).$this->localeInstruction());
    }

    public function arguments(): array
    {
        return [
            new Argument('department', 'Limit review to a specific department', required: false),
            new Argument('category', 'Limit review to a specific asset category', required: false),
        ];
    }
}
