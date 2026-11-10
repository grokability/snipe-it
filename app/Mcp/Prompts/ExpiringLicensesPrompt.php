<?php

namespace App\Mcp\Prompts;

use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Prompt;
use Laravel\Mcp\Server\Prompts\Argument;

#[Name('expiring_licenses')]
#[Title('Expiring Licenses')]
#[Description('Review license seat usage and flag licenses expiring within a given number of days')]
class ExpiringLicensesPrompt extends Prompt
{
    public function handle(Request $request): Response
    {
        $days = (int) ($request->get('days', 30));

        $prompt = <<<TEXT
        You are reviewing software license health across the organisation. Focus on licenses expiring within {$days} days.

        Please complete the following steps using the available tools:

        1. List all licenses in the system.
        2. Identify licenses whose expiration date falls within the next {$days} days.
        3. For each expiring license, show: license name, total seats, seats in use, seats free, and the expiration date.
        4. Flag any licenses that are over-deployed (more seats checked out than purchased).
        5. Flag any licenses that are under-used (many free seats that may indicate unused subscriptions worth cancelling).
        6. Produce a prioritised action list: renewals needed urgently, over-deployments to resolve, and possible cancellations.
        TEXT;

        return Response::text(trim($prompt));
    }

    public function arguments(): array
    {
        return [
            new Argument('days', 'Number of days ahead to check for expiring licenses (default: 30)', required: false),
        ];
    }
}
