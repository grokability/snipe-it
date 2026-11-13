<?php

namespace App\Mcp\Prompts;

use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Prompts\Argument;

#[Name('bulk_cleanup')]
#[Title('Bulk Cleanup')]
#[Description('Safely delete multiple records matching a filter. Walks through preview, admin confirmation, and committed deletion inside a single DB transaction.')]
class BulkCleanupPrompt extends SnipePrompt
{
    public function handle(Request $request): Response
    {
        $entityType = $request->get('entity_type');
        $filterDescription = $request->get('filter_description');
        $reason = $request->get('reason');

        $toolName = 'delete_'.$entityType;
        $reasonText = $reason ? "The admin's stated reason is: \"{$reason}\". Pass this exact string as the `reason` argument on the commit call so every action log entry carries it." : 'No reason was provided. You may ask the admin for one before committing, or pass through without a reason argument if they say to proceed anyway.';

        $prompt = <<<TEXT
        You are helping a Snipe-IT administrator safely delete multiple :entity_type: records in bulk.

        Target entity type: {$entityType}
        Delete tool to use: {$toolName}
        Filter description from admin: "{$filterDescription}"

        {$reasonText}

        Bulk deletion is irreversible except through restore. Follow this sequence exactly. Do NOT skip steps, do NOT attempt to improvise your own batching.

        Step 1. Translate the admin's filter description into the correct {$toolName} filter payload. Prefer structured filter / foreign-key filters (e.g. `category_name`, `status_type`, `created_before`) over free-text `search` when the intent is clear. If the description is ambiguous, paraphrase what you understood and ask the admin to confirm before continuing.

        Step 2. Call {$toolName} with your filter and `dry_run=true`. The response includes:
          - `total_matched`: how many rows the filter catches
          - `will_delete`: how many of those will actually delete on commit
          - `undeletable_count`: how many match but fail isDeletable (checked out, has children, etc)
          - `sample_rows`: first 10 matching rows with id and display name
          - `undeletable_matches`: first 10 undeletable rows with the reason each is blocked

        Step 3. Present the preview to the admin in a clear, scannable summary. Call out:
          - The exact total count
          - Any undeletable rows and why they are blocked
          - Sample rows that look like potential false positives (patterns that suggest the filter is too broad or matches unexpected records)

        Step 4. Wait for the admin to explicitly confirm. Do NOT proceed on "ok", "sure", "go ahead" alone. The admin must either name the count ("yes, delete all 47") or clearly acknowledge the total you reported ("yes, delete those 47 rows"). If they seem unsure or asked a clarifying question, back off and address the question before asking again.

        Step 5. On confirmation, call {$toolName} a second time with:
          - the same filter payload as step 2
          - `dry_run=false`
          - `confirm_count` set to the exact `total_matched` value from step 2
          - `skip_undeletable=true` ONLY if the admin explicitly asked to skip the blocked rows
          - `reason` set to the admin's stated reason (see above)

        If the commit call returns a `bulk_confirm_count_mismatch` error, the filter result has changed between preview and commit (concurrent writes). Re-run step 2 to get a fresh preview, surface the discrepancy to the admin, and ask whether to proceed with the new count.

        Step 6. Report the final result. Include:
          - How many rows deleted
          - Any that failed at commit time and why
          - A reminder that Snipe-IT soft-deletes have no retention cap, so restore_{$entityType} stays available on every removed row until an admin manually purges it

        Guardrails:
          - Never call {$toolName} with `dry_run=false` on the first invocation of this flow. The preview step is mandatory.
          - Never substitute a different tool (list_{$entityType} + a loop of single-row delete_{$entityType} calls, for example). The widened {$toolName} with filter + dry_run is the supported bulk path and gives the admin a single rollback boundary.
          - If the admin asks you to delete with no filter at all (which would match every row), refuse and ask for a narrowing filter. This flow is not for truncating a whole table.

        TEXT;

        return Response::text(trim($prompt).$this->localeInstruction());
    }

    public function arguments(): array
    {
        return [
            new Argument('entity_type', 'What to delete: asset, user, accessory, consumable, component, or license', required: true),
            new Argument('filter_description', 'Plain-English description of what to delete (e.g. "accessories whose name starts with TEST- that were created during the import trial", "duplicate consumable rows from the 2024-05 bad import"). Bulk delete is for mistakes and test data, not lifecycle transitions. Use archive or status changes for lifecycle.', required: true),
            new Argument('reason', 'Why this cleanup is happening. Recorded on each deleted row\'s action log.', required: false),
        ];
    }
}
