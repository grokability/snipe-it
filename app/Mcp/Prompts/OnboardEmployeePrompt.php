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

        $fullName = trim("{$firstName} {$lastName}");

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

        Please complete the following onboarding steps using the available tools:

        1. Create a new user account using first_name "{$firstName}" and last_name "{$lastName}" along with the details provided above. Ask for any missing required fields (email address, username) before proceeding.
        2. Search for available (undeployed) assets suitable for their role — typically a laptop and any other standard equipment for their department or location.
        3. Check out the selected assets to the new user.
        4. Check whether any software license seats are available that should be assigned (e.g. productivity suites, VPN, etc.) and assign them.
        5. Summarise what was set up: the user account created, assets checked out, and licenses assigned.
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
