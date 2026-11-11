<?php

namespace App\Mcp\Prompts;

use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Prompts\Argument;

/**
 * Thin preview-then-confirm wrapper around the transfer_user_assets tool.
 * Consumables are intentionally not transferred because consumable
 * checkouts are one-way (they do not return to stock), so the summary
 * should call that out when the source user had consumables.
 */
#[Name('transfer_employee_assets')]
#[Title('Transfer Employee Assets')]
#[Description('Transfer all assets, accessories, and license seats from one employee to another, typically when someone changes teams or roles')]
class TransferEmployeeAssetsPrompt extends SnipePrompt
{
    public function handle(Request $request): Response
    {
        $from = $request->get('from_username');
        $to = $request->get('to_username');

        $prompt = <<<TEXT
        You are helping transfer all assigned items from one employee to another.

        Source user (currently has the items): {$from}
        Target user (will receive the items): {$to}

        Transfer is a destructive, multi-step operation. The transfer_user_assets tool executes the whole flow atomically so you do NOT need to invoke checkin / checkout tools one by one. Follow this sequence:

        1. Call the transfer_user_assets tool with from_username="{$from}", to_username="{$to}", and dry_run=true. This returns a preview of every asset, accessory, and license seat that would move.
        2. If the tool returns ambiguous_user instead of a preview, present the match list to the admin and ask them which user to proceed with. Call the tool again with that user's exact username.
        3. Present the preview to the admin in plain language: what will move, from whom, to whom. Ask them to explicitly confirm before proceeding.
        4. If the admin confirms, call the transfer_user_assets tool again with the same from and to and dry_run=false. The tool wraps every change in a single DB transaction, so partial failures roll back cleanly.
        5. Relay the completion summary to the admin. If the tool reports any failures, surface them exactly as returned so the admin can follow up manually.

        Do not attempt to orchestrate the individual check-ins and check-outs yourself even if the tool fails. The atomic tool is the single supported entry point and a manual half-finished run would be harder to recover from.
        TEXT;

        return Response::text(trim($prompt).$this->localeInstruction());
    }

    public function arguments(): array
    {
        return [
            new Argument('from_username', 'Username of the employee currently holding the items', required: true),
            new Argument('to_username', 'Username of the employee who should receive the items', required: true),
        ];
    }
}
