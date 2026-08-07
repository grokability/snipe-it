<?php

namespace Tests\Support;

use App\Models\Company;
use App\Models\User;
use Illuminate\Testing\PendingCommand;

/**
 * Scripts the interactive filter prompts of snipeit:send-reacceptance-requests in
 * command order, one method per filter, so a test reads like the actual prompt
 * sequence. Each step records the matching expectsQuestion/expectsConfirmation/
 * expectsSearch call on the wrapped command.
 *
 * "Skip" semantics: when a CLI flag supplies a filter, the command does not prompt
 * for it — so the caller simply does not chain that step.
 *
 * Call apply() to get the PendingCommand back for the trailing execution confirms
 * (dry-run, regenerate, send).
 */
class ReacceptancePromptScript
{
    private const TYPES_LABEL = 'Which item types would you like to regenerate acceptances for?';

    private const CATEGORIES_GATE_LABEL = 'Limit to specific categories?';

    private const CATEGORIES_SEARCH_LABEL = 'Search for categories to include.';

    private const COMPANY_GATE_LABEL = 'Filter to a specific company?';

    private const COMPANY_SEARCH_LABEL = 'Search for a company by name.';

    private const USER_GATE_LABEL = 'Limit to a single user?';

    private const USER_SEARCH_LABEL = 'Search for a user by username, first or last name.';

    private const ACCEPTED_BEFORE_GATE_LABEL = 'Only include items accepted before a cutoff date?';

    private const ACCEPTED_BEFORE_TEXT_LABEL = 'Accepted-before cutoff date (Y-m-d):';

    private const BREAKDOWN_LABEL = 'Show a breakdown of the affected users and items?';

    public function __construct(private PendingCommand $command) {}

    /**
     * Select every covered type (an empty multiselect selection means "all four").
     */
    public function allTypes(): self
    {
        return $this->selectTypes([]);
    }

    /**
     * @param  string[]  $tokens  the type tokens to select, e.g. ['asset', 'license']
     */
    public function selectTypes(array $tokens): self
    {
        $this->command->expectsQuestion(self::TYPES_LABEL, $tokens);

        return $this;
    }

    public function declineCategories(): self
    {
        $this->command->expectsConfirmation(self::CATEGORIES_GATE_LABEL, 'no');

        return $this;
    }

    /**
     * Open the categories gate and select the given categories. The multisearch
     * falls back to a search-term ask (answered empty) followed by the selection.
     *
     * @param  int[]  $categoryIds
     */
    public function chooseCategories(array $categoryIds): self
    {
        $this->command->expectsConfirmation(self::CATEGORIES_GATE_LABEL, 'yes');
        $this->command->expectsQuestion(self::CATEGORIES_SEARCH_LABEL, '');
        $this->command->expectsQuestion(self::CATEGORIES_SEARCH_LABEL, $categoryIds);

        return $this;
    }

    public function declineCompany(): self
    {
        $this->command->expectsConfirmation(self::COMPANY_GATE_LABEL, 'no');

        return $this;
    }

    public function chooseCompany(Company $company): self
    {
        $this->command->expectsConfirmation(self::COMPANY_GATE_LABEL, 'yes');
        $this->command->expectsSearch(
            self::COMPANY_SEARCH_LABEL,
            $company->id,
            $company->name,
            [$company->id => "{$company->name} (ID: {$company->id})"],
        );

        return $this;
    }

    public function declineUser(): self
    {
        $this->command->expectsConfirmation(self::USER_GATE_LABEL, 'no');

        return $this;
    }

    public function chooseUser(User $user): self
    {
        $this->command->expectsConfirmation(self::USER_GATE_LABEL, 'yes');
        $this->command->expectsSearch(
            self::USER_SEARCH_LABEL,
            $user->id,
            $user->username,
            [$user->id => "{$user->first_name} {$user->last_name} ({$user->username})"],
        );

        return $this;
    }

    public function declineAcceptedBefore(): self
    {
        $this->command->expectsConfirmation(self::ACCEPTED_BEFORE_GATE_LABEL, 'no');

        return $this;
    }

    public function acceptedBefore(string $date): self
    {
        $this->command->expectsConfirmation(self::ACCEPTED_BEFORE_GATE_LABEL, 'yes');
        $this->command->expectsQuestion(self::ACCEPTED_BEFORE_TEXT_LABEL, $date);

        return $this;
    }

    public function declineBreakdown(): self
    {
        $this->command->expectsConfirmation(self::BREAKDOWN_LABEL, 'no');

        return $this;
    }

    public function showBreakdown(): self
    {
        $this->command->expectsConfirmation(self::BREAKDOWN_LABEL, 'yes');

        return $this;
    }

    /**
     * Return the wrapped command so the caller can chain the execution confirms.
     */
    public function apply(): PendingCommand
    {
        return $this->command;
    }
}
