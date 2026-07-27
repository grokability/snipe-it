<?php

namespace Tests\Feature\Fmcs;

use App\Models\Accessory;
use App\Models\Asset;
use App\Models\Component;
use App\Models\Consumable;
use App\Models\Department;
use App\Models\License;
use App\Models\Location;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Regression coverage for GitHub issue #19192, part 2 of 4.
 *
 * Sanity check that every Companyable model the reporter listed carries
 * the fmcs_company validator on its $rules['company_id'] entry. The rule
 * itself is exercised in FmcsCompanyValidatorTest. This file just guards
 * against a future refactor accidentally dropping the rule off one of
 * the model rule arrays and quietly re-opening the #19192 hole.
 */
class FmcsCompanyRuleWiringTest extends TestCase
{
    #[DataProvider('companyableModelProvider')]
    public function test_model_rules_include_fmcs_company_for_company_id(string $modelClass)
    {
        $rules = $modelClass::rules();
        $this->assertArrayHasKey('company_id', $rules, $modelClass.' should declare a company_id rule');

        $companyRule = $rules['company_id'];
        $ruleString = is_array($companyRule) ? implode('|', $companyRule) : $companyRule;

        $this->assertStringContainsString(
            'fmcs_company',
            $ruleString,
            $modelClass.'::rules()[company_id] must include the fmcs_company validator so strict-FMCS mode rejects blank submissions',
        );
    }

    public static function companyableModelProvider(): array
    {
        return [
            'Asset' => [Asset::class],
            'License' => [License::class],
            'Accessory' => [Accessory::class],
            'Consumable' => [Consumable::class],
            'Component' => [Component::class],
            'Department' => [Department::class],
            'Location' => [Location::class],
        ];
    }
}
