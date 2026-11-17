<?php

namespace App\Mcp\Prompts;

use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Prompts\Argument;

#[Name('onboard_employee')]
#[Title('Onboard Employee')]
#[Description('Guide through creating a new employee account and assigning appropriate equipment and licenses')]
class OnboardEmployeePrompt extends SnipePrompt
{
    public function handle(Request $request): Response
    {
        $firstName = $request->get('first_name');
        $lastName = $request->get('last_name');
        $department = $request->get('department');
        $location = $request->get('location');
        $title = $request->get('title');

        $context = collect([
            $department ? "Department: {$department}" : null,
            $location ? "Location: {$location}" : null,
            $title ? "Job title: {$title}" : null,
        ])->filter()->implode("\n");

        $prompt = <<<TEXT
        You are helping onboard a new employee.

        Employee details:
        - First name: {$firstName}
        - Last name: {$lastName}
        {$context}

        Onboarding steps:

        1. Determine a username for the new account. First call list_users with a limit of 5 and look at the existing username patterns (firstname.lastname, firstinitial+last, employee number, email-style, etc). Propose a username that matches the dominant convention in this install. Confirm it with the admin before creating the account. If they want something different, use what they say.
        2. Ask the admin for an email address if one was not supplied, and ask whether a password reset link should be sent to it after the account is created.
        3. Create the user account using the confirmed username, first_name "{$firstName}", last_name "{$lastName}", and any other details provided above. Do not ask for a password, one will be set automatically. If create_user reports a conflict on the username, propose a disambiguated variant (append an initial, a number, etc) and confirm again before retrying.
        4. If the admin said yes in step 2 and the account has an email address, call send_password_reset.
        5. Search for available (undeployed) assets suitable for their role, typically a laptop and any other standard equipment for their department or location.
        6. Check out the selected assets to the new user.
        7. Check whether any software license seats are available that should be assigned (e.g. productivity suites, VPN, etc.) and assign them.
        8. Verify onboarding completed cleanly by re-running the user lookup from step 3. Confirm the user account exists (not soft-deleted), every intended asset is now assigned to them, and every intended license seat is now assigned to them. Do not confuse the `activated` flag with employment status, it only controls login access and many orgs never set it. If anything is missing or still unassigned, report it clearly instead of claiming success.
        9. Summarise what was set up: the user account created, whether a password reset email was sent, assets checked out, licenses assigned, and the verification result from step 8.

        If any assets or license seats cannot be checked out automatically, flag them for manual follow-up and include them explicitly in the verification report.
        TEXT;

        return Response::text(trim($prompt).$this->localeInstruction());
    }

    public function arguments(): array
    {
        return [
            new Argument('first_name', 'First name of the new employee', required: true),
            new Argument('last_name', 'Last name of the new employee', required: false),
            new Argument('department', 'Department the employee will join', required: false),
            new Argument('location', 'Primary office location', required: false),
            new Argument('title', 'Job title', required: false),
        ];
    }
}
