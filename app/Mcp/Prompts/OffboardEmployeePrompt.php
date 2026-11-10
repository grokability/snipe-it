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

        1. Look up the user account for {$username} and display a summary of everything currently assigned to them (assets, licenses, accessories, consumables), plus any users or locations that list this user as their manager.
        2. Check in all assigned assets from this user.
        3. Check in all assigned accessories from this user.
        4. Revoke or check in any license seats assigned to this user.
        5. For every user that lists {$username} as their manager, clear the manager reference (set manager_id to null). Do the same for every location that lists {$username} as its manager. Leaving these pointers in place would orphan references to a deactivated account.
        6. Deactivate the user account.
        7. Verify offboarding completed cleanly by re-running the lookups from step 1. Confirm the user has no remaining assigned assets, accessories, license seats, or consumables, and that no users or locations still reference them as manager. If anything is still assigned or still references them, report it clearly instead of claiming success.
        8. Provide a final summary of all items that were checked in, all users and locations that had their manager cleared, the verification result from step 7, and confirmation that the account has been deactivated.

        If any items cannot be checked in automatically or any manager references cannot be cleared, flag them for manual follow-up and include them explicitly in the verification report.
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
