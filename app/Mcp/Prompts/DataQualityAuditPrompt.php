<?php

namespace App\Mcp\Prompts;

use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;

/**
 * Several checks here (duplicate-serial detection, duplicate-email
 * detection, license-seats-held-by-soft-deleted-users) have no
 * server-side filter today and require the AI to paginate and group
 * client-side. Tractable on small-to-medium installs. Prohibitive at
 * 10k+ assets or users. A dedicated server-side data_quality_check tool
 * would be the real fix if the audit becomes a frequent workflow.
 */
#[Name('data_quality_audit')]
#[Title('Data Quality Audit')]
#[Description('Scan the Snipe-IT database for common data quality problems across assets, users, and licenses, and produce a prioritized findings report')]
class DataQualityAuditPrompt extends SnipePrompt
{
    public function handle(Request $request): Response
    {
        $prompt = <<<'TEXT'
        You are helping run a data quality audit across the Snipe-IT inventory. This is a read-only report. Do not modify any records.

        Run the following checks using the available tools and report findings for each:

        1. Assets with missing or empty serial numbers.
        2. Assets with duplicate serial numbers (same serial on more than one row).
        3. Assets with missing purchase dates.
        4. Assets with missing manufacturer or category.
        5. Asset models without an EOL date configured.
        6. Assets whose status label is Ready to Deploy but who have a non-null assigned_to (checked out status mismatch).
        7. Assets whose status label indicates deployed but who have no assigned user, location, or asset (deployed with nothing to point at).
        8. Assets assigned to users who have been soft-deleted (deleted_at IS NOT NULL). Do not key this on the `activated` flag, which many orgs never set.
        9. Users with duplicate email addresses (same email on more than one user).
        10. Users without an assigned department or location.
        11. License seats assigned to users who have been soft-deleted (same guidance as check 8, use deleted_at not activated).
        12. Licenses with zero available seats (fully consumed, may need renewal or expansion).
        13. Users with multiple active checked-out license seats for the same license (double-provisioning).

        For each finding:
        - List the number of affected rows.
        - Show a sample of up to 10 affected records with enough detail to identify them.
        - Suggest a remediation step (which tool would fix it).

        Group findings by severity: critical (data integrity or compliance), high (likely wrong), medium (worth reviewing), low (cosmetic). Do not take any corrective action automatically. The admin will decide what to fix and in what order.

        Finish with a single-line summary count: "N critical, N high, N medium, N low findings across N check categories."
        TEXT;

        return Response::text(trim($prompt).$this->localeInstruction());
    }

    public function arguments(): array
    {
        return [];
    }
}
