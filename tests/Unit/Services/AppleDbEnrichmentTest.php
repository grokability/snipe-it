<?php

namespace Tests\Unit\Services;

use App\Services\AppleDb\AppleDbEnrichment;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AppleDbEnrichmentTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        AppleDbEnrichment::clearCache();
    }

    public function test_single_release_and_discontinued_dates_are_parsed()
    {
        $this->fakeAppleDb(released: '2022-06-24', discontinued: '2023-10-30');

        $record = AppleDbEnrichment::canonicalIdentity('Mac14,7');
        $this->assertNotNull($record);

        $this->assertSame('2022-06-24', $record->releaseDate?->toDateString());
        $this->assertSame('2023-10-30', $record->discontinuedDate?->toDateString());
    }

    public function test_release_date_list_uses_the_earliest_date()
    {
        // appledb.dev's entry for iPhone14,7 (iPhone 14): the yellow model shipped later
        $this->fakeAppleDb(released: ['2023-03-14', '2022-09-16'], discontinued: '2025-02-19');

        $record = AppleDbEnrichment::canonicalIdentity('iPhone14,7');
        $this->assertNotNull($record);

        $this->assertSame('2022-09-16', $record->releaseDate?->toDateString());
        $this->assertSame('2025-02-19', $record->discontinuedDate?->toDateString());
    }

    public function test_discontinued_date_list_uses_the_latest_date()
    {
        $this->fakeAppleDb(released: '2022-09-16', discontinued: ['2024-09-09', 'not a date', '2025-02-19']);

        $record = AppleDbEnrichment::canonicalIdentity('iPhone14,7');
        $this->assertNotNull($record);

        $this->assertSame('2025-02-19', $record->discontinuedDate?->toDateString());
    }

    public function test_list_without_usable_dates_gives_no_date()
    {
        $this->fakeAppleDb(released: [], discontinued: [null, '']);

        $record = AppleDbEnrichment::canonicalIdentity('iPhone14,7');
        $this->assertNotNull($record);

        $this->assertNull($record->releaseDate);
        $this->assertNull($record->discontinuedDate);
    }

    private function fakeAppleDb(mixed $released, mixed $discontinued): void
    {
        Http::fake([
            'api.appledb.dev/device/*' => Http::response([
                'name' => 'Test device',
                'released' => $released,
                'discontinued' => $discontinued,
                'model' => ['A0000'],
            ]),
        ]);
    }
}
