<?php

namespace App\Mcp\Prompts;

use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Prompt;
use Laravel\Mcp\Server\Prompts\Argument;

#[Name('user_inventory')]
#[Title('User Inventory')]
#[Description('List everything currently assigned to a specific user across all asset types')]
class UserInventoryPrompt extends Prompt
{
    public function handle(Request $request): Response
    {
        $username = $request->get('username');

        $prompt = <<<TEXT
        You are pulling a complete inventory of everything assigned to the user: {$username}.

        Please complete the following steps using the available tools:

        1. Look up the user account for {$username} and display their basic info (name, department, location, job title).
        2. List all assets currently checked out to this user (asset tag, name, model, serial, status).
        3. List all accessories checked out to this user.
        4. List all license seats assigned to this user.
        5. List any consumables that have been checked out to this user.
        6. Calculate the total purchase value of all assigned assets if cost data is available.
        7. Present a clean summary grouped by item type, suitable for sharing with a manager or for an audit.
        TEXT;

        return Response::text(trim($prompt));
    }

    public function arguments(): array
    {
        return [
            new Argument('username', 'Username of the user to review', required: true),
        ];
    }
}
