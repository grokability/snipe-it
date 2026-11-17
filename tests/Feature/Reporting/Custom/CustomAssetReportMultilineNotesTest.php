<?php

namespace Tests\Feature\Reporting\Custom;

use App\Models\Asset;
use App\Models\User;
use League\Csv\Reader;
use Tests\TestCase;

/**
 * Notes containing embedded newlines
 * (admins type multi-line text into the notes editor) exported fine
 * per RFC 4180 because fputcsv auto-quotes fields with embedded
 * newlines, but the file was being written with Unix LF record
 * terminators. Excel on Windows reads a bare LF inside a quoted
 * field as a record break anyway, so the asset's row splits across
 * three on open. Switching the row terminator to CRLF puts Excel in
 * the mode where it treats embedded LFs as data and only hard breaks
 * on CRLF.
 */
class CustomAssetReportMultilineNotesTest extends TestCase
{
    public function test_asset_notes_with_embedded_newlines_stay_in_a_single_csv_row(): void
    {
        Asset::factory()->create([
            'name' => 'notes-asset',
            'notes' => "Date: 21\nFor: Bob\nFrom: Paul",
        ]);

        $response = $this->actingAs(User::factory()->canViewReports()->create())
            ->post('reports/custom', [
                'asset_name' => '1',
                'notes' => '1',
            ])
            ->assertOk();

        $csv = $response->streamedContent();

        // RFC 4180 record terminator so Excel reliably treats bare
        // LFs inside quoted cells as data instead of row breaks.
        $this->assertStringContainsString("\r\n", $csv, 'CSV must terminate rows with CRLF for Excel compatibility.');

        $reader = Reader::createFromString($csv);
        $reader->setHeaderOffset(0);
        $rows = iterator_to_array($reader->getRecords());

        $this->assertCount(1, $rows, 'Multi-line notes field must stay in a single CSV row rather than split across three.');
        $this->assertSame(
            "Date: 21\nFor: Bob\nFrom: Paul",
            array_values($rows)[0]['Notes'] ?? null,
            'Notes column must preserve the embedded newlines verbatim inside the quoted cell.',
        );
    }

    public function test_asset_notes_with_crlf_embedded_newlines_also_stay_in_a_single_row(): void
    {
        // A user editing notes on Windows produces CRLF line breaks.
        // The quoted-cell handling has to tolerate both LF and CRLF
        // inside the field so Windows-authored text round-trips too.
        Asset::factory()->create([
            'name' => 'crlf-notes-asset',
            'notes' => "Date: 21\r\nFor: Bob\r\nFrom: Paul",
        ]);

        $response = $this->actingAs(User::factory()->canViewReports()->create())
            ->post('reports/custom', [
                'asset_name' => '1',
                'notes' => '1',
            ])
            ->assertOk();

        $reader = Reader::createFromString($response->streamedContent());
        $reader->setHeaderOffset(0);
        $rows = iterator_to_array($reader->getRecords());

        $this->assertCount(1, $rows);
    }
}
