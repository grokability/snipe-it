<?php

namespace App\Mcp\Prompts;

use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Prompts\Argument;

#[Name('vendor_summary')]
#[Title('Vendor Summary')]
#[Description('Produce a procurement-focused summary of everything acquired from a given supplier or manufacturer: counts across asset types, total spend, deployed vs available breakdown, warranty coverage, and open maintenance / service history')]
class VendorSummaryPrompt extends SnipePrompt
{
    public function handle(Request $request): Response
    {
        $vendorName = $request->get('vendor_name');
        $vendorType = $request->get('vendor_type', 'supplier');

        $prompt = <<<TEXT
        You are producing a procurement-focused summary of everything in the Snipe-IT inventory tied to a single vendor.

        Vendor name: {$vendorName}
        Vendor type: {$vendorType} (either "supplier" or "manufacturer")

        This is a read-only report. Do not create, update, or delete any records.

        Steps:

        1. Resolve the vendor. If vendor_type is "supplier", call list_suppliers with a search for {$vendorName}. If vendor_type is "manufacturer", call list_manufacturers. If multiple vendors match the name, present the match list to the admin and ask which one they meant before proceeding. Capture the numeric ID of the resolved vendor for the subsequent queries.
        2. List all assets tied to this vendor using list_assets with either supplier_id or manufacturer_id set to the resolved ID. Note the total count from the response payload. Group the returned rows by status_type (Deployed, Ready to Deploy, Archived, Pending, Undeployable) and report counts per bucket.
        3. List all licenses tied to this vendor using list_licenses with the same filter. Report total count, total seats, free seats across all licenses, and any licenses that are fully consumed or expiring within 90 days (use expiring_within_days=90 as a second call if needed).
        4. If vendor_type is "supplier", list all consumables with list_consumables filtered by supplier_id, and all accessories with list_accessories filtered by supplier_id. Report item counts, any items flagged by low_stock_only=true, and current remaining totals.
        5. If vendor_type is "supplier", list all maintenances performed by this supplier using list_maintenances with supplier_id. Report count of open vs completed, total cost spent to date (sum of cost fields on completed rows), and date of most recent maintenance.
        6. Total spend rollup: sum purchase_cost across all asset rows, license rows, consumable rows, and accessory rows returned above. Report the grand total and a per-category breakdown.
        7. Warranty rollup (assets only): for each asset tied to this vendor, compute whether warranty_expires is in the past, within 90 days, or beyond 90 days. Report counts per bucket. This tells the admin how much of this vendor's deployed fleet is still covered.
        8. Produce a structured summary with these sections, in order:
           - Vendor header: resolved vendor name, id, and type.
           - Inventory counts: assets (with status breakdown), licenses (with seat usage), consumables, accessories.
           - Financial rollup: total spend, per-category breakdown.
           - Warranty status: covered vs expiring-soon vs expired asset counts.
           - Service history: open vs completed maintenance counts, total service spend, most recent maintenance date.
           - Flags: any low-stock consumables/accessories, any fully-consumed licenses, any expiring-soon licenses, any out-of-warranty deployed assets. These are the items the admin may want to act on.

        Keep the report concise. Use counts and totals, not full row listings, unless the admin asks for drill-down detail on a specific section.
        TEXT;

        return Response::text(trim($prompt).$this->localeInstruction());
    }

    public function arguments(): array
    {
        return [
            new Argument('vendor_name', 'Name of the supplier or manufacturer to summarise', required: true),
            new Argument('vendor_type', 'Either "supplier" or "manufacturer" (default: supplier)', required: false),
        ];
    }
}
