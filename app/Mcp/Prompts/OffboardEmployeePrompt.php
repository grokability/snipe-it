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

        Offboarding is a destructive, multi-step operation. The offboard_user tool executes the whole flow atomically so you do NOT need to invoke checkin / update tools one by one. Follow this sequence:

        1. Call the offboard_user tool with username="{$username}" and dry_run=true. This returns a preview of every asset, accessory, license seat, managed user, and managed location that would change, plus confirmation that the user account will be soft-deleted.
        2. If the tool returns ambiguous_user instead of a preview (more than one user matches a provided name), present the match list to the admin and ask them which user to proceed with. Call the tool again with that user's exact username.
        3. Present the preview to the admin in plain language. Walk them through what will be checked in, what manager references will be cleared, and that the account will be soft-deleted (removed from active user lists while preserving history). Ask them to explicitly confirm before proceeding.
        4. If the admin confirms, call the offboard_user tool again with the same username and dry_run=false. The tool wraps every change in a single DB transaction, so partial failures roll back cleanly.
        5. Relay the completion summary to the admin. If the tool reports any failures, surface them exactly as returned so the admin can follow up manually.

        Do not attempt to orchestrate the individual checkins yourself even if the tool fails, the atomic tool is the single supported entry point and a manual half-finished run would be harder to recover from.
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
