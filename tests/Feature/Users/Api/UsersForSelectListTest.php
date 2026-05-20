<?php

namespace Tests\Feature\Users\Api;

use App\Models\Company;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;
use Laravel\Passport\Passport;
use Tests\TestCase;

class UsersForSelectListTest extends TestCase
{
    public function test_requires_view_selectlists_permission(): void
    {
        $this->actingAsForApi(User::factory()->create())
            ->getJson(route('api.users.selectlist'))
            ->assertForbidden();
    }

    public function test_users_are_returned()
    {
        $users = User::factory()->superuser()->count(3)->create();

        Passport::actingAs($users->first());
        $this->getJson(route('api.users.selectlist'))
            ->assertOk()
            ->assertJsonStructure([
                'results',
                'pagination',
                'total_count',
                'page',
                'page_count',
            ])
            ->assertJson(fn (AssertableJson $json) => $json->has('results', 3)->etc());
    }

    public function test_users_can_be_searched_by_first_and_last_name()
    {
        User::factory()->create(['first_name' => 'Luke', 'last_name' => 'Skywalker']);

        Passport::actingAs(User::factory()->editUsers()->create());
        $response = $this->getJson(route('api.users.selectlist', ['search' => 'luke sky']))->assertOk();

        $results = collect($response->json('results'));

        $this->assertEquals(1, $results->count());
        $this->assertTrue($results->pluck('text')->contains(fn ($text) => str_contains($text, 'Luke')));
    }

    public function test_users_can_be_searched_by_email()
    {
        User::factory()->create(['first_name' => 'Luke', 'last_name' => 'Skywalker', 'email' => 'luke@jedis.org']);

        Passport::actingAs(User::factory()->editUsers()->create());
        $response = $this->getJson(route('api.users.selectlist', ['search' => 'luke@jedis']))->assertOk();

        $results = collect($response->json('results'));

        $this->assertEquals(1, $results->count());
        $this->assertTrue($results->pluck('text')->contains(fn ($text) => str_contains($text, 'Luke')));
    }

    public function test_users_scoped_to_company_when_multiple_full_company_support_enabled()
    {
        $this->settings->enableMultipleFullCompanySupport();

        $jedi = Company::factory()->has(User::factory()->count(3)->sequence(
            ['first_name' => 'Luke', 'last_name' => 'Skywalker', 'username' => 'lskywalker', 'permissions' => json_encode(['users.edit' => '1'])],
            ['first_name' => 'Obi-Wan', 'last_name' => 'Kenobi', 'username' => 'okenobi'],
            ['first_name' => 'Anakin', 'last_name' => 'Skywalker', 'username' => 'askywalker'],
        ))->create();

        $sith = Company::factory()
            ->has(User::factory()->state(['first_name' => 'Darth', 'last_name' => 'Vader', 'username' => 'dvader']))
            ->create();

        Passport::actingAs($jedi->users->first());
        $response = $this->getJson(route('api.users.selectlist'))->assertOk();

        $results = collect($response->json('results'));

        $this->assertEquals(3, $results->count());
        $this->assertTrue(
            $results->pluck('text')->contains(fn ($text) => str_contains($text, 'Luke'))
        );
        $this->assertFalse(
            $results->pluck('text')->contains(fn ($text) => str_contains($text, 'Darth'))
        );
    }

    public function test_users_scoped_to_company_during_search_when_multiple_full_company_support_enabled()
    {
        $this->settings->enableMultipleFullCompanySupport();

        $jedi = Company::factory()->has(User::factory()->count(3)->sequence(
            ['first_name' => 'Luke', 'last_name' => 'Skywalker', 'username' => 'lskywalker', 'email' => 'lskywalker@jedis.org', 'permissions' => json_encode(['users.edit' => '1'])],
            ['first_name' => 'Obi-Wan', 'last_name' => 'Kenobi', 'username' => 'okenobi', 'email' => 'okenobi@jedis.org'],
            ['first_name' => 'Anakin', 'last_name' => 'Skywalker', 'username' => 'askywalker', 'email' => 'askywalker@alliance.org'],
        ))->create();

        Company::factory()
            ->has(User::factory()->state(['first_name' => 'Darth', 'last_name' => 'Vader', 'username' => 'dvader', 'email' => 'dvader@empire.jerks']))
            ->create();

        Passport::actingAs($jedi->users->first());
        $response = $this->getJson(route('api.users.selectlist', ['search' => 'a']))->assertOk();

        $results = collect($response->json('results'));

        $this->assertEquals(3, $results->count());
        $this->assertTrue($results->pluck('text')->contains(fn ($text) => str_contains($text, 'Luke')));
        $this->assertTrue($results->pluck('text')->contains(fn ($text) => str_contains($text, 'Anakin')));

        $response = $this->getJson(route('api.users.selectlist', ['search' => 'dvader']))->assertOk();
        $this->assertEquals(0, collect($response->json('results'))->count());
    }

    public function test_users_are_filtered_by_company_id_parameter_when_full_company_support_is_enabled()
    {
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $userInA = User::factory()->create(['first_name' => 'Luke', 'last_name' => 'Skywalker', 'username' => 'lskywalker_fmcs1']);
        $companyA->users()->attach($userInA);

        $userInB = User::factory()->create(['first_name' => 'Darth', 'last_name' => 'Vader', 'username' => 'dvader_fmcs1']);
        $companyB->users()->attach($userInB);

        $actor = User::factory()->superuser()->create();

        $response = $this->actingAsForApi($actor)
            ->getJson(route('api.users.selectlist', ['companyId' => $companyA->id]))
            ->assertOk();

        $results = collect($response->json('results'));
        $this->assertTrue($results->pluck('text')->contains(fn ($t) => str_contains($t, 'Luke')));
        $this->assertFalse($results->pluck('text')->contains(fn ($t) => str_contains($t, 'Darth')));
    }

    public function test_users_are_filtered_by_multiple_comma_separated_company_ids_when_full_company_support_is_enabled()
    {
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB, $companyC] = Company::factory()->count(3)->create();

        $userInA = User::factory()->create(['first_name' => 'Luke', 'last_name' => 'Skywalker', 'username' => 'lskywalker_fmcs2']);
        $companyA->users()->attach($userInA);

        $userInB = User::factory()->create(['first_name' => 'Obi-Wan', 'last_name' => 'Kenobi', 'username' => 'okenobi_fmcs2']);
        $companyB->users()->attach($userInB);

        $userInC = User::factory()->create(['first_name' => 'Darth', 'last_name' => 'Vader', 'username' => 'dvader_fmcs2']);
        $companyC->users()->attach($userInC);

        $actor = User::factory()->superuser()->create();

        $response = $this->actingAsForApi($actor)
            ->getJson(route('api.users.selectlist', ['companyId' => $companyA->id.','.$companyB->id]))
            ->assertOk();

        $results = collect($response->json('results'));
        $this->assertTrue($results->pluck('text')->contains(fn ($t) => str_contains($t, 'Luke')));
        $this->assertTrue($results->pluck('text')->contains(fn ($t) => str_contains($t, 'Obi-Wan')));
        $this->assertFalse($results->pluck('text')->contains(fn ($t) => str_contains($t, 'Darth')));
    }
}
