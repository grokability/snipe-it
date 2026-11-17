<?php

namespace Tests\Feature\Importing\Api;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * The pre-fix duplicate-header check ran `in_array` + `array_search`
 * for every header, both O(N), so a CSV with N unique headers took
 * O(N^2) time even though no duplicates existed. The rewrite uses a
 * single-pass seen-map keyed by header name recording the first-seen
 * column index. These tests cover the two things the rewrite must
 * preserve: duplicates are still rejected with the same message shape,
 * and a wide CSV with distinct headers goes through quickly.
 */
class ImportDuplicateHeaderDetectionTest extends TestCase
{
    public function test_duplicate_headers_are_rejected_with_first_and_repeat_column_numbers(): void
    {
        Storage::fake();

        $importer = User::factory()->canImport()->create();

        $csv = "asset tag,name,asset tag,status\nAT-1,Example,AT-2,Deployed\n";

        $this->actingAsForApi($importer)
            ->postJson(route('api.imports.store'), [
                'files' => [
                    $this->fakeCsvUpload('dup.csv', $csv),
                ],
            ])
            ->assertStatus(422)
            ->assertJsonFragment([
                'messages' => "Duplicate header 'asset tag' detected, first at column: 1, repeats at column: 3",
            ]);
    }

    public function test_multiple_distinct_duplicate_pairs_are_each_reported(): void
    {
        Storage::fake();

        $importer = User::factory()->canImport()->create();

        $csv = "id,name,id,status,name\nR1,Foo,R2,Deployed,Bar\n";

        $response = $this->actingAsForApi($importer)
            ->postJson(route('api.imports.store'), [
                'files' => [
                    $this->fakeCsvUpload('two-dups.csv', $csv),
                ],
            ])
            ->assertStatus(422);

        $messages = $response->json('messages');
        $this->assertStringContainsString("Duplicate header 'id' detected, first at column: 1, repeats at column: 3", $messages);
        $this->assertStringContainsString("Duplicate header 'name' detected, first at column: 2, repeats at column: 5", $messages);
    }

    public function test_triple_occurrence_header_reports_both_repeats_against_the_first_occurrence(): void
    {
        Storage::fake();

        $importer = User::factory()->canImport()->create();

        $csv = "name,status,name,notes,name\nA,Deployed,B,n,C\n";

        $response = $this->actingAsForApi($importer)
            ->postJson(route('api.imports.store'), [
                'files' => [
                    $this->fakeCsvUpload('triple.csv', $csv),
                ],
            ])
            ->assertStatus(422);

        $messages = $response->json('messages');
        $this->assertStringContainsString("Duplicate header 'name' detected, first at column: 1, repeats at column: 3", $messages);
        $this->assertStringContainsString("Duplicate header 'name' detected, first at column: 1, repeats at column: 5", $messages);
    }

    public function test_wide_csv_with_distinct_headers_is_accepted_quickly(): void
    {
        // Pre-fix, a 8000-column unique-header CSV ran roughly 64 million
        // in_array + array_search comparisons against the header array
        // (both scan the full array of values on every call), which was
        // the ~10-second case the reporter demonstrated. Post-fix it is a
        // single pass of 8000 array_key_exists lookups against a
        // hashtable, which should complete well under one second. A
        // 10-second budget here still flags a regression into the
        // scan-per-header shape without being flaky under heavy CI load.
        Storage::fake();

        $importer = User::factory()->canImport()->create();

        $columns = array_map(fn ($i) => "column_$i", range(0, 7999));
        $values = array_fill(0, 8000, 'v');
        $csv = implode(',', $columns)."\n".implode(',', $values)."\n";

        $start = microtime(true);
        $this->actingAsForApi($importer)
            ->postJson(route('api.imports.store'), [
                'files' => [
                    $this->fakeCsvUpload('wide.csv', $csv),
                ],
            ])
            ->assertSuccessful();
        $elapsed = microtime(true) - $start;

        $this->assertLessThan(
            10.0,
            $elapsed,
            "Processing 8,000 unique CSV headers took {$elapsed}s. Pre-fix measured ~10s on the same shape, so anything near or past the budget is a regression into quadratic duplicate-detection behavior.",
        );
    }

    private function fakeCsvUpload(string $name, string $contents): UploadedFile
    {
        $path = $this->trackTempFile(tempnam(sys_get_temp_dir(), 'csv'));
        file_put_contents($path, $contents);

        return new UploadedFile($path, $name, 'text/csv', null, true);
    }
}
