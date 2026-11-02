<?php

namespace Tests\Feature\SavedSearches\Api;

use App\Models\Accessory;
use App\Models\SavedSearch;
use App\Models\User;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Regression coverage for the two bits of saved-search behavior that
 * are most likely to break in a future refactor:
 *
 *   1. Name uniqueness is per-user, not global.
 *   2. `model` metadata must be a loadable Eloquent subclass.
 */
class SavedSearchStoreTest extends TestCase
{
    public function test_two_users_can_use_the_same_name_on_the_same_listing(): void
    {
        $adminA = User::factory()->create();
        $adminB = User::factory()->create();

        $this->actingAsForApi($adminA)
            ->postJson(route('api.saved-searches.store'), [
                'table_name' => 'accessoriesListingTable',
                'name' => 'Weekly audit',
                'query_string' => 'filter[category]=1',
            ])
            ->assertCreated();

        $this->actingAsForApi($adminB)
            ->postJson(route('api.saved-searches.store'), [
                'table_name' => 'accessoriesListingTable',
                'name' => 'Weekly audit',
                'query_string' => 'filter[category]=2',
            ])
            ->assertCreated();

        $this->assertSame(1, SavedSearch::where('created_by', $adminA->id)->count());
        $this->assertSame(1, SavedSearch::where('created_by', $adminB->id)->count());
    }

    public function test_same_user_cannot_reuse_a_name_on_the_same_listing(): void
    {
        $admin = User::factory()->create();

        $this->actingAsForApi($admin)
            ->postJson(route('api.saved-searches.store'), [
                'table_name' => 'accessoriesListingTable',
                'name' => 'Weekly audit',
                'query_string' => 'filter[category]=1',
            ])
            ->assertCreated();

        $this->actingAsForApi($admin)
            ->postJson(route('api.saved-searches.store'), [
                'table_name' => 'accessoriesListingTable',
                'name' => 'Weekly audit',
                'query_string' => 'filter[category]=99',
            ])
            ->assertStatus(422);

        $this->assertSame(1, SavedSearch::where('created_by', $admin->id)->count());
    }

    public function test_same_user_can_reuse_a_name_on_a_different_listing(): void
    {
        $admin = User::factory()->create();

        $this->actingAsForApi($admin)
            ->postJson(route('api.saved-searches.store'), [
                'table_name' => 'accessoriesListingTable',
                'name' => 'Weekly audit',
                'query_string' => 'filter[category]=1',
            ])
            ->assertCreated();

        $this->actingAsForApi($admin)
            ->postJson(route('api.saved-searches.store'), [
                'table_name' => 'assetsListingTable',
                'name' => 'Weekly audit',
                'query_string' => 'filter[category]=1',
            ])
            ->assertCreated();

        $this->assertSame(2, SavedSearch::where('created_by', $admin->id)->count());
    }

    public function test_model_metadata_accepts_a_real_eloquent_class(): void
    {
        $admin = User::factory()->create();

        $this->actingAsForApi($admin)
            ->postJson(route('api.saved-searches.store'), [
                'table_name' => 'accessoriesListingTable',
                'model' => Accessory::class,
                'name' => 'Weekly audit',
                'query_string' => 'filter[category]=1',
            ])
            ->assertCreated();

        $this->assertSame(Accessory::class, SavedSearch::first()->model);
    }

    public function test_model_metadata_accepts_null(): void
    {
        $admin = User::factory()->create();

        $this->actingAsForApi($admin)
            ->postJson(route('api.saved-searches.store'), [
                'table_name' => 'accessoriesListingTable',
                'model' => null,
                'name' => 'Weekly audit',
                'query_string' => 'filter[category]=1',
            ])
            ->assertCreated();

        $this->assertNull(SavedSearch::first()->model);
    }

    #[DataProvider('provideInvalidModelStrings')]
    public function test_model_metadata_rejects_non_eloquent_strings(string $invalid): void
    {
        $admin = User::factory()->create();

        $this->actingAsForApi($admin)
            ->postJson(route('api.saved-searches.store'), [
                'table_name' => 'accessoriesListingTable',
                'model' => $invalid,
                'name' => 'Weekly audit',
                'query_string' => 'filter[category]=1',
            ])
            ->assertStatus(422)
            ->assertJsonPath('status', 'error')
            ->assertJsonStructure(['messages' => ['model']]);

        $this->assertSame(0, SavedSearch::count());
    }

    public static function provideInvalidModelStrings(): array
    {
        return [
            'nonexistent class' => ['App\\Models\\DoesNotExist'],
            'not a class at all' => ['<script>alert(1)</script>'],
            // Random non-Eloquent FQCN that IS loadable. Guards
            // against a future weakening of the subclass check to
            // just `class_exists`.
            'non-eloquent class' => ['Illuminate\\Support\\Str'],
        ];
    }
}
