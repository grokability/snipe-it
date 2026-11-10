<?php

namespace App\Mcp\Prompts;

use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Prompts\Argument;

#[Name('offboard_employee')]
#[Title('Offboard Employee')]
#[Description('Guide through checking in all equipment and licenses from a departing employee and deactivating their account')]
class OffboardEmployeePrompt extends SnipePrompt
{
    public function handle(Request $request): Response
    {
        $username = $request->get('username');

        $prompt = <<<TEXT
        You are helping offboard a departing employee with username: {$username}.

        Please complete the following offboarding steps using the available tools:

        1. Look up the user account for {$username} and display a summary of everything currently assigned to them (assets, licenses, accessories, consumables).
        2. Check in all assigned assets from this user.
        3. Check in all assigned accessories from this user.
        4. Revoke or check in any license seats assigned to this user.
        5. Deactivate the user account.
        6. Provide a final summary of all items that were checked in and confirm the account has been deactivated.

        If any items cannot be checked in automatically, flag them for manual follow-up.
        TEXT;

        return Response::text(trim($prompt).$this->localeInstruction());
    }

    public function arguments(): array
    {
        return [
            new Argument('username', 'Username of the departing employee', required: true),
        ];
    }
}
