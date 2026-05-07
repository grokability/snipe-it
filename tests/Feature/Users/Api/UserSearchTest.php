<?php

namespace Tests\Feature\Users\Api;

use App\Models\Company;
use App\Models\User;
use Laravel\Passport\Passport;
use Tests\TestCase;

class UserSearchTest extends TestCase
{
    public function test_can_search_by_user_first_and_last_name()
    {
        User::factory()->create(['first_name' => 'Luke', 'last_name' => 'Skywalker']);
        User::factory()->create(['first_name' => 'Darth', 'last_name' => 'Vader']);

        Passport::actingAs(User::factory()->viewUsers()->create());
        $response = $this->getJson(route('api.users.index', ['search' => 'luke sky']))->assertOk();

        $results = collect($response->json('rows'));

        $this->assertEquals(1, $results->count());
        $this->assertTrue($results->pluck('name')->contains(fn ($text) => str_contains($text, 'Luke')));
        $this->assertFalse($results->pluck('name')->contains(fn ($text) => str_contains($text, 'Darth')));
    }

    public function test_results_when_searching_for_active_users()
    {
        User::factory()->create(['first_name' => 'Active', 'last_name' => 'User']);
        User::factory()->create(['first_name' => 'Deleted', 'last_name' => 'User'])->delete();

        $response = $this->actingAsForApi(User::factory()->viewUsers()->create())
            ->getJson(route('api.users.index', [
                'deleted' => 'false',
                'company_id' => '',
                'search' => 'user',
                'order' => 'asc',
                'offset' => '0',
                'limit' => '20',
            ]))
            ->assertOk();

        $firstNames = collect($response->json('rows'))->pluck('first_name');

        $this->assertTrue(
            $firstNames->contains('Active'),
            'Expected user does not appear in results'
        );

        $this->assertFalse(
            $firstNames->contains('Deleted'),
            'Unexpected deleted user appears in results'
        );
    }

    public function test_results_when_searching_for_deleted_users()
    {
        User::factory()->create(['first_name' => 'Active', 'last_name' => 'User']);
        User::factory()->create(['first_name' => 'Deleted', 'last_name' => 'User'])->delete();

        $response = $this->actingAsForApi(User::factory()->viewUsers()->create())
            ->getJson(route('api.users.index', [
                'deleted' => 'true',
                'company_id' => '',
                'search' => 'user',
                'order' => 'asc',
                'offset' => '0',
                'limit' => '20',
            ]))
            ->assertOk();

        $firstNames = collect($response->json('rows'))->pluck('first_name');

        $this->assertFalse(
            $firstNames->contains('Active'),
            'Unexpected active user appears in results'
        );

        $this->assertTrue(
            $firstNames->contains('Deleted'),
            'Expected deleted user does not appear in results'
        );
    }

    public function test_users_scoped_to_company_when_multiple_full_company_support_enabled()
    {
        $this->settings->enableMultipleFullCompanySupport();

        $companyA = Company::factory()
            ->has(User::factory(['first_name' => 'Company A', 'last_name' => 'User']))
            ->create();

        Company::factory()
            ->has(User::factory(['first_name' => 'Company B', 'last_name' => 'User']))
            ->create();

        $response = $this->actingAsForApi(User::factory()->for($companyA)->viewUsers()->create())
            ->getJson(route('api.users.index'))
            ->assertOk();

        $results = collect($response->json('rows'));

        $this->assertTrue(
            $results->pluck('name')->contains(fn ($text) => str_contains($text, 'Company A')),
            'User index does not contain expected user'
        );
        $this->assertFalse(
            $results->pluck('name')->contains(fn ($text) => str_contains($text, 'Company B')),
            'User index contains unexpected user from another company'
        );
    }

    public function test_users_scoped_to_company_during_search_when_multiple_full_company_support_enabled()
    {
        $this->settings->enableMultipleFullCompanySupport();

        $companyA = Company::factory()
            ->has(User::factory(['first_name' => 'Company A', 'last_name' => 'User']))
            ->create();

        Company::factory()
            ->has(User::factory(['first_name' => 'Company B', 'last_name' => 'User']))
            ->create();

        $response = $this->actingAsForApi(User::factory()->for($companyA)->viewUsers()->create())
            ->getJson(route('api.users.index', [
                'deleted' => 'false',
                'company_id' => null,
                'search' => 'user',
                'order' => 'asc',
                'offset' => '0',
                'limit' => '20',
            ]))
            ->assertOk();

        $results = collect($response->json('rows'));

        $this->assertTrue(
            $results->pluck('name')->contains(fn ($text) => str_contains($text, 'Company A')),
            'User index does not contain expected user'
        );
        $this->assertFalse(
            $results->pluck('name')->contains(fn ($text) => str_contains($text, 'Company B')),
            'User index contains unexpected user from another company'
        );
    }

    public function test_users_index_when_invalid_sort_field_is_passed()
    {
        $this->markIncompleteIfSqlite('This test is not compatible with SQLite');

        $this->actingAsForApi(User::factory()->viewUsers()->create())
            ->getJson(route('api.users.index', [
                'sort' => 'assets',
            ]))
            ->assertOk()
            ->assertStatus(200)
            ->json();
    }
}
