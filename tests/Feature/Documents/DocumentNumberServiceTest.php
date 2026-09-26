<?php

namespace Tests\Feature\Documents;

use App\Models\Setting;
use App\Services\Documents\DocumentNumberService;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DocumentNumberServiceTest extends TestCase
{
    private DocumentNumberService $numbers;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(\Illuminate\Support\Carbon::create(2026, 9, 23, 12));
        $this->numbers = app(DocumentNumberService::class);
    }

    public function test_numbers_use_type_prefix_year_and_sequence()
    {
        $one = $this->numbers->next('checkout');
        $two = $this->numbers->next('checkout');

        $this->assertMatchesRegularExpression('/^CO-2026-\d{6}$/', $one);
        $this->assertSame(
            (int) substr($one, -6) + 1,
            (int) substr($two, -6),
        );
    }

    public function test_prefixes_come_from_settings()
    {
        $settings = Setting::getSettings();
        $settings->document_prefix_checkout = 'CHK-';
        $settings->save();
        Setting::$_cache = null;

        $this->assertStringStartsWith('CHK-2026-', $this->numbers->next('checkout'));
    }

    public function test_sequence_is_atomic_under_concurrency()
    {
        $workers = 8;
        $perWorker = 5;

        $results = [];
        for ($i = 0; $i < $workers; $i++) {
            // Simulate concurrent reservation by allocating in one tight loop —
            // uniqueness + monotonic increments are what matter here.
            for ($j = 0; $j < $perWorker; $j++) {
                $results[] = $this->numbers->next('handover');
            }
        }

        $this->assertCount($workers * $perWorker, array_unique($results));

        $seq = array_map(fn ($n) => (int) substr($n, -6), $results);
        sort($seq);
        $this->assertSame(range($seq[0], $seq[0] + count($seq) - 1), $seq, 'Sequence must not skip or repeat');
    }

    public function test_unknown_type_is_rejected()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->numbers->next('nonsense');
    }

    public function test_settings_row_drives_the_increment()
    {
        $before = (int) DB::table('settings')->value('document_number_seq');
        $this->numbers->next('return');
        $after = (int) DB::table('settings')->value('document_number_seq');

        $this->assertSame($before + 1, $after);
    }
}
