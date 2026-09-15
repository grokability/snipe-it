<?php

namespace App\Actions\Acceptances;

use App\Models\User;

/**
 * One run of `RegenerateAcceptancesAction`: what it was asked to do, and what it did.
 *
 * `.ai/rules/actions.md` requires an Action to be a stateless static `run()`, so this is
 * what carries state through the five builders and back out to the caller. The options
 * are fixed at construction; everything below them is a tally the Action fills in.
 *
 * The caller decides how to present any of it. Nothing here writes to a console, which
 * is what lets an HTTP controller use the same run as the Artisan command.
 *
 * @phpstan-type ItemLine array{name: string, type: 'asset'|'license'|'accessory'|'consumable'|'component', qty: int|null}
 */
class RegenerateAcceptancesResult
{
    /**
     * The holders this run created rows for, keyed by user id, each with the items they
     * were re-requested for. Keying by holder is what keeps a holder re-requested for
     * three items to one email rather than three.
     *
     * The items are scalars, never the models: holding a checkoutable per created row
     * would pin every item this run touches in memory and undo the chunking that bounds
     * the work.
     *
     * @var array<int, array{user: User, items: array<int, ItemLine>}>
     */
    public array $holdersToNotify = [];

    /**
     * Holders who got rows but could not be emailed, as ID/name pairs.
     *
     * @var array<int, array{0: int, 1: string}>
     */
    public array $holdersWithoutEmail = [];

    /**
     * The rows this run re-requested, in the order the builders found them. Under a dry
     * run they are the rows it would have created.
     *
     * @var array<int, array{0: string, 1: string, 2: string, 3: int, 4: int}>
     */
    public array $reportRows = [];

    /**
     * How many pairs each checkoutable type contributed.
     *
     * @var array<string, int>
     */
    public array $sendCountsByType = [];

    public int $candidateCount = 0;

    public int $previouslyDeclined = 0;

    public int $alreadyCovered = 0;

    public int $declinedAndExcluded = 0;

    public int $created = 0;

    public int $notified = 0;

    /**
     * @param  array<int, int|string>  $categoryIds  Empty means every category requiring acceptance.
     * @param  array<int, int|string>  $companyIds  Empty means every company.
     */
    public function __construct(
        public readonly array $categoryIds = [],
        public readonly array $companyIds = [],
        public readonly bool $excludeDeclined = false,
        public readonly bool $dryRun = false,
        public readonly bool $notify = false,
    ) {}
}
