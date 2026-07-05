<?php

namespace Tests\Feature\Accessories\Api;

use App\Models\Accessory;
use App\Models\User;
use Tests\TestCase;

class AccessoryPaginationTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->superuser()->create();
    }

    public function test_page_one_returns_first_items()
    {
        foreach (range(1, 10) as $i) {
            Accessory::factory()->create(['name' => sprintf('PAG-TEST-%03d', $i)]);
        }

        $names = $this->actingAsForApi($this->user)
            ->getJson(route('api.accessories.index', ['page' => 1, 'limit' => 5, 'sort' => 'name', 'order' => 'asc']))
            ->assertOk()
            ->json('rows.*.name');

        $this->assertEquals(
            ['PAG-TEST-001', 'PAG-TEST-002', 'PAG-TEST-003', 'PAG-TEST-004', 'PAG-TEST-005'],
            $names
        );
    }

    public function test_page_two_returns_second_set_of_items()
    {
        foreach (range(1, 10) as $i) {
            Accessory::factory()->create(['name' => sprintf('PAG-TEST-%03d', $i)]);
        }

        $names = $this->actingAsForApi($this->user)
            ->getJson(route('api.accessories.index', ['page' => 2, 'limit' => 5, 'sort' => 'name', 'order' => 'asc']))
            ->assertOk()
            ->json('rows.*.name');

        $this->assertEquals(
            ['PAG-TEST-006', 'PAG-TEST-007', 'PAG-TEST-008', 'PAG-TEST-009', 'PAG-TEST-010'],
            $names
        );
    }

    public function test_offset_returns_correct_items()
    {
        foreach (range(1, 10) as $i) {
            Accessory::factory()->create(['name' => sprintf('PAG-TEST-%03d', $i)]);
        }

        $names = $this->actingAsForApi($this->user)
            ->getJson(route('api.accessories.index', ['offset' => 5, 'limit' => 5, 'sort' => 'name', 'order' => 'asc']))
            ->assertOk()
            ->json('rows.*.name');

        $this->assertEquals(
            ['PAG-TEST-006', 'PAG-TEST-007', 'PAG-TEST-008', 'PAG-TEST-009', 'PAG-TEST-010'],
            $names
        );
    }

    public function test_page_param_respects_limit()
    {
        Accessory::factory()->count(10)->create();

        $response = $this->actingAsForApi($this->user)
            ->getJson(route('api.accessories.index', ['page' => 1, 'limit' => 4]))
            ->assertOk();

        $this->assertCount(4, $response->json('rows'));
    }

    public function test_page_beyond_results_returns_empty_rows()
    {
        Accessory::factory()->count(5)->create();

        $response = $this->actingAsForApi($this->user)
            ->getJson(route('api.accessories.index', ['page' => 99, 'limit' => 5]))
            ->assertOk();

        $this->assertCount(0, $response->json('rows'));
        $this->assertEquals(5, $response->json('total'));
    }
}
