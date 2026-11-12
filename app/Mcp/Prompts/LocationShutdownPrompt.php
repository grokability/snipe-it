<?php

namespace App\Mcp\Prompts;

use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Prompts\Argument;

#[Name('location_shutdown')]
#[Title('Location Shutdown')]
#[Description('Plan the shutdown of a physical location by inventorying every asset and user tied to it and proposing disposition for each')]
class LocationShutdownPrompt extends SnipePrompt
{
    public function handle(Request $request): Response
    {
        $location = $request->get('location');

        $prompt = <<<TEXT
        You are helping plan the shutdown of a physical location: {$location}.

        This prompt builds a plan. Do not execute any check-ins, reassignments, or deletions yourself. The admin will act on the plan after reviewing it.

        Steps:

        1. Look up the location {$location} and confirm it exists. Note its id for the subsequent queries.
        2. List every asset where either location_id or rtd_location_id points at this location. Group them by: currently assigned to a user, currently assigned to another asset, or unassigned at this location.
        3. List every user whose primary location is this location.
        4. List any other locations that reference this location as their parent, so the admin knows about downstream hierarchy fallout.
        5. For each asset currently assigned to a user, note the user and their primary location. If the user is also at this closing location, flag them for user reassignment first.
        6. Propose a disposition plan per asset group:
           - Assets with assignees moving to another location: recommend updating location to match the new site.
           - Unassigned assets at this location: recommend relocation to the nearest surviving location, or retirement if EOL.
           - Assets assigned to users at other locations already: recommend updating the asset's location to match the user.
        7. Propose a reassignment plan per user: suggest the closest surviving location based on any available clues in the data (department, manager's location, etc). Do not update records, just propose.
        8. Return the full plan as a structured report with counts per category and the per-asset / per-user lists. End with a short "next actions" section listing what the admin should do, in order.

        Do not modify any records as part of this plan. Flag anything ambiguous for the admin to decide manually.
        TEXT;

        return Response::text(trim($prompt).$this->localeInstruction());
    }

    public function arguments(): array
    {
        return [
            new Argument('location', 'Name or ID of the location being shut down', required: true),
        ];
    }
}
