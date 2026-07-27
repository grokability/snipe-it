<?php

namespace Tests\Feature\Fmcs;

use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

/**
 * Regression coverage for GitHub issue #19192, part 1 of 4.
 *
 * Exercises the fmcs_company validator in isolation across the settings
 * and actor context matrix. Per Snipe's FMCS adversarial-tests memory,
 * cover strict and floater and non-FMCS with superuser and non-superuser
 * actors, plus the CLI (no-auth) posture and the pseudo-company carve-out
 * for uncompanied actors in strict mode.
 *
 * The rule is exercised on its own (not against each model's full $rules)
 * so unrelated pseudo-rules like License::limit_change don't crash the
 * runner. A companion sanity file, FmcsCompanyRuleWiringTest, asserts
 * every affected model's $rules array carries the rule.
 */
class FmcsCompanyValidatorTest extends TestCase
{
    public function test_rule_rejects_null_in_strict_fmcs_for_non_superuser()
    {
        $this->settings->enableMultipleFullCompanySupport();
        $this->settings->disableFloaterMode();
        auth()->login(User::factory()->create());

        $validator = Validator::make(['company_id' => null], ['company_id' => 'fmcs_company']);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('company_id', $validator->errors()->toArray());
    }

    public function test_rule_accepts_null_in_strict_fmcs_for_superuser()
    {
        $this->settings->enableMultipleFullCompanySupport();
        $this->settings->disableFloaterMode();
        auth()->login(User::factory()->superuser()->create());

        $validator = Validator::make(['company_id' => null], ['company_id' => 'fmcs_company']);

        $this->assertFalse($validator->fails());
    }

    public function test_rule_accepts_null_when_floater_mode_enabled()
    {
        $this->settings->enableFloaterMode();
        auth()->login(User::factory()->create());

        $validator = Validator::make(['company_id' => null], ['company_id' => 'fmcs_company']);

        $this->assertFalse($validator->fails());
    }

    public function test_rule_accepts_null_when_fmcs_off()
    {
        $this->settings->disableMultipleFullCompanySupport();
        auth()->login(User::factory()->create());

        $validator = Validator::make(['company_id' => null], ['company_id' => 'fmcs_company']);

        $this->assertFalse($validator->fails());
    }

    public function test_rule_accepts_non_null_in_strict_fmcs_for_non_superuser()
    {
        $this->settings->enableMultipleFullCompanySupport();
        $this->settings->disableFloaterMode();
        auth()->login(User::factory()->create());
        $company = Company::factory()->create();

        $validator = Validator::make(['company_id' => $company->id], ['company_id' => 'fmcs_company']);

        $this->assertFalse($validator->fails());
    }

    public function test_rule_accepts_null_when_no_auth_context()
    {
        // CLI, seeders, and importers deliberately bypass, matching the
        // SaveUserRequest cannot_make_floater gate's posture.
        $this->settings->enableMultipleFullCompanySupport();
        $this->settings->disableFloaterMode();
        auth()->logout();

        $validator = Validator::make(['company_id' => null], ['company_id' => 'fmcs_company']);

        $this->assertFalse($validator->fails());
    }

    public function test_rule_accepts_null_for_uncompanied_non_superuser_in_strict_mode()
    {
        // Regression guard for the pseudo-company workflow. Under
        // Company::scopeCompanyablesDirectly in strict mode, actors
        // with no company memberships are scoped to null-company rows
        // (whereNull($column)). Null IS a valid company id for them.
        // Forcing them to pick a non-null company would both lock them
        // out of their normal workflow AND produce a row they wouldn't
        // be able to see afterward.
        $this->settings->enableMultipleFullCompanySupport();
        $this->settings->disableFloaterMode();

        $actor = User::factory()->withoutCompany()->create();
        $this->assertFalse($actor->companies()->exists(), 'test precondition: actor is uncompanied');
        auth()->login($actor);

        $validator = Validator::make(['company_id' => null], ['company_id' => 'fmcs_company']);

        $this->assertFalse($validator->fails());
    }

    public function test_rule_still_rejects_null_for_companied_non_superuser_in_strict_mode()
    {
        // Reporter's #19192 case. A non-superuser WITH company
        // memberships submitting a null company_id would land an
        // invisible row. That must still fail.
        $this->settings->enableMultipleFullCompanySupport();
        $this->settings->disableFloaterMode();

        $company = Company::factory()->create();
        $actor = $company->users()->save(User::factory()->create());
        $this->assertTrue($actor->companies()->exists(), 'test precondition: actor has memberships');
        auth()->login($actor);

        $validator = Validator::make(['company_id' => null], ['company_id' => 'fmcs_company']);

        $this->assertTrue($validator->fails());
    }
}
