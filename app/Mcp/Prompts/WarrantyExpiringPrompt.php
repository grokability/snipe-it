<?php

namespace App\Mcp\Prompts;

use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Prompts\Argument;

#[Name('warranty_expiring')]
#[Title('Warranty Expiring')]
#[Description('List assets whose warranty expires within a given number of days')]
class WarrantyExpiringPrompt extends SnipePrompt
{
    public function handle(Request $request): Response
    {
        $days = (int) ($request->get('days', 90));

        $prompt = <<<TEXT
        You are reviewing assets whose warranty is expiring soon. Focus on assets expiring within {$days} days.

        Please complete the following steps using the available tools:

        1. List assets and filter for those whose warranty expiration date (calculated from purchase_date + warranty_months) falls within the next {$days} days.
        2. For each asset, show: asset tag, name, model, assigned user or location, purchase date, warranty months, and calculated warranty end date.
        3. Group by urgency: expiring within 30 days, 31–60 days, and 61–{$days} days.
        4. Flag any assets that are deployed to critical roles or users where warranty coverage is especially important.
        5. Recommend actions: extend warranty, schedule replacement, or note as acceptable risk.
        TEXT;

        return Response::text(trim($prompt).$this->localeInstruction());
    }

    public function arguments(): array
    {
        return [
            new Argument('days', 'Number of days ahead to check for warranty expiry (default: 90)', required: false),
        ];
    }
}
