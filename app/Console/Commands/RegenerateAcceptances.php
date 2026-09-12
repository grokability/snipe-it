<?php

namespace App\Console\Commands;

use App\Actions\Acceptances\RegenerateAcceptancesAction;
use App\Actions\Acceptances\RegenerateAcceptancesResult;
use App\Models\Category;
use App\Models\Company;
use Illuminate\Console\Command;

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
        $this->categoryIds = $this->option('category');
        $this->companyIds = $this->option('company');
        $this->excludeDeclined = (bool) $this->option('exclude-declined');
        $this->dryRun = (bool) $this->option('dry-run');
        $this->notify = (bool) $this->option('notify');

        if ($this->input->isInteractive() && ! $this->runWizard()) {
            return 0;
        }

        return $this->printReport($this->regenerate(dryRun: $this->dryRun));
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

            return 0;
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

        return 0;
    }
}
