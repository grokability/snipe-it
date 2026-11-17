<?php

namespace App\Mcp\Prompts;

use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Prompts\Argument;

/**
 * Status-label names are not standardized across installs (Lost / Stolen /
 * Missing / Pending Investigation / etc), so the AI has to look them up
 * at runtime via list_status_labels rather than hard-coding a name.
 */
#[Name('report_lost_stolen')]
#[Title('Report Lost or Stolen Asset')]
#[Description('Document the circumstances of a lost or stolen asset, update its status, and note the last known assignee')]
class ReportLostStolenPrompt extends SnipePrompt
{
    public function handle(Request $request): Response
    {
        $assetTag = $request->get('asset_tag');
        $disposition = $request->get('disposition');
        $circumstances = $request->get('circumstances');

        $circumstancesLine = $circumstances
            ? "Circumstances provided: {$circumstances}"
            : 'Circumstances: ask the admin for a short description of what happened before proceeding.';

        $dispositionLine = $disposition
            ? "Disposition: {$disposition} (confirm this is one of the available non-deployable status labels before updating)."
            : 'Disposition: ask the admin whether the asset is Lost or Stolen, then look up the matching status label.';

        $prompt = <<<TEXT
        You are helping document a lost or stolen asset report.

        Asset: {$assetTag}
        {$dispositionLine}
        {$circumstancesLine}

        Steps:

        1. Look up the asset by {$assetTag} and display its current state: status, assigned user or location, last checkout date, serial, purchase date, purchase cost.
        2. Capture the last known holder. If the asset is checked out, record the current assignee. If not, pull the most recent checkout entry from the asset's history so there is still a name on file.
        3. Add a note to the asset recording the circumstances, the date reported, and the name of the person filing the report. Keep the wording factual.
        4. Update the asset's status to the appropriate non-deployable label (Lost or Stolen). If multiple labels match, confirm with the admin before updating.
        5. Do not check the asset in from its current assignee. The status change alone is sufficient; leaving the assignment in place preserves accountability in the history.
        6. Verify the status change took effect by re-running the asset lookup from step 1. Confirm the status now shows Lost or Stolen and the note is attached.
        7. Produce a recovery/insurance summary: asset identifiers, last known holder and location, purchase cost, warranty status, circumstances, date reported. This is the information the admin will likely need to forward to insurance, security, or law enforcement.

        If any step fails (status label missing, note write fails, asset not found), flag it clearly rather than silently continuing.
        TEXT;

        return Response::text(trim($prompt).$this->localeInstruction());
    }

    public function arguments(): array
    {
        return [
            new Argument('asset_tag', 'Asset tag of the lost or stolen asset', required: true),
            new Argument('disposition', 'Either "lost" or "stolen"', required: false),
            new Argument('circumstances', 'Short description of what happened', required: false),
        ];
    }
}
