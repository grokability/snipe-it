<?php

namespace App\Console\Commands;

use App\Actions\Acceptances\RegenerateAcceptancesAction;
use App\Actions\Acceptances\RegenerateAcceptancesResult;
use App\Models\Category;
use App\Models\Company;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\multiselect;

class RegenerateAcceptances extends Command
{
    protected $signature = 'snipeit:regenerate-acceptances
        {--category=* : Limit to these category ids (default: all categories requiring acceptance)}
        {--company=* : Limit to these company ids (default: all companies)}
        {--dry-run : Print the report; create nothing}
        {--exclude-declined : Skip users whose latest response was a decline (default: re-ask them)}
        {--notify : Also email each affected user}';

    protected $description = 'Re-request EULA acceptance from users who currently hold items';

    /**
     * Category ids, empty when the operator did not narrow the run.
     *
     * @var array<int, int|string>
     */
    private array $categoryIds = [];

    /**
     * Company ids, empty when the operator did not narrow the run.
     *
     * @var array<int, int|string>
     */
    private array $companyIds = [];

    private bool $excludeDeclined = false;

    private bool $dryRun = false;

    private bool $notify = false;

    public function handle(): int
    {
        $this->categoryIds = $this->splitIds($this->option('category'));
        $this->companyIds = $this->splitIds($this->option('company'));
        $this->excludeDeclined = (bool) $this->option('exclude-declined');
        $this->dryRun = (bool) $this->option('dry-run');
        $this->notify = (bool) $this->option('notify');

        if ($this->refuseUnusableScope()) {
            return self::FAILURE;
        }

        if ($this->input->isInteractive() && ! $this->runWizard()) {
            return self::SUCCESS;
        }

        return $this->printReport($this->regenerate(dryRun: $this->dryRun));
    }

