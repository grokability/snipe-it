<?php

namespace Tests\Feature\Users;

use App\Models\Actionlog;
use App\Models\Company;
use App\Models\User;
use Tests\TestCase;

class UserCompanyLoggingTest extends TestCase
{
    public function test_field_and_company_changes_produce_single_log_entry()
    {
        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $user = User::factory()->create(['company_id' => $companyA->id, 'jobtitle' => 'Engineer']);
        $user->companies()->sync([$companyA->id]);

        $actor = User::factory()->superuser()->create();

        $existingLogIds = Actionlog::where('item_type', User::class)
            ->where('item_id', $user->id)
            ->pluck('id');

        $this->actingAsForApi($actor)
            ->patchJson(route('api.users.update', $user), [
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'username' => $user->username,
                'jobtitle' => 'Senior Engineer',
                'company_ids' => [$companyB->id],
            ])
            ->assertOk();

        $newLogs = Actionlog::where('item_type', User::class)
            ->where('item_id', $user->id)
            ->where('action_type', 'update')
            ->whereNotIn('id', $existingLogIds)
            ->get();

        $this->assertCount(1, $newLogs, 'Field and company changes should produce exactly one log entry');

        $meta = json_decode($newLogs->first()->log_meta, true);
        $this->assertArrayHasKey('jobtitle', $meta, 'Log should include field change');
        $this->assertArrayHasKey('companies', $meta, 'Log should include company change in same entry');
    }

    public function test_company_only_change_produces_standalone_log_entry()
    {
        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $user = User::factory()->create(['company_id' => $companyA->id]);
        $user->companies()->sync([$companyA->id]);

        $actor = User::factory()->superuser()->create();

        $existingLogIds = Actionlog::where('item_type', User::class)
            ->where('item_id', $user->id)
            ->pluck('id');

        // Patch with no field changes — only company_ids differs.
        $this->actingAsForApi($actor)
            ->patchJson(route('api.users.update', $user), [
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'username' => $user->username,
                'company_ids' => [$companyB->id],
            ])
            ->assertOk();

        $newLogs = Actionlog::where('item_type', User::class)
            ->where('item_id', $user->id)
            ->where('action_type', 'update')
            ->whereNotIn('id', $existingLogIds)
            ->get();

        $this->assertCount(1, $newLogs, 'Company-only change should produce one log entry');

        $meta = json_decode($newLogs->first()->log_meta, true);
        $this->assertArrayHasKey('companies', $meta, 'Log should record company change');
    }

    public function test_log_entry_records_old_and_new_company_ids()
    {
        [$companyA, $companyB, $companyC] = Company::factory()->count(3)->create();

        $user = User::factory()->create(['company_id' => $companyA->id]);
        $user->companies()->sync([$companyA->id, $companyB->id]);

        $actor = User::factory()->superuser()->create();

        $this->actingAsForApi($actor)
            ->patchJson(route('api.users.update', $user), [
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'username' => $user->username,
                'company_ids' => [$companyC->id],
            ])
            ->assertOk();

        $log = Actionlog::where('item_type', User::class)
            ->where('item_id', $user->id)
            ->where('action_type', 'update')
            ->latest('id')
            ->first();

        $meta = json_decode($log->log_meta, true);

        $this->assertEqualsCanonicalizing(
            [$companyA->id, $companyB->id],
            $meta['companies']['old'],
            'Log old company IDs should match previous pivot'
        );
        $this->assertEqualsCanonicalizing(
            [$companyC->id],
            $meta['companies']['new'],
            'Log new company IDs should match updated pivot'
        );
    }

    public function test_no_change_to_companies_does_not_create_extra_log_entry()
    {
        $company = Company::factory()->create();

        $user = User::factory()->create(['company_id' => $company->id]);
        $user->companies()->sync([$company->id]);

        $actor = User::factory()->superuser()->create();

        $existingLogIds = Actionlog::where('item_type', User::class)
            ->where('item_id', $user->id)
            ->pluck('id');

        // Send the same company_ids — no field changes either.
        $this->actingAsForApi($actor)
            ->patchJson(route('api.users.update', $user), [
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'username' => $user->username,
                'company_ids' => [$company->id],
            ])
            ->assertOk();

        $newLogs = Actionlog::where('item_type', User::class)
            ->where('item_id', $user->id)
            ->whereNotIn('id', $existingLogIds)
            ->count();

        $this->assertEquals(0, $newLogs, 'No changes should produce no new log entries');
    }
}
