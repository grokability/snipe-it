<?php

namespace App\Mcp\Prompts;

use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Prompts\Argument;

#[Name('audit_location')]
#[Title('Audit Location')]
#[Description('Review all assets at a location, flag overdue audits and status anomalies')]
class AuditLocationPrompt extends SnipePrompt
{
    public function handle(Request $request): Response
    {
        $location = $request->get('location');

        $prompt = <<<TEXT
        You are conducting an asset audit for location: {$location}.

        Please complete the following steps using the available tools:

        1. Find the location record for "{$location}" (search by name if needed).
        2. List all assets currently assigned to or located at that location.
        3. Identify any assets with overdue audit dates (next_audit_date is in the past).
        4. Flag any assets with unexpected status labels (e.g. archived, pending, or out-for-repair assets that appear to still be at this location).
        5. Note any assets that have been at this location longer than expected without a check-in or audit event.
        6. Produce a summary report with: total asset count, assets requiring audit, assets with status anomalies, and any recommended actions.

        Present the findings clearly so they can be acted on or exported.
        TEXT;

        return Response::text(trim($prompt).$this->localeInstruction());
    }

    public function arguments(): array
    {
        return [
            new Argument('location', 'Name or ID of the location to audit', required: true),
        ];
    }
}
