<?php

namespace Tests\Unit\Models\Traits;

use App\Models\Asset;
use App\Models\Company;
use App\Models\Consumable;
use App\Models\License;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Tests\TestCase;

/**
 * Mas cobertura del trait Searchable ejercitando textSearch sobre distintos
 * modelos y formas de entrada (texto libre y filtros estructurados seguros en SQLite).
 */
class SearchableMoreTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->superuser()->create());
    }

    public function test_text_search_on_users(): void
    {
        User::factory()->count(3)->create();

        $this->assertInstanceOf(Collection::class, User::textSearch('admin')->get());
        $this->assertInstanceOf(Collection::class, User::textSearch('test AND user')->get());
        $this->assertInstanceOf(Collection::class, User::textSearch('{"first_name":"Jane"}')->get());
    }

    public function test_text_search_on_license(): void
    {
        License::factory()->count(2)->create();

        $this->assertInstanceOf(Collection::class, License::textSearch('Office')->get());
        $this->assertInstanceOf(Collection::class, License::textSearch('{"name":"is:not_null"}')->get());
    }

    public function test_text_search_on_consumable(): void
    {
        Consumable::factory()->count(2)->create();

        $this->assertInstanceOf(Collection::class, Consumable::textSearch('Toner')->get());
    }

    public function test_text_search_on_company(): void
    {
        Company::factory()->count(2)->create();

        $this->assertInstanceOf(Collection::class, Company::textSearch('Acme')->get());
    }

    public function test_text_search_multi_term_and_empty_on_asset(): void
    {
        Asset::factory()->count(2)->create();

        $this->assertInstanceOf(Collection::class, Asset::textSearch('Dell AND laptop AND 2020')->get());
        $this->assertInstanceOf(Collection::class, Asset::textSearch('   ')->get());
    }
}
