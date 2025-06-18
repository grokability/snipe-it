<?php

namespace Tests\Feature\Users\Api;

use App\Models\Company;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;
use Laravel\Passport\Passport;
use Tests\TestCase;

class UsersForSelectListTest extends TestCase
{
    public function testUsersAreReturned()
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
            ->assertJson(fn(AssertableJson $json) => $json->has('results', 3)->etc());
    }

    public function testUsersCanBeSearchedByFirstAndLastName()
    {
        User::factory()->create(['first_name' => 'Luke', 'last_name' => 'Skywalker']);

        Passport::actingAs(User::factory()->create());
        $response = $this->getJson(route('api.users.selectlist', ['search' => 'luke sky']))->assertOk();

        $results = collect($response->json('results'));

        $this->assertEquals(1, $results->count());
        $this->assertTrue($results->pluck('text')->contains(fn($text) => str_contains($text, 'Luke')));
    }

    public function testUsersCanBeSearchedByEmail()
    {
        User::factory()->create(['first_name' => 'Luke', 'last_name' => 'Skywalker', 'email' => 'luke@jedis.org']);

        Passport::actingAs(User::factory()->create());
        $response = $this->getJson(route('api.users.selectlist', ['search' => 'luke@jedis']))->assertOk();

        $results = collect($response->json('results'));

        $this->assertEquals(1, $results->count());
        $this->assertTrue($results->pluck('text')->contains(fn($text) => str_contains($text, 'Luke')));
    }

    public function testUsersScopedToCompanyWhenMultipleFullCompanySupportEnabled()
    {
        $this->settings->enableMultipleFullCompanySupport();

        $jedi = Company::factory()->has(User::factory()->count(3)->sequence(
            ['first_name' => 'Luke', 'last_name' => 'Skywalker', 'username' => 'lskywalker'],
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
            $results->pluck('text')->contains(fn($text) => str_contains($text, 'Luke'))
        );
        $this->assertFalse(
            $results->pluck('text')->contains(fn($text) => str_contains($text, 'Darth'))
        );
    }

    public function testUsersScopedToCompanyDuringSearchWhenMultipleFullCompanySupportEnabled()
    {
        $this->settings->enableMultipleFullCompanySupport();

        $jedi = Company::factory()->has(User::factory()->count(3)->sequence(
            ['first_name' => 'Luke', 'last_name' => 'Skywalker', 'username' => 'lskywalker', 'email' => 'lskywalker@jedis.org'],
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
        $this->assertTrue($results->pluck('text')->contains(fn($text) => str_contains($text, 'Luke')));
        $this->assertTrue($results->pluck('text')->contains(fn($text) => str_contains($text, 'Anakin')));

        $response = $this->getJson(route('api.users.selectlist', ['search' => 'dvader']))->assertOk();
        $this->assertEquals(0, collect($response->json('results'))->count());
    }
}