    /**
     * The ids behind a repeatable id option, with comma-separated values split out.
     *
     * Symfony hands `--category=1,2` back as the single string `'1,2'` rather than two
     * ids, and MySQL then coerces that string to its leading integer inside a `whereIn`
     * — so the un-split form scopes the run to category 1 alone and reports nothing
     * amiss. Splitting here makes `--category=1,2` and `--category=1 --category=2` mean
     * the same thing, and leaves a genuinely unknown id to be named on its own.
     *
     * @param  array<int, string>  $values
     * @return array<int, string>
     */
    private function splitIds(array $values): array
    {
        return collect($values)
            ->flatMap(fn (string $value) => explode(',', $value))
            ->map(fn (string $id) => trim($id))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Reports every `--category` id the run cannot use, and says whether it was refused.
     *
     * `--category` is ANDed with the requires-acceptance scope rather than added to it,
     * so an id that is unknown, or whose category does not require acceptance,
     * contributes nothing and the run reports an empty scope. That report is
     * indistinguishable from a correctly scoped run with no work to do, which is how a
     * mistyped id reads to an operator as a clean bill of health.
     *
     * The two mistakes are reported separately because they are fixed differently — one
     * is a wrong id, the other a category setting — and both are reported by the one
     * refused run, since naming only the kind found first would send the operator round
     * a second failed run to discover the other.
     *
     * @return bool whether the scope is unusable
     */
    private function reportUnusableCategories(): bool
    {
        if ($this->categoryIds === []) {
            return false;
        }

        $categories = Category::whereIn('id', $this->categoryIds)->orderBy('name')->get();

        $unknownIds = array_values(array_diff($this->categoryIds, $categories->modelKeys()));
        $withoutAcceptance = $categories->where('require_acceptance', false);

        if ($unknownIds === [] && $withoutAcceptance->isEmpty()) {
            return false;
        }

        if ($unknownIds !== []) {
            $this->error(vsprintf('No category exists with %s %s.', [
                Str::plural('id', count($unknownIds)),
                implode(', ', $unknownIds),
            ]));
        }

        if ($withoutAcceptance->isNotEmpty()) {
            $this->error('These categories do not require acceptance, so nothing in them can be re-requested:');
            $this->table(
                ['ID', 'Category'],
                $withoutAcceptance->map(fn (Category $category) => [$category->id, $category->name])->all(),
            );
            $this->line('Turn on Require Acceptance on them, or drop them from --category.');
        }

        return true;
    }

    /**
     * Reports every `--company` id that names no company, and says whether it is usable.
     *
     * A company has no acceptance setting to get wrong, so an unknown id is the only way
     * to mis-scope by company — but it fails the same silent way a bad category id does,
     * narrowing the run to nothing that reads as nothing to do.
     *
     * @return bool whether the scope is unusable
     */
    private function reportUnknownCompanies(): bool
    {
        if ($this->companyIds === []) {
            return false;
        }

        $knownIds = Company::whereIn('id', $this->companyIds)->pluck('id')->all();
        $unknownIds = array_values(array_diff($this->companyIds, $knownIds));

        if ($unknownIds === []) {
            return false;
        }

        $this->error(vsprintf('No company exists with %s %s.', [
            Str::plural('id', count($unknownIds)),
            implode(', ', $unknownIds),
        ]));

        return true;
    }

    /**
     * Refuses a run whose `--category` or `--company` ids cannot scope it, after naming
     * every problem with both.
     *
     * Both scopes are reported before the refusal, under one closing line, so an
     * operator who mistyped an id in each is told about both rather than sent round a
     * second failed run to discover the second.
     *
     * @return bool whether the run was refused
     */
    private function refuseUnusableScope(): bool
    {
        $categoriesAreUnusable = $this->reportUnusableCategories();
        $companiesAreUnusable = $this->reportUnknownCompanies();

        if (! $categoriesAreUnusable && ! $companiesAreUnusable) {
            return false;
        }

        $this->line('Nothing was run.');

        return true;
    }

    /**
     * One pass of the Action with whatever options are settled by now.
     */
    private function regenerate(bool $dryRun): RegenerateAcceptancesResult
    {
        return RegenerateAcceptancesAction::run(
            categoryIds: $this->categoryIds,
            companyIds: $this->companyIds,
            excludeDeclined: $this->excludeDeclined,
            dryRun: $dryRun,
            notify: $this->notify,
        );
    }

    /**
     * Walks the operator through the options no flag already answered, previews what
     * the run would do, and asks whether to go ahead.
     *
     * Every step is walked in order, and one whose flag was passed announces itself and
     * moves on, so the operator sees the same sequence whatever they typed. The preview
     * is a real dry run, so confirming costs a second pass over the same items — the
     * alternative, holding every send pair in memory until the operator answers, would
     * undo the chunking that bounds the Action on a large install.
     *
     * The gate on this is the caller's `isInteractive()` check rather than anything
     * Prompts does on its own: `ConfiguresPrompts` forces Prompts interactive whenever
     * the app is running unit tests, so a prompt reached under test hits a mocked
     * question helper and throws rather than taking its default. Nothing here may run
     * before that check.
     *
     * @return bool whether to go on and create the rows
     */
    private function runWizard(): bool
    {
        $this->askForCategories();
        $this->askForCompanies();
        $this->askWhetherToExcludeDeclined();
        $this->askWhetherToNotify();

        $this->printReport($this->regenerate(dryRun: true));

        if ($this->dryRun) {
            return false;
        }

        if (! confirm(label: 'Create these acceptance requests?', default: false)) {
            $this->info('Nothing was created.');

            return false;
        }

        return true;
    }

    private function askForCategories(): void
    {
        if ($this->categoryIds !== []) {
            $this->line('--category passed — skipping');

            return;
        }

        $categories = Category::requiresAcceptance()->orderBy('name')->pluck('name', 'id');

        if ($categories->isEmpty()) {
            return;
        }

        $this->categoryIds = multiselect(
            label: 'Which categories should this run cover?',
            options: $categories->all(),
            hint: 'Select none to cover every category requiring acceptance.',
        );
    }

    private function askForCompanies(): void
    {
        if ($this->companyIds !== []) {
            $this->line('--company passed — skipping');

            return;
        }

        $companies = Company::orderBy('name')->pluck('name', 'id');

        if ($companies->isEmpty()) {
            return;
        }

        $this->companyIds = multiselect(
            label: 'Which companies should this run cover?',
            options: $companies->all(),
            hint: 'Select none to cover every company.',
        );
    }

    private function askWhetherToExcludeDeclined(): void
    {
        if ($this->option('exclude-declined')) {
            $this->line('--exclude-declined passed — skipping');

            return;
        }

        $this->excludeDeclined = confirm(
            label: 'Skip holders whose latest response was a decline?',
            default: false,
            yes: 'Yes — leave decliners alone',
            no: 'No — re-ask them too',
        );
    }

    private function askWhetherToNotify(): void
    {
        if ($this->option('notify')) {
            $this->line('--notify passed — skipping');

            return;
        }

        $this->notify = confirm(label: 'Email each affected holder?', default: false);
    }

    /**
     * Prints what the run re-requested, or under a dry run what it would have.
     */
    private function printReport(RegenerateAcceptancesResult $result): int
    {
        if ($result->candidateCount === 0) {
            $this->info('No users currently hold items requiring acceptance in that scope.');

            return self::SUCCESS;
        }

        if ($result->reportRows !== []) {
            $this->table(['User', 'Item', 'Type', 'Units held', 'Qty'], $result->reportRows);
        }

        $this->info('To re-request: '.count($result->reportRows).'.');

        foreach ($result->sendCountsByType as $type => $count) {
            $this->line('  '.$type.': '.$count);
        }

        $this->info('Previously declined: '.$result->previouslyDeclined.'.');
        $this->info('Already covered by a pending request: '.$result->alreadyCovered.'.');

        if ($result->excludeDeclined) {
            $this->info('Previously declined and excluded: '.$result->declinedAndExcluded.'.');
        }

        $this->info($result->dryRun ? 'Nothing was created.' : 'Created: '.$result->created.'.');

        if ($result->notify) {
            $this->info('Notified: '.$result->notified.'.');

            if ($result->holdersWithoutEmail !== []) {
                $this->info('The following users do not have an email address:');
                $this->table(['ID', 'Name'], $result->holdersWithoutEmail);
            }
        }

        return self::SUCCESS;
    }
}
