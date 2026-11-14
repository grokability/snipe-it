<?php

namespace App\Mcp\Prompts;

use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;

#[Name('low_stock_alert')]
#[Title('Low Stock Alert')]
#[Description('Enumerate accessories and consumables whose current quantity is at or below the configured minimum, and suggest reorder quantities')]
class LowStockAlertPrompt extends SnipePrompt
{
    public function handle(Request $request): Response
    {
        $prompt = <<<'TEXT'
        You are helping identify accessories and consumables that are running low.

        Steps:

        1. List every accessory where the current remaining quantity is less than or equal to the configured min_amt. For each, show: name, model number, category, supplier, current qty, remaining qty, min_amt.
        2. List every consumable where the current remaining quantity is less than or equal to the configured min_amt. Same column set as accessories.
        3. Group both lists by supplier so the admin can see what to reorder from each vendor in a single pass.
        4. For each low-stock line, suggest a reorder quantity. Use the formula "target = 2 x min_amt" as a starting point, rounded up to a sensible pack size if the item has a known pack size in its notes. Flag anything that is already at zero as urgent.
        5. Produce a short single-line summary: "N accessories and N consumables below threshold across N suppliers. Urgent (zero stock): N."
        6. Return the full grouped list under the summary.

        This is a read-only report. Do not create or update any records. The admin will act on the reorder suggestions.
        TEXT;

        return Response::text(trim($prompt).$this->localeInstruction());
    }

    public function arguments(): array
    {
        return [];
    }
}
